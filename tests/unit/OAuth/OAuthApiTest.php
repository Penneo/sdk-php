<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\Nonce\NonceGenerator;
use Penneo\SDK\OAuth\OAuthApi;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class OAuthApiTest extends TestCase
{
    use TestsEnvironments;

    private $config;
    private $client;
    private $api;
    /** @var NonceGenerator&Stub */
    private $nonceGenerator;

    /** @var DateTimeImmutable */
    private $fixedNow;

    public function setUp(): void
    {
        $this->fixedNow = new DateTimeImmutable('now');

        $this->config = $this->createPartialMock(
            OAuthConfig::class,
            ['getClientSecret', 'getRedirectUri', 'getClientId', 'getEnvironment', 'getApiKey', 'getApiSecret']
        );
        $this->config->method('getClientSecret')->willReturn('secret');
        $this->config->method('getRedirectUri')->willReturn('https://google.com');
        $this->config->method('getClientId')->willReturn('id');
        $this->config->method('getApiKey')->willReturn('apiKey');

        $storage = $this->createMock(SessionTokenStorage::class);
        $this->client = $this->createMock(Client::class);
        $this->nonceGenerator = $this->createStub(NonceGenerator::class);

        $this->api = new OAuthApi(
            $this->config,
            $storage,
            $this->client,
            $this->nonceGenerator,
            function () {
                return $this->fixedNow;
            }
        );

        $storage->method('getTokens')
            ->willReturn(new PenneoTokens(
                'not_important',
                10,
                'refresh_token',
                20
            ));

        parent::setUp();
    }

    /** @dataProvider environmentAndApiMethodProvider */
    public function testAPICallsUseCorrectHostname(string $env, string $expected, string $method, array $params = [])
    {
        $this->config->method('getEnvironment')->willReturn($env);

        $this->client->expects($this->once())
            ->method('post')
            ->with("https://{$expected}/oauth/token")
            ->willReturn($this->successfulResponse());

        $this->api->{$method}(...$params);
    }

    public function environmentAndApiMethodProvider(): \Generator
    {
        foreach (self::environmentProvider() as $case) {
            yield array_merge($case, ['postTokenRefresh']);
            yield array_merge($case, ['postCodeExchange', ['code', 'verifier']]);
            yield array_merge($case, ['postApiKeyExchange']);
        }
    }

    public function testPostTokenRefreshThrowsWhenRefreshTokenIsMissing(): void
    {
        $storage = $this->createMock(SessionTokenStorage::class);
        $storage->method('getTokens')
            ->willReturn(new PenneoTokens('access_only', 999999, null, null));

        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('post');

        $api = new OAuthApi($this->config, $storage, $client, $this->nonceGenerator);

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage('no refresh token');

        $api->postTokenRefresh();
    }

    /**
     * @testWith ["unique nonce", "secret"]
     *           ["another unique nonce", "real secret"]
     */
    public function testApiKeysExchangeGeneratesProperParameters(string $mockNonce, string $apiSecret)
    {
        $this->config->method('getEnvironment')->willReturn('sandbox');
        $this->nonceGenerator->method('generate')->willReturn($mockNonce);
        $this->config->method('getApiSecret')->willReturn($apiSecret);

        $createdAt = $this->fixedNow->format('D M j Y H:i:s \G\M\TO');
        $digest = base64_encode(sha1($mockNonce . $createdAt . $apiSecret, true));

        $this->client->expects($this->once())
            ->method('post')
            ->with("https://sandbox.oauth.penneo.cloud/oauth/token", [
                'json' => [
                    'grant_type' => 'api_keys',
                    'client_id' => 'id',
                    'client_secret' => 'secret',
                    'key' => 'apiKey',
                    'created_at' => $createdAt,
                    'nonce' => base64_encode($mockNonce),
                    'digest' => $digest
                ]
            ])
            ->willReturn($this->successfulResponse());

        $this->api->postApiKeyExchange();
    }

    public function successfulResponse(): Response
    {
        return new Response(
            200,
            [],
            json_encode([
                'refresh_token' => '',
                'access_token' => '',
                'access_token_expires_at' => 5,
                'refresh_token_expires_at' => 20
            ])
        );
    }
}
