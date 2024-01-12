<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use BlastCloud\Guzzler\UsesGuzzler;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\Tests\Unit\OAuth\BuildsOAuth;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

class ApiKeysMiddlewareTest extends TestCase
{
    use BuildsOAuth;
    use UsesGuzzler;
    use MocksTokenStorage;

    /** @var SessionTokenStorage */
    private $mockStorage;
    private $tomorrowTimestamp;
    private $yesterdayTimestamp;
    private $oauth;
    private $mockAuthClient;

    public function setUp(): void
    {
        Carbon::setTestNow(Carbon::now());

        $this->tomorrowTimestamp = Carbon::now()->addDay()->getTimestamp();
        $this->yesterdayTimestamp = Carbon::now()->subDay()->getTimestamp();
        $this->mockStorage = $this->mockTokenStorage();

        $this->mockAuthClient = $this->createMock(Client::class);
        $this->oauth = $this->build([
            'tokenStorage' => $this->mockStorage,
            'apiKey' => 'some API Key',
            'apiSecret' => 'some API secret'
        ], $this->mockAuthClient);

        $this->guzzler->getHandlerStack()
            ->unshift($this->oauth->getMiddleware());

        parent::setUp();
    }

    /**
     * @testWith ["accessTokenOne"]
     *           ["accessTokenTwo"]
     */
    public function testAppendsAccessTokenToRequests(string $accessToken)
    {
        $this->mockStorage->saveTokens(
            new PenneoTokens($accessToken, null, $this->tomorrowTimestamp, null)
        );

        // a post request is sent when tokens are refreshed - this should never happen if the token is present.
        $this->mockAuthClient->expects($this->never())
            ->method('post');

        $this->expectTokenToBeAddedToOutgoingRequest($accessToken);

        $this->triggerMiddleware();
    }

    public function testGetsNewTokenWhenStoredAccessTokenHasExpired()
    {
        $this->mockStorage->saveTokens(
            new PenneoTokens('oldAT', null, $this->yesterdayTimestamp, null)
        );

        $this->apiKeysGrantRequest()
            ->willReturn(new Response(200, [], json_encode([
                'access_token' => 'newAT',
                'access_token_expires_at' => $this->tomorrowTimestamp,
            ])));

        $this->expectTokenToBeAddedToOutgoingRequest('newAT');

        $this->triggerMiddleware();
    }

    public function testGetsNewTokenWhenTokensHaveNotBeenSetYet()
    {
        $this->apiKeysGrantRequest()
            ->willReturn(new Response(200, [], json_encode([
                'access_token' => 'freshAT',
                'access_token_expires_at' => $this->tomorrowTimestamp,
            ])));

        $this->expectTokenToBeAddedToOutgoingRequest('freshAT');

        $this->triggerMiddleware();
    }

    public function triggerMiddleware(): void
    {
        $this->guzzler->getClient()
            ->get('/');
    }

    public function expectTokenToBeAddedToOutgoingRequest(string $accessToken): void
    {
        $this->guzzler->expects($this->once())
            ->get('/')
            ->withHeader('Authorization', "Bearer {$accessToken}")
            ->willRespond(new Response());
    }

    public function apiKeysGrantRequest(): InvocationMocker
    {
        return $this->mockAuthClient->expects($this->once())
            ->method('post')
            ->with('https://sandbox.oauth.penneo.cloud/oauth/token');
    }
}
