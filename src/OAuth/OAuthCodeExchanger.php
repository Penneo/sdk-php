<?php

namespace Penneo\SDK\OAuth;

class OAuthCodeExchanger
{

    public static function exchangeCode($code, CodeChallenge $codeChallenge): PenneoTokens
    {
        return new PenneoTokens("access", "refresh");
    }
}