<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\OAuthBuilder;

trait BuildsOAuth
{
    /**
     * @param array{
     *     clientId: ?string,
     *     clientSecret: ?int,
     *     redirectUri: ?string,
     *     environment: ?string
     * } $overrides
     */
    private function build(array $overrides = [], Client $guzzle = null): OAuth
    {
        $buildParams = array_merge([
            'clientId' => 'myId',
            'clientSecret' => 'mySecret',
            'redirectUri' => 'https://myUri.com',
            'environment' => 'sandbox',
        ], $overrides);

        $builder = OAuthBuilder::start();

        foreach ($buildParams as $key => $value) {
            if ($value) {
                $builder->{"set$key"}($value);
            }
        }

        return $builder->build($guzzle);
    }
}
