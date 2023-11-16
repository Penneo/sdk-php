<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;
use Penneo\SDK\Tests\Unit\OAuth\BuildsOAuth;
use PHPUnit\Framework\TestCase;

class ExchangeAuthCodeTest extends TestCase
{
    use BuildsOAuth;
    use UsesGuzzler;

    public function testWhenPlutoRespondsWithErrorThenAPenneoExceptionIsThrown()
    {
        $this->guzzler->expects($this->once())
            ->post('https://sandbox.oauth.penneo.cloud/oauth/token')
            ->withJson([
                'code' => 'someCode',
                'code_verifier' => 'someCodeVerifier'
            ])
            ->willRespond(
                new Response(
                    400,
                    [],
                    json_encode([
                        'error' => 'Something broke!',
                        'error_description' => 'The white thing exploded.'
                    ])
                )
            );

        $oauth = $this->build([], $this->guzzler->getClient());

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage('Failed to exchange code: HTTP 400, Something broke! The white thing exploded.');

        $oauth->exchangeAuthCode('someCode', 'someCodeVerifier');
    }

    public function testWhenGuzzleExceptionIsThrownThenItIsRethrownAsAPenneoException()
    {
        $guzzleMock = $this->createMock(Client::class);

        $guzzleException = new class ('I am an exception!') extends \Exception implements GuzzleException {
        };

        $guzzleMock->method('post')->willThrowException($guzzleException);
        $oauth = $this->build([], $guzzleMock);

        try {
            $oauth->exchangeAuthCode('doesntmatter', 'doesntmatter');
        } catch (PenneoSdkRuntimeException $e) {
            $this->assertEquals('Unexpected error occurred: I am an exception!', $e->getMessage());
            $this->assertEquals($guzzleException, $e->getPrevious());
            return;
        }

        $this->fail('Expected exception has not been thrown.');
    }

    /**
     * @testWith ["at1", "rt1", 88, 11]
     *           ["at2", "rt2", 6, 7]
     */
    public function testRetrievesTokensFromPlutoSuccessfully(
        $accessToken,
        $refreshToken,
        $atExpiresAt,
        $rtExpiresAt
    ) {
        $code = 'someCode';
        $tokenStorage = $this->createMock(TokenStorage::class);
        $attributes = [
            "clientId" => "client_id",
            "clientSecret" => "client_secret",
            "tokenStorage" => $tokenStorage,
            "redirectUri" => "https://google.com"
        ];

        $this->guzzler->expects($this->once())
            ->post('https://sandbox.oauth.penneo.cloud/oauth/token')
            ->withJson([
                "grant_type" => "authorization_code",
                "client_id" => $attributes['clientId'],
                "client_secret" => $attributes['clientSecret'],
                "code" => $code,
                "redirect_uri" => $attributes['redirectUri'],
                'code_verifier' => 'someverifier',
            ])
            ->willRespond(
                new Response(
                    200,
                    [],
                    json_encode([
                        'refresh_token' => $refreshToken,
                        'access_token' => $accessToken,
                        'access_token_expires_at' => $atExpiresAt,
                        'refresh_token_expires_at' => $rtExpiresAt
                    ])
                )
            );

        $oauth = $this->build($attributes, $this->guzzler->getClient());

        $tokenStorage->expects($this->once())
            ->method('saveTokens')
            ->with($this->callback(function (PenneoTokens $tokens) use (
                $accessToken,
                $refreshToken,
                $atExpiresAt,
                $rtExpiresAt
            ) {
                $this->assertEquals($accessToken, $tokens->getAccessToken());
                $this->assertEquals($refreshToken, $tokens->getRefreshToken());
                $this->assertEquals($atExpiresAt, $tokens->getAccessTokenExpiresAt());
                $this->assertEquals($rtExpiresAt, $tokens->getRefreshTokenExpiresAt());

                return true;
            }));

        $oauth->exchangeAuthCode($code, 'someverifier');
    }
}
