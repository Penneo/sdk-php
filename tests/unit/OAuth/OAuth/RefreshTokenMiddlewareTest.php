<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use BlastCloud\Guzzler\UsesGuzzler;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\AuthenticationExpiredException;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;
use Penneo\SDK\Tests\Unit\OAuth\BuildsOAuth;
use PHPUnit\Framework\TestCase;

class RefreshTokenMiddlewareTest extends TestCase
{
    use BuildsOAuth;
    use MocksTokenStorage;
    use UsesGuzzler;

    private $tomorrowTimestamp;
    private $yesterdayTimestamp;
    private $fiveSecondsInTheFutureTimestamp;

    /**
     * @var SessionTokenStorage
     */
    private $mockStorage;

    public function setUp(): void
    {
        Carbon::setTestNow(Carbon::now());

        $this->tomorrowTimestamp = Carbon::now()->addDay()->getTimestamp();
        $this->yesterdayTimestamp = Carbon::now()->subDay()->getTimestamp();
        $this->fiveSecondsInTheFutureTimestamp = Carbon::now()->addSeconds('5')->getTimestamp();
        $this->mockStorage = $this->mockTokenStorage(
            new PenneoTokens('accessToken', 'refreshToken', $this->tomorrowTimestamp, $this->tomorrowTimestamp)
        );

        parent::setUp();
    }

    public function testWhenAccessTokenIsNotPresentThenAPenneoExceptionIsThrown()
    {
        $mockStorage = $this->createMock(SessionTokenStorage::class);
        $mockStorage->method('getTokens')
            ->willReturn(null);

        $oauth = $this->build(['tokenStorage' => $mockStorage]);

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage('The access token is missing or expired! Did you complete the OAuth flow?');

        $oauth->getMiddleware();
    }

