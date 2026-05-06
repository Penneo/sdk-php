<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\OAuth\Tokens\PenneoTokensValidator;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;
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

        $tokens = $this->tokenStorage->getTokens();
        if ($tokens === null) {
            throw new PenneoSdkRuntimeException(
                'OAuth tokens are not available. Complete an OAuth or API key exchange flow first.'
            );
        }

        return $request->withHeader(
            'Authorization',
            "Bearer {$tokens->getAccessToken()}"
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
