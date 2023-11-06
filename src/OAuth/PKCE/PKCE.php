<?php

namespace Penneo\SDK\OAuth\PKCE;

class PKCE
{
    public function getCodeVerifier(): string
    {
        $random = bin2hex(openssl_random_pseudo_bytes(96));

        return $this->base64UrlEncode(pack('H*', $random));
    }

    private function base64UrlEncode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function getCodeChallenge(string $codeVerifier): CodeChallenge
    {
        return new CodeChallenge(
            $this->base64UrlEncode(pack('H*', hash('sha256', $codeVerifier))),
            CodeChallenge::S256
        );
    }
}
