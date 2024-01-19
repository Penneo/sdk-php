<?php

namespace Penneo\SDK\OAuth\Tokens;

class SessionTokenStorage implements TokenStorage
{
    /** @var string */
    private $keyInSession;

    public function __construct(string $keyInSession = 'penneoOAuthTokens')
    {
        $this->keyInSession = $keyInSession;
    }

    public function saveTokens(PenneoTokens $tokens)
    {
        $_SESSION[$this->keyInSession] = $tokens->serialize();
    }

    public function getTokens(): ?PenneoTokens
    {
        if (false === empty($_SESSION[$this->keyInSession])) {
            return PenneoTokens::deserialize($_SESSION[$this->keyInSession]);
        }

        return null;
    }
}
