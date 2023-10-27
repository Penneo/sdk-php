<?php

namespace Penneo\SDK\OAuth;

interface TokenStorage {
    function saveTokens(PenneoTokens $tokens);
    function getTokens(): ?PenneoTokens;
}

