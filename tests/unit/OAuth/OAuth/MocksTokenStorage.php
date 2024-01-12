<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;

trait MocksTokenStorage
{
    private function mockTokenStorage($tokens = null): SessionTokenStorage
    {
        $mockStorage = $this->createMock(SessionTokenStorage::class);
        $mockStorage->method('getTokens')
            ->willReturnCallback(function () use (&$tokens) {
                return $tokens;
            });

        $mockStorage->method('saveTokens')
            ->willReturnCallback(function ($input) use (&$tokens) {
                $tokens = $input;
            });

        return $mockStorage;
    }

}
