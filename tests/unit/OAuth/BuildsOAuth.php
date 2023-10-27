<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use GuzzleHttp\Client;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\TokenStorage;

trait BuildsOAuth
{
    /** @var TokenStorage */
    private $tokenStorage;

    private function build(array $overrides = [], Client $guzzle = null): OAuth {
        $this->tokenStorage = $this->createStub(TokenStorage::class);

        $buildParams = array_merge([
            'clientId' => 'myId',
            'clientSecret' => 'mySecret',
            'redirectUri' => 'https://myUri.com',
            'tokenStorage' => $this->tokenStorage,
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