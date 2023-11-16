<?php

namespace Penneo\SDK\OAuth\PKCE;

class CodeChallenge
{
    public const S256 = "S256";

    private $codeChallenge;

    public function __construct(string $codeChallenge)
    {
        $this->codeChallenge = $codeChallenge;
    }

    public function getCodeChallenge(): string
    {
        return $this->codeChallenge;
    }

    public function getMethod(): string
    {
        return self::S256;
    }
}
