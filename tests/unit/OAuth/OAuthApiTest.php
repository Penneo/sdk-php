<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\OAuthApi;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use PHPUnit\Framework\TestCase;

class OAuthApiTest extends TestCase
{
    use TestsEnvironments;

    private $config;

    private $client;

    private $api;

    public function setUp(): void
    {
        $this->config = $this->createPartialMock(
            OAuthConfig::class,
            ['getClientSecret', 'getRedirectUri', 'getClientId', 'getEnvironment']
        );
        $this->config->method('getClientSecret')->willReturn('secret');
        $this->config->method('getRedirectUri')->willReturn('https://google.com');
        $this->config->method('getClientId')->willReturn('id');

        $storage = $this->createMock(SessionTokenStorage::class);
        $this->client = $this->createMock(Client::class);

        $this->api = new OAuthApi($this->config, $storage, $this->client);

        $storage->method('getTokens')
            ->willReturn(new PenneoTokens(
                'not_important',
                'refresh_token',
                10,
                20
            ));

        parent::setUp();
    }

    /** @dataProvider environmentProvider */
    public function testRefreshTokenMethodUsesCorrectHostname(string $environment, string $expectedHostname)
    {
        $this->config->method('getEnvironment')->willReturn($environment);
        $this->client->expects($this->once())
            ->method('post')
            ->with("https://{$expectedHostname}/oauth/token")
            ->willReturn($this->successfulResponse());

        $this->api->postTokenRefresh();
    }

    /** @dataProvider environmentProvider */
    public function testExchangeAuthCodeMethodUsesCorrectHostname(string $environment, string $expectedHostname)
    {
        $this->config->method('getEnvironment')->willReturn($environment);

        $this->client->expects($this->once())
            ->method('post')
            ->with("https://{$expectedHostname}/oauth/token")
            ->willReturn($this->successfulResponse());

        $this->api->postCodeExchange('someCode', 'someVerifier');
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
