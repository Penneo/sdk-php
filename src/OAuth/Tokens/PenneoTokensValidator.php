<?php

namespace Penneo\SDK\OAuth\Tokens;

class PenneoTokensValidator
{
    private const TOKEN_EXPIRY_BUFFER_IN_SECONDS = 5;

    public static function areNotExpired(?PenneoTokens $tokens = null): bool
    {
        if ($tokens === null) {
            return false;
        }

        $now = \time();

        $refreshExp = $tokens->getRefreshTokenExpiresAt();

        return $tokens->getAccessToken()
            && ($now < $tokens->getAccessTokenExpiresAt() - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS
            || ($refreshExp !== null && $now < $refreshExp - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS));
    }

    public static function isAccessTokenExpired(PenneoTokens $tokens): bool
    {
        return \time() >= $tokens->getAccessTokenExpiresAt() - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS;
    }
}
