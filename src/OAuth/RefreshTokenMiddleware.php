<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\AuthenticationExpiredException;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\PenneoTokensValidator;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Psr\Http\Message\RequestInterface;

class RefreshTokenMiddleware
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
        $tokens = $this->tokenStorage->getTokens();

        $this->validateBothTokensNotExpired($tokens);
        $this->refreshExpiredAccessToken($tokens);

        return $request->withHeader(
            'Authorization',
            "Bearer {$this->tokenStorage->getTokens()->getAccessToken()}"
        );
    }

    /** @throws AuthenticationExpiredException */
    private function validateBothTokensNotExpired(PenneoTokens $tokens): void
    {
        if (!PenneoTokensValidator::areNotExpired($tokens)) {
            throw new AuthenticationExpiredException('Session has expired, please reauthenticate!');
        }
    }

    private function refreshExpiredAccessToken(PenneoTokens $tokens): void
    {
        if (PenneoTokensValidator::isAccessTokenExpired($tokens)) {
            $this->tokenStorage->saveTokens(
                $this->api->postTokenRefresh()
            );
        }
    }
}
