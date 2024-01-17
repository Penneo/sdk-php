<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\OAuth\Tokens\TokenStorage;

trait BuildsOAuth
{
    /**
     * @param array{
     *     clientId: ?string,
     *     clientSecret: ?int,
     *     redirectUri: ?string,
     *     tokenStorage: ?TokenStorage,
     *     environment: ?string,
     *     apiKey: ?string,
     *     apiSecret: ?string,
     * } $overrides
     */
    private function build(array $overrides = [], Client $guzzle = null): OAuth
    {
        $buildParams = array_merge([
            'clientId' => 'myId',
            'clientSecret' => 'mySecret',
            'redirectUri' => 'https://myUri.com',
            'tokenStorage' => $this->createStub(SessionTokenStorage::class),
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
