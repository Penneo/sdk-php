<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\OAuth\PenneoTokens;
use Penneo\SDK\OAuth\TokenStorage;
use Penneo\SDK\PenneoSDKException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Tests\Server;

class OAuthTest extends TestCase
{
    use BuildsOAuth;

    /**
     * @testWith ["noScope"]
     *           ["360noScope"]
     */
    public function
    test_buildRedirectUrl_throws_error_when_invalid_scope_supplied(string $unknownScope)
    {
        $oauth = $this->build();

        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build URL! Unknown scope '${unknownScope}'!");

        $oauth->buildRedirectUrl($unknownScope);
    }

    public function
    test_buildRedirectUrl_succeeds()
    {
        $oauth = $this->build([
            'clientId' => '123',
            'clientSecret' => '456',
            'redirectUri' => 'https://google.com',
            'environment' => 'sandbox'
        ]);

        $url = $oauth->buildRedirectUrl('read', 'someState');

        $parsed = parse_url($url);
        $this->assertNotEmpty($parsed);

        $this->assertEquals('https', $parsed['scheme']);
        $this->assertEquals('sandbox.oauth.penneo.cloud', $parsed['host']);
        $this->assertEquals('/oauth/token', $parsed['path']);

        $this->assertStringContainsString('scope=read', $parsed['query']);
        $this->assertStringContainsString('client_id=123', $parsed['query']);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://google.com'), $parsed['query']);
        $this->assertStringContainsString('state=someState', $parsed['query']);

        $this->assertStringNotContainsString('client_secret', $parsed['query']);
        $this->assertStringNotContainsString('code_challenge', $parsed['query']);
        $this->assertStringNotContainsString('code_challenge_method', $parsed['query']);
    }

    public function
    test_exchangeAuthCode_throws_error_when_supplied_code_is_empty()
    {
        $oauth = $this->build();

        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage('Cannot exchange code! Provided code should not be empty!');

        $oauth->exchangeAuthCode('');
    }

    public function
    test_exchangeAuthCode_does_something()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['refreshToken' => 'refreshToken', 'accessToken' => 'accessToken'])
            ),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);

        foreach ($container as $transaction) {
            /** @var Request $request */
            $request = $transaction['request'];

            $this->assertEquals('POST', $request->getMethod());
            $this->assertEquals('sandbox.oauth.penneo.cloud', $request->getUri()->getHost());
            $this->assertEquals('https', $request->getUri()->getScheme());

            $requestBody = json_decode($request->getBody());

            $this->assertEquals($requestBody["grant_type"], "authorization_code");
            $this->assertEquals($requestBody["client_id"], "client_id");
            $this->assertEquals($requestBody["client_secret"], "client_secret");
            $this->assertEquals($requestBody["code"], "code");
            $this->assertEquals($requestBody["redirect_uri"], "redirect_uri");
        }

        $tokenStorage = $this->createMock(TokenStorage::class);

        $code = 'someCode';
        $oauth = $this->build([
            "clientId" => "client_id",
            "clientSecret" => "client_secret",
            "tokenStorage" => $tokenStorage
        ], $client);

        $tokenStorage->expects($this->once())
            ->method('saveTokens')
            ->with($this->callback(function (PenneoTokens $parameter) {
                $this->assertEquals('accessToken', $parameter->getAccessToken());
                $this->assertEquals('refreshToken', $parameter->getRefreshToken());
            }));

        $oauth->exchangeAuthCode($code);
    }
}