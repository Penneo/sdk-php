<?php

namespace Penneo\SDK\OAuth;

use GuzzleHttp\Client;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSDKException;

final class OAuthBuilder
{
    /** @var string */
    private $environment;
    /** @var string */
    private $clientId;
    /** @var string */
    private $clientSecret;
    /** @var string */
    private $redirectUri;
    /** @var TokenStorage */
    private $tokenStorage;

    private function __construct()
    {
    }

    public static function start(): self
    {
        return new self();
    }

    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $secret): self
    {
        $this->clientSecret = $secret;
        return $this;
    }

    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function setTokenStorage(TokenStorage $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }

    public function build(Client $client = null): OAuth
    {
        $this->validateAllParametersPresent();
        $this->validateEnvironment();
        $this->validateRedirectUri();

        return new OAuth(
            new OAuthConfig(
                $this->environment,
                $this->clientId,
                $this->clientSecret,
                $this->redirectUri
            ),
            $this->tokenStorage,
            $client ?: new Client()
        );
    }

    /** @throws PenneoSDKException */
    private function validateAllParametersPresent(): void
    {
        if (!$this->environment) {
            $this->throwMissingParameterError('environment');
        }
        if (!$this->clientId) {
            $this->throwMissingParameterError('clientId');
        }
        if (!$this->clientSecret) {
            $this->throwMissingParameterError('clientSecret');
        }
        if (!$this->redirectUri) {
            $this->throwMissingParameterError('redirectUri');
        }
        if (!$this->tokenStorage) {
            $this->throwMissingParameterError('tokenStorage');
        }
    }

    /** @throws PenneoSDKException */
    private function throwMissingParameterError(string $missingParameter): void
    {
        $capitalized = ucfirst($missingParameter);
        throw new PenneoSDKException("Cannot build! Please set the {$missingParameter} with ->set{$capitalized}()!");
    }

    /** @throws PenneoSDKException */
    private function validateEnvironment(): void
    {
        if ($this->environment != 'sandbox' && $this->environment != 'production') {
            throw new PenneoSDKException("Cannot build! Unknown environment '$this->environment'!");
        }
    }

    /** @throws PenneoSDKException */
    private function validateRedirectUri(): void
    {
        if (!filter_var($this->redirectUri, FILTER_VALIDATE_URL)) {
            throw new PenneoSDKException('Cannot build! The supplied redirect URI is not a valid URL!');
        }
    }
}
