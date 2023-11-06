<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use PHPUnit\Framework\TestCase;

class PenneoTokensTest extends TestCase
{
    /**
     * @testWith ["hello", "goodbye", 88, 11]
     *           ["hi", "bye", 6, 7]
     */
    public function testSerializingAndDeserializingResultsInTheSameData(
        string $accessToken,
        string $refreshToken,
        int $accessTokenExpiresAt,
        int $refreshTokenExpiresAt
    ): void {
        $tokens = new PenneoTokens($accessToken, $refreshToken, $accessTokenExpiresAt, $refreshTokenExpiresAt);
        $deserialized = PenneoTokens::deserialize($tokens->serialize());

        $this->assertEquals($accessToken, $deserialized->getAccessToken());
        $this->assertEquals($refreshToken, $deserialized->getRefreshToken());
        $this->assertEquals($accessTokenExpiresAt, $deserialized->getAccessTokenExpiresAt());
        $this->assertEquals($refreshTokenExpiresAt, $deserialized->getRefreshTokenExpiresAt());
    }
}
