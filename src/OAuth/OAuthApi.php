<?php

namespace Penneo\SDK\OAuth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Penneo\SDK\OAuth\Config\OAuthConfig;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\PenneoSDKException;
use Psr\Http\Message\ResponseInterface;

/** @internal */
final class OAuthApi
{
    /** @var OAuthConfig */
    private $config;

    /** @var Client */
    private $client;

    public function __construct(OAuthConfig $config, Client $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /** @throws PenneoSDKException */
    public function postCodeExchange(string $code, string $codeVerifier): PenneoTokens
    {
        return $this->postOrThrow(
            $this->buildCodeExchangePayload($code, $codeVerifier),
            "exchange code"
        );
    }

    /** @throws PenneoSDKException */
    private function postOrThrow(array $payload, string $actionDescription): PenneoTokens
    {
        try {
            return $this->post($payload);
        } catch (BadResponseException $e) {
            $this->handleBadResponse($e->getResponse(), "Failed to $actionDescription");
        } catch (GuzzleException $e) {
            throw new PenneoSDKException("Unexpected error occurred: {$e->getMessage()}", $e);
        }
    }

    /**
     * @throws GuzzleException
     * @throws BadResponseException
     */
    private function post(array $payload): PenneoTokens
    {
        $response = $this->client->post(
            "https://{$this->config->getOAuthHostname()}/oauth/token",
            ['json' => $payload]
        );

        $result = json_decode($response->getBody());

        return new PenneoTokens(
            $result->access_token,
            $result->refresh_token,
            $result->access_token_expires_at,
            $result->refresh_token_expires_at
        );
    }

    /** @throws PenneoSDKException */
    private function handleBadResponse(ResponseInterface $response, string $title)
    {
        $body = json_decode($response->getBody());
        $code = $response->getStatusCode();

        $message = $body->error ?? 'Unknown error';
        $description = isset($body->error_description) ? " {$body->error_description}" : '';

        throw new PenneoSDKException(
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
}
