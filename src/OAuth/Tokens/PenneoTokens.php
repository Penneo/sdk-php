<?php

namespace Penneo\SDK\OAuth\Tokens;

/** @internal */
final class PenneoTokens
{
    private $refreshToken;

    private $accessToken;

    private $accessTokenExpiresAt;

    private $refreshTokenExpiresAt;

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getAccessTokenExpiresAt(): int
    {
        return $this->accessTokenExpiresAt;
    }

    public function getRefreshTokenExpiresAt(): int
    {
        return $this->refreshTokenExpiresAt;
    }

    public function __construct(
        string $accessToken,
        string $refreshToken,
        int $accessTokenExpiresAt,
        int $refreshTokenExpiresAt
    ) {
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
    }

    public function serialize(): string
    {
        return json_encode([
            'accessToken' => $this->getAccessToken(),
            'refreshToken' => $this->getRefreshToken(),
            'accessTokenExpiresAt' => $this->getAccessTokenExpiresAt(),
            'refreshTokenExpiresAt' => $this->getRefreshTokenExpiresAt(),
        ]);
    }

    public static function deserialize(string $tokens): PenneoTokens
    {
        $json = json_decode($tokens, true);

        return new PenneoTokens(
            $json['accessToken'],
            $json['refreshToken'],
            $json['accessTokenExpiresAt'],
            $json['refreshTokenExpiresAt']
        );
    }
}
