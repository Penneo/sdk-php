<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\Config\Environment;

trait TestsEnvironments
{
    public static function environmentProvider(): array
    {
        return [
            [Environment::SANDBOX, 'sandbox.oauth.penneo.cloud'],
            [Environment::PRODUCTION, 'login.penneo.com'],
        ];
    }
}
