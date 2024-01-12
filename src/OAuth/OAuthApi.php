<?php

namespace Penneo\SDK\OAuth;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Penneo\SDK\OAuth\Config\Environment;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\TokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;
use Psr\Http\Message\ResponseInterface;

/** @internal */
final class OAuthApi
{
    /** @var OAuthConfig */
    private $config;

    /** @var Client */
    private $client;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var UniqueIdGenerator */
    private $idGenerator;

    public function __construct(
        OAuthConfig $config,
        TokenStorage $tokenStorage,
        Client $client,
        UniqueIdGenerator $idGenerator = null
    ) {
        $this->config = $config;
        $this->tokenStorage = $tokenStorage;
        $this->client = $client;
        $this->idGenerator = $idGenerator ?? new UniqIdGenerator();
    }

    /** @throws PenneoSdkRuntimeException */
    public function postCodeExchange(string $code, string $codeVerifier): PenneoTokens
    {
        return $this->postOrThrow(
            $this->buildCodeExchangePayload($code, $codeVerifier),
            "exchange code"
        );
    }

    /** @throws PenneoSdkRuntimeException */
    private function postOrThrow(array $payload, string $actionDescription): PenneoTokens
    {
        try {
            return $this->post($payload);
        } catch (BadResponseException $e) {
            $this->handleBadResponse($e->getResponse(), "Failed to $actionDescription");
        } catch (GuzzleException $e) {
            throw new PenneoSdkRuntimeException("Unexpected error occurred: {$e->getMessage()}", $e);
        }
    }

    /**
     * @throws GuzzleException
     * @throws BadResponseException
     */
    private function post(array $payload): PenneoTokens
    {
        $hostname = Environment::getOAuthHostname($this->config->getEnvironment());

        $response = $this->client->post(
            "https://{$hostname}/oauth/token",
            ['json' => $payload]
        );

        $result = json_decode($response->getBody());

        return new PenneoTokens(
            $result->access_token,
            $result->refresh_token ?? null,
            $result->access_token_expires_at,
            $result->refresh_token_expires_at ?? null
        );
    }

    /** @throws PenneoSdkRuntimeException */
    private function handleBadResponse(ResponseInterface $response, string $title)
    {
        $body = json_decode($response->getBody());
        $code = $response->getStatusCode();

        $message = $body->error ?? 'Unknown error';
        $description = isset($body->error_description) ? " {$body->error_description}" : '';

        throw new PenneoSdkRuntimeException(
            "$title: HTTP {$code}, {$message}{$description}"
        );
    }

    private function buildCodeExchangePayload(string $code, string $codeVerifier): array
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'code' => $code,
            'redirect_uri' => $this->config->getRedirectUri(),
            'code_verifier' => $codeVerifier
        ];
    }

    /** @throws PenneoSdkRuntimeException */
    public function postTokenRefresh(): PenneoTokens
    {
        return $this->postOrThrow(
            $this->buildTokenRefreshPayload(),
            "refresh tokens"
        );
    }

    private function buildTokenRefreshPayload(): array
    {
        return [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->tokenStorage->getTokens()->getRefreshToken(),
            'redirect_uri' => $this->config->getRedirectUri(),
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
        ];
    }
    public function postApiKeyExchange(): PenneoTokens
    {
        return $this->postOrThrow(
            $this->buildApiKeyExchangePayload(),
            "exchange api key and secret for access token"
        );
    }

    private function buildApiKeyExchangePayload(): array
    {
        $createdAt = Carbon::now()->toString();
        $nonce = substr(hash('sha512', $this->idGenerator->generate()), 0, 64);;
        $digest = base64_encode(sha1($nonce . $createdAt . $this->config->getApiSecret(), true));

        return [
            'grant_type' => 'api_keys',
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'key' => $this->config->getApiKey(),
            'created_at' => $createdAt,
            'nonce' => $nonce,
            'digest' => $digest
        ];
    }
}
