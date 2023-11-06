<?php

namespace Penneo\SDK\OAuth\Tokens;

interface TokenStorage
{
    public function saveTokens(PenneoTokens $tokens);
    public function getTokens(): ?PenneoTokens;
}
