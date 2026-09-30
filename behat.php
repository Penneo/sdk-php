<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->withPaths(__DIR__ . '/tests/integration')
                    ->withContexts(
                        \Penneo\SDK\Tests\Integration\SdkContext::class,
                        \Penneo\SDK\Tests\Integration\MessageTemplateContext::class,
                        \Penneo\SDK\Tests\Integration\CustomerContext::class,
                        \Penneo\SDK\Tests\Integration\UserContext::class,
                    )
            )
    );
