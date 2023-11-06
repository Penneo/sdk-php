<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use PHPUnit\Framework\TestCase;

class SessionTokenStorageTest extends TestCase
{
    /**
     * @testWith ["hello", "goodbye", 1, 2]
     *           ["hi", "bye", 5, 1]
     */
    public function testSetAndGetReturnsSameValues(
        string $accessToken,
        string $refreshToken,
        int $accessTokenExpiresAt,
        int $refreshTokenExpiresAt
    ): void {
        $tokens = new PenneoTokens($accessToken, $refreshToken, $accessTokenExpiresAt, $refreshTokenExpiresAt);
        $storage = new SessionTokenStorage();

        $storage->saveTokens($tokens);
        $loaded = $storage->getTokens();

        $this->assertEquals($loaded->getAccessToken(), $accessToken);
        $this->assertEquals($loaded->getRefreshToken(), $refreshToken);
        $this->assertEquals($loaded->getAccessTokenExpiresAt(), $accessTokenExpiresAt);
        $this->assertEquals($loaded->getRefreshTokenExpiresAt(), $refreshTokenExpiresAt);
    }
}
