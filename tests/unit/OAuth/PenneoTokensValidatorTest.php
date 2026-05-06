<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use DateTimeImmutable;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\PenneoTokensValidator;
use PHPUnit\Framework\TestCase;

class PenneoTokensValidatorTest extends TestCase
{
    public function testValidatesThePenneoTokensWhenBothAreExpired(): void
    {
        $tokens = new PenneoTokens(
            'access_token',
            (new DateTimeImmutable('yesterday'))->getTimestamp(),
            'refresh_token',
            (new DateTimeImmutable('yesterday'))->getTimestamp()
        );

        $this->assertFalse(PenneoTokensValidator::areNotExpired($tokens));
    }

    public function testReturnsTrueWhenBothTokensAreValid(): void
    {
        $tokens = new PenneoTokens(
            'access_token',
            (new DateTimeImmutable('tomorrow'))->getTimestamp(),
            'refresh_token',
            (new DateTimeImmutable('tomorrow'))->getTimestamp()
        );

        $this->assertTrue(PenneoTokensValidator::areNotExpired($tokens));
    }

    public function testReturnsTrueWhenAccessTokenIsExpiredAndRefreshTokenIsValid(): void
    {
        $tokens = new PenneoTokens(
            'access_token',
            (new DateTimeImmutable('yesterday'))->getTimestamp(),
            'refresh_token',
            (new DateTimeImmutable('tomorrow'))->getTimestamp()
        );

        $this->assertTrue(PenneoTokensValidator::areNotExpired($tokens));
    }

    public function testReturnsTrueWhenAccessTokenIsValidAndRefreshTokenIsExpired(): void
    {
        $tokens = new PenneoTokens(
            'access_token',
            (new DateTimeImmutable('tomorrow'))->getTimestamp(),
            'refresh_token',
            (new DateTimeImmutable('yesterday'))->getTimestamp()
        );

        $this->assertTrue(PenneoTokensValidator::areNotExpired($tokens));
    }

    /**
     * @testWith [5, "seconds"]
     *           [0, "seconds"]
     *           [-1, "day"]
     *           [-1, "year"]
     */
    public function testReturnsFalseWhenBothTokensAreExpired(int $timeDiffValue, string $timeDiffUnit)
    {
        $expiredTs = self::adjustNowByUnits($timeDiffValue, $timeDiffUnit)->getTimestamp();
        $tokens = new PenneoTokens('access_token', $expiredTs, 'refresh_token', $expiredTs);

        $this->assertFalse(PenneoTokensValidator::areNotExpired($tokens));
    }

    private static function adjustNowByUnits(int $timeDiffValue, string $timeDiffUnit): DateTimeImmutable
    {
        $base = new DateTimeImmutable('@' . time());

        return $base->modify(\sprintf('%+d %s', $timeDiffValue, $timeDiffUnit));
    }
}
