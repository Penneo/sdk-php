<?php

namespace Penneo\SDK\OAuth;

class SessionTokenStorage implements TokenStorage {
    /** @var string */
    private $keyInSession;

    public function __construct(string $keyInSession = 'penneoOAuthTokens') {
        $this->keyInSession = $keyInSession;
    }

    function saveTokens(PenneoTokens $tokens) {
        $_SESSION[$this->keyInSession] = $tokens->serialize();
    }

    function getTokens(): ?PenneoTokens {
        if ($_SESSION[$this->keyInSession]) {
            return PenneoTokens::deserialize($_SESSION[$this->keyInSession]);
        }
    }
}
