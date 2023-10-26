<?php

namespace Penneo\SDK\OAuth;

class PenneoOAuthBuilder
{
    private function __construct()
    {
    }

    public static function start(): self
    {
        return new self();
    }

    public function setEnvironment(string $environment): self
    {
    }

    public function setClientId(string $string): self
    {
    }

    public function setClientSecret(string $string): self
    {
    }

    public function setRedirectUri(string $string): self
    {
    }

    public function setTokenStorage(TokenStorage $tokenStorage): self
    {
    }

    public function build(): PenneoOAuth
    {
    }
}