<?php

namespace Penneo\SDK\OAuth\Tokens;

use Carbon\Carbon;

class PenneoTokensValidator
{
    private const TOKEN_EXPIRY_BUFFER_IN_SECONDS = 5;

    public static function areNotExpired(?PenneoTokens $tokens = null): bool
    {
        if ($tokens === null) {
            return false;
        }

        $now = Carbon::now()->getTimestamp();

        return $tokens->getAccessToken()
            && ($now < $tokens->getAccessTokenExpiresAt() - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS
            || $now < $tokens->getRefreshTokenExpiresAt() - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS);
    }

    public static function isAccessTokenExpired(PenneoTokens $tokens): bool
    {
        $now = Carbon::now()->getTimestamp();
        return $now >= $tokens->getAccessTokenExpiresAt() - self::TOKEN_EXPIRY_BUFFER_IN_SECONDS;
    }
}
