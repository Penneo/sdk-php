<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Carbon\Carbon;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\Tests\Unit\OAuth\BuildsOAuth;
use PHPUnit\Framework\TestCase;

class IsAuthorizedTest extends TestCase
{
    use BuildsOAuth;

    public function testWhenNoTokensPresentThenItReturnsFalse()
    {
        $tokenStorage = $this->createMock(SessionTokenStorage::class);
        $tokenStorage->method('getTokens')
            ->willReturn(null);

        $oauth = $this->build([
            'tokenStorage' => $tokenStorage,
        ]);
        $this->assertFalse($oauth->isAuthorized());
    }

    public function testWhenTokensArePresentButExpiredReturnsFalse()
    {
        $oauth = $this->prepare(0, 0);
        $this->assertFalse($oauth->isAuthorized());
    }

    public function testWhenAccessTokenValidButRefreshTokenExpiredReturnsTrue()
    {
        $oauth = $this->prepare(PHP_INT_MAX, 0);
        $this->assertTrue($oauth->isAuthorized());
    }

    public function testWhenAccessTokenExpiredButRefreshTokenValidReturnsTrue()
    {
        $oauth = $this->prepare(0, PHP_INT_MAX);
        $this->assertTrue($oauth->isAuthorized());
    }

    public function prepare(int $accessTokenExpiresAt, int $refreshTokenExpiresAt): OAuth
    {
        $tokenStorage = $this->createMock(SessionTokenStorage::class);
        $tokenStorage->method('getTokens')
            ->willReturn(
                new PenneoTokens('a', 'b', $accessTokenExpiresAt, $refreshTokenExpiresAt)
            );

        return $this->build([
            'tokenStorage' => $tokenStorage,
        ]);
    }
}
