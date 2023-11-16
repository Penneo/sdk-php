<?php

namespace Penneo\SDK\OAuth\Config;

use Penneo\SDK\PenneoSdkRuntimeException;

final class Environment
{
    public const SANDBOX = 'sandbox';
    public const PRODUCTION = 'production';

    private const SERVICE_OAUTH = 'oauth';
    private const SERVICE_SIGN = 'sign';

    private const HOSTNAMES = [
        self::SANDBOX => [
            self::SERVICE_OAUTH => 'sandbox.oauth.penneo.cloud',
            self::SERVICE_SIGN => 'sandbox.penneo.com'
        ],
        self::PRODUCTION => [
            self::SERVICE_OAUTH => 'login.penneo.com',
            self::SERVICE_SIGN => 'app.penneo.com'
        ]
    ];

    public static function isSupported(string $environment): bool
    {
        return in_array($environment, [self::SANDBOX, self::PRODUCTION], true);
    }

    public static function getOAuthHostname(string $environment): string
    {
        return self::getHostnames($environment)[self::SERVICE_OAUTH];
    }

    public static function getSignHostname(string $environment): string
    {
        return self::getHostnames($environment)[self::SERVICE_SIGN];
    }

    private static function getHostnames(string $environment): array
    {
        if (!self::isSupported($environment)) {
            throw new PenneoSdkRuntimeException("Unknown environment '$environment'!");
        }

        return self::HOSTNAMES[$environment];
    }
}
