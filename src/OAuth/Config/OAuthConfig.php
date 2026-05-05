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
     * @var string|null
     */
    private $redirectUri;
    /**
     * @var string|null
     */
    private $apiKey;
    /**
     * @var string|null
     */
    private $apiSecret;

    public function __construct(
        string $environment,
        string $clientId,
        string $clientSecret,
        ?string $redirectUri = null,
        ?string $apiKey = null,
        ?string $apiSecret = null
    ) {
        $this->environment = $environment;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
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

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }
}
