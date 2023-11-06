<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\PenneoSDKException;

class OAuth
{
    /** @var AuthorizeUrlBuilder */
    private $urlBuilder;

    /** @var OAuthConfig */
    private $config;

    public function __construct(OAuthConfig $config)
    {
        $this->config = $config;
        $this->urlBuilder = new AuthorizeUrlBuilder($config);
    }

    /** @throws PenneoSDKException */
    public function buildRedirectUrl(array $scope, string $state = ''): string
    {
        return $this->urlBuilder->build($scope, $state);
    }

    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
    }
}
