<?php

namespace Penneo\SDK\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\PKCE\CodeChallenge;
use Penneo\SDK\OAuth\Tokens\PenneoTokenValidator;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSDKException;

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

    /** @throws PenneoSDKException */
    public function buildRedirectUrl(array $scope, CodeChallenge $codeChallenge, string $state = ''): string
    {
        return $this->urlBuilder->build($scope, $codeChallenge, $state);
    }

    /** @throws PenneoSDKException */
    public function exchangeAuthCode(string $code, string $codeVerifier): void
    {
        $this->tokenStorage->saveTokens(
            $this->api->postCodeExchange($code, $codeVerifier)
        );
    }

    /**
     * @throws PenneoSDKException
     * @internal
     */
    public function getMiddleware(): callable
    {
        if (!PenneoTokenValidator::isValid($this->tokenStorage->getTokens())) {
            throw new PenneoSDKException('The access token is missing or expired! Did you complete the OAuth flow?');
        }

        return Middleware::mapRequest([
            new OAuthMiddleware($this->tokenStorage, $this->api),
            'handleRequest'
        ]);
    }

    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }
}