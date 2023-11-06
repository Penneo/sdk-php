<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

trait TestsEnvironments
{
    public static function environmentProvider(): array
    {
        return [
            ['sandbox', 'sandbox.oauth.penneo.cloud'],
            ['production', 'login.penneo.com'],
        ];
    }
}
