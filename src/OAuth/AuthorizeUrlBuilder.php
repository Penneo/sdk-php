<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\OAuth\Config\OAuthConfig;

class AuthorizeUrlBuilder
{
    /** @var OAuthConfig */
    private $config;

    public function __construct(OAuthConfig $config)
    {
        $this->config = $config;
    }

    public function build(array $scope, string $state = ''): string
    {
        $queryParameters = [
            'response_type' => 'code',
            'client_id' => $this->config->getClientId(),
            'redirect_uri' => $this->config->getRedirectUri(),
            'scope' => join('%20', $scope),
        ];

        if ($state) {
            $queryParameters['state'] = $state;
        }

        $query = http_build_query($queryParameters);

        return "https://{$this->config->getOAuthHostname()}/oauth/authorize?$query";
    }
}