    /**
     * @testWith ["accessTokenOne"]
     *           ["accessTokenTwo"]
     */
    public function testAppendsAccessTokenToRequests(string $accessToken)
    {
        $this->mockStorage->saveTokens(new PenneoTokens(
            $accessToken,
            'refreshToken',
            $this->tomorrowTimestamp,
            $this->tomorrowTimestamp
        ));

        $mockGuzzle = $this->createMock(Client::class);

        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage
        ], $mockGuzzle);

        $this->guzzler->getHandlerStack()
            ->unshift($oauth->getMiddleware());

        $this->guzzler->expects($this->once())
            ->get('/')
            ->withHeader('Authorization', "Bearer {$accessToken}")
            ->willRespond(new Response());

        // a post request is sent when tokens are refreshed,
        // this should never happen if the tokens are valid.
        $mockGuzzle->expects($this->never())
            ->method('post');

        $this->guzzler->getClient()
            ->get('/');
    }

    /**
     * @testWith [5, "seconds"]
     *           [0, "seconds"]
     *           [-1, "day"]
     *           [-1, "year"]
     */
    public function testWhenBothTokensAreExpiredThenGetMiddlewareThenAPenneoExceptionIsThrown(
        int $timeDiffValue,
        string $timeDiffUnit
    ) {
        $this->mockStorage->saveTokens(new PenneoTokens(
            'not_important',
            'not_important',
            Carbon::now()->addUnit($timeDiffUnit, $timeDiffValue)->getTimestamp(),
            Carbon::now()->addUnit($timeDiffUnit, $timeDiffValue)->getTimestamp()
        ));

        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
        ]);

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage('The access token is missing or expired! Did you complete the OAuth flow?');

        $oauth->getMiddleware();
    }

    /**
     * @testWith [5, "seconds"]
     *           [0, "seconds"]
     *           [-1, "day"]
     *           [-1, "year"]
     */
    public function testWhenBothTokensAreExpiredAndMakingARequestThenAPenneoExceptionIsThrown(
        int $timeDiffValue,
        string $timeDiffUnit
    ) {
        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
        ]);

        $this->guzzler->getHandlerStack()
            ->unshift($oauth->getMiddleware());

        $this->mockStorage->saveTokens(new PenneoTokens(
            'not_important',
            'not_important',
            Carbon::now()->addUnit($timeDiffUnit, $timeDiffValue)->getTimestamp(),
            Carbon::now()->addUnit($timeDiffUnit, $timeDiffValue)->getTimestamp()
        ));

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectException(AuthenticationExpiredException::class);
        $this->expectExceptionMessage('Session has expired, please reauthenticate!');

        $this->guzzler->getClient()
            ->get('/');
    }

    /**
     * @testWith [5, "seconds"]
     *           [0, "seconds"]
     *           [-1, "day"]
     *           [-1, "year"]
     */
    public function testRefreshesTokensIfAccessTokenIsExpiredOrAboutToExpire(
        int $timeDiffValue,
        string $timeDiffUnit
    ) {
        $mockAuthClient = $this->createMock(Client::class);

        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
            'redirectUri' => 'https://google.com',
            'clientId' => 'helloIAmAClientId',
            'clientSecret' => 'helloIAmAClientSecret',
        ], $mockAuthClient);

        $this->guzzler->getHandlerStack()
            ->unshift($oauth->getMiddleware());

        $this->mockStorage->saveTokens(new PenneoTokens(
            'not_important',
            'VERY_IMPORTANT',
            Carbon::now()->addUnit($timeDiffUnit, $timeDiffValue)->getTimestamp(),
            $this->tomorrowTimestamp
        ));

        $mockAuthClient->expects($this->once())
            ->method('post')
            ->with(
                'https://sandbox.oauth.penneo.cloud/oauth/token',
                [
                    'json' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => 'VERY_IMPORTANT',
                        'redirect_uri' => 'https://google.com',
                        'client_id' => 'helloIAmAClientId',
                        'client_secret' => 'helloIAmAClientSecret',
                    ]
                ]
            )
            ->willReturn(new Response(200, [], json_encode([
                'access_token' => 'newAT',
                'refresh_token' => 'newRT',
                'access_token_expires_at' => $this->tomorrowTimestamp,
                'refresh_token_expires_at' => $this->tomorrowTimestamp
            ])));

        $this->guzzler->expects($this->once())
            ->get('/')
            ->withHeader('Authorization', 'Bearer newAT')
            ->willRespond(new Response());

        $this->guzzler->getClient()
            ->get('/');
    }

    public function testWhenTokenRefreshingFailsDueToNon200ResponseThenAPenneoExceptionIsThrown()
    {
        $mockAuthClient = $this->createMock(Client::class);

        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
        ], $mockAuthClient);

        $this->guzzler->getHandlerStack()
            ->unshift($oauth->getMiddleware());

        $this->mockStorage->saveTokens(new PenneoTokens(
            'not_important',
            'refresh_token',
            $this->yesterdayTimestamp,
            $this->tomorrowTimestamp
        ));

        $mockAuthClient->expects($this->once())
            ->method('post')
            ->with('https://sandbox.oauth.penneo.cloud/oauth/token')
            ->willThrowException(
                new BadResponseException(
                    'something',
                    $this->createStub(Request::class),
                    new Response(
                        400,
                        [],
                        json_encode([
                            'error' => 'something went wrong',
                            'error_description' => 'not sure what'
                        ])
                    )
                )
            );

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage('Failed to refresh tokens: HTTP 400, something went wrong not sure what');

        $this->guzzler->getClient()
            ->get('/');
    }

    public function testWhenTokenRefreshingFailsWithUnknownGuzzleExceptionThenAPEnneoExceptionIsThrown()
    {
        $mockAuthClient = $this->createMock(Client::class);

        $oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
        ], $mockAuthClient);

        $this->guzzler->getHandlerStack()
            ->unshift($oauth->getMiddleware());

        $this->mockStorage->saveTokens(new PenneoTokens(
            'not_important',
            'refresh_token',
            $this->yesterdayTimestamp,
            $this->tomorrowTimestamp
        ));

        $guzzleException = new class ('uh oh') extends \Exception implements GuzzleException {
        };

        $mockAuthClient->expects($this->once())
            ->method('post')
            ->with('https://sandbox.oauth.penneo.cloud/oauth/token')
            ->willThrowException($guzzleException);

        try {
            $this->guzzler->getClient()
                ->get('/');
        } catch (PenneoSdkRuntimeException $e) {
            $this->assertEquals('Unexpected error occurred: uh oh', $e->getMessage());
            $this->assertEquals($guzzleException, $e->getPrevious());
            return;
        }

        $this->fail('Expected exception was not thrown!');
    }
}
