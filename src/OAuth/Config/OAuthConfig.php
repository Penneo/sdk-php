<?php

namespace Penneo\SDK\OAuth\Config;

/** @internal */
class OAuthConfig
{
    /**
     * @var string
     */
    private $environment;
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $redirectUri;

    public function __construct(
        string $environment,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->environment = $environment;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getOAuthHostname(): string
    {
        return $this->getEnvironment() === 'sandbox' ? 'sandbox.oauth.penneo.cloud' : 'login.penneo.com';
    }
}
