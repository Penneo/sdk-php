<?php

namespace Penneo\SDK\OAuth\Tokens;

/** @internal */
final class PenneoTokens
{
    private $refreshToken;

    private $accessToken;

    private $accessTokenExpiresAt;

    private $refreshTokenExpiresAt;

    /** @return string|null */
    public function getRefreshToken()
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

    /** @return int|null */
    public function getRefreshTokenExpiresAt()
    {
        return $this->refreshTokenExpiresAt;
    }

    public function __construct(
        string $accessToken,
        int $accessTokenExpiresAt,
        ?string $refreshToken = null,
        ?int $refreshTokenExpiresAt = null
    ) {
        $this->accessToken = $accessToken;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        $this->refreshToken = $refreshToken;
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
            $json['accessTokenExpiresAt'],
            $json['refreshToken'] ?? null,
            $json['refreshTokenExpiresAt'] ?? null
        );
    }
}
