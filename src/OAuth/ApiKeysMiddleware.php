<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\PenneoTokensValidator;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Psr\Http\Message\RequestInterface;

class ApiKeysMiddleware
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var OAuthApi */
    private $api;

    public function __construct(TokenStorage $tokenStorage, OAuthApi $api)
    {
        $this->tokenStorage = $tokenStorage;
        $this->api = $api;
    }

    public function handleRequest(RequestInterface $request): RequestInterface
    {
        $this->refreshAccessToken();

        return $request->withHeader(
            'Authorization',
            "Bearer {$this->tokenStorage->getTokens()->getAccessToken()}"
        );
    }

    private function refreshAccessToken(): void
    {
        $tokens = $this->tokenStorage->getTokens();

        if (null === $tokens || PenneoTokensValidator::isAccessTokenExpired($tokens)) {
            $this->tokenStorage->saveTokens(
                $this->api->postApiKeyExchange()
            );
        }
    }
}
