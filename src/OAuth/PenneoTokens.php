<?php

namespace Penneo\SDK\OAuth;

final class PenneoTokens {
    private $refreshToken;

    private $accessToken;

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function __construct(string $accessToken, string $refreshToken)
    {
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
    }

    public function serialize(): string {
        return json_encode([
            'accessToken' => $this->getAccessToken(),
            'refreshToken' => $this->getRefreshToken(),
        ]);
    }

    public static function deserialize(string $tokens): PenneoTokens {
        $json = json_decode($tokens);
        return new PenneoTokens($json['accessToken'], $json['refreshToken']);
    }
}
