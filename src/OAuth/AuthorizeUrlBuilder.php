<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\OAuth\Config\Environment;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\PKCE\CodeChallenge;
use Penneo\SDK\PenneoSdkRuntimeException;

class AuthorizeUrlBuilder
{
    /** @var OAuthConfig */
    private $config;

    public function __construct(OAuthConfig $config)
    {
        $this->config = $config;
    }

    public function build(array $scope, CodeChallenge $codeChallenge, string $state = ''): string
    {
        if (null === $this->config->getRedirectUri()) {
            throw new PenneoSdkRuntimeException(
                'Cannot build redirect URL! Please set the redirectUri with ->setRedirectUri() when building ' .
                'the OAuth client!'
            );
        }

        $queryParameters = [
            'response_type' => 'code',
            'client_id' => $this->config->getClientId(),
            'redirect_uri' => $this->config->getRedirectUri(),
            'scope' => join('%20', $scope),
            'code_challenge_method' => $codeChallenge->getMethod(),
            'code_challenge' => $codeChallenge->getCodeChallenge(),
        ];

        if ($state) {
            $queryParameters['state'] = $state;
        }

        $query = http_build_query($queryParameters);
        $hostname = Environment::getOAuthHostname($this->config->getEnvironment());

        return "https://{$hostname}/oauth/authorize?$query";
    }
}
