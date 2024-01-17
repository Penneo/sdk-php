<?php

namespace Penneo\SDK\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\PKCE\CodeChallenge;
use Penneo\SDK\OAuth\Tokens\PenneoTokensValidator;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;

class OAuth
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var OAuthApi */
    private $api;

    /** @var AuthorizeUrlBuilder */
    private $urlBuilder;

    /** @var OAuthConfig */
    private $config;

    public function __construct(OAuthConfig $config, TokenStorage $tokenStorage, Client $client)
    {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->urlBuilder = new AuthorizeUrlBuilder($config);
        $this->api = new OAuthApi($config, $tokenStorage, $client);
    }

    /** @throws PenneoSdkRuntimeException */
    public function buildRedirectUrl(array $scope, CodeChallenge $codeChallenge, string $state = ''): string
    {
        return $this->urlBuilder->build($scope, $codeChallenge, $state);
    }

    /** @throws PenneoSdkRuntimeException */
    public function exchangeAuthCode(string $code, string $codeVerifier): void
    {
        $this->tokenStorage->saveTokens(
            $this->api->postCodeExchange($code, $codeVerifier)
        );
    }

    public function isAuthorized(): bool
    {
        return PenneoTokensValidator::areNotExpired($this->tokenStorage->getTokens());
    }

    /**
     * @throws PenneoSdkRuntimeException
     * @internal
     */
    public function getMiddleware(): callable
    {
        if ($this->config->getApiKey() && $this->config->getApiSecret()) {
            return Middleware::mapRequest([
                new ApiKeysMiddleware($this->tokenStorage, $this->api),
                'handleRequest'
            ]);
        }

        if (!PenneoTokensValidator::areNotExpired($this->tokenStorage->getTokens())) {
            throw new PenneoSdkRuntimeException(
                'The access token is missing or expired! Did you complete the OAuth flow?'
            );
        }

        return Middleware::mapRequest([
            new RefreshTokenMiddleware($this->tokenStorage, $this->api),
            'handleRequest'
        ]);
    }

    /**
     * @internal
     */
    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }
}
