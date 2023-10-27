<?php

namespace Penneo\SDK\OAuth;

use Penneo\SDK\PenneoSDKException;

class OAuth
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
//    /** @var ?CodeChallenge */
//    private $codeChallenge;

    public function __construct(
        string $environment,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        TokenStorage $tokenStorage
    ) {
        $this->environment = $environment;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->tokenStorage = $tokenStorage;
    }
    public function exchangeAuthCode(string $code)
    {
        if (!$code) {
            throw new PenneoSDKException('Cannot exchange code! Provided code should not be empty!');
        }
    }

//    public function isAuthorized(): bool
//    {
//        return !!$this->tokenStorage->getTokens();
//    }

//    public function setUpS256CodeChallenge(): string
//    {
//        $this->codeChallenge = new S256CodeChallenge();
//        return $this->codeChallenge->serialize();
//    }

    const SERVER = "https://sandbox.oauth.penneo.cloud";

    public function buildRedirectUrl(string $scope = '', string $state = ''): string
    {
        // TODO: different states
        if ($scope !== 'read') {
            throw new PenneoSDKException("Cannot build URL! Unknown scope '${scope}'!");
        }

        // TODO: different environments
        // TODO: validate state

        $queryParameters = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $scope,
            'state' => $state,
        ];

        // TODO: implement code challenge
//        if ($this->codeChallenge) {
//            $queryParameters['code_challenge_method'] = $this->codeChallenge->getMethod();
//            $queryParameters['code_challenge'] = $this->codeChallenge->getChallenge();
//        }

        $query = http_build_query($queryParameters);
        $base = self::SERVER;

        return "$base/oauth/token?$query";
    }

//    public function useCodeChallenge(string $serializedCodeChallenge)
//    {
//        $this->codeChallenge = CodeChallengeFactory::fromSerialized($serializedCodeChallenge);
//    }
}