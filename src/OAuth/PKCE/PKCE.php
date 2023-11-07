<?php

namespace Penneo\SDK\OAuth\PKCE;

class PKCE
{
    public function getCodeVerifier(): string
    {
        $random = bin2hex(openssl_random_pseudo_bytes(96));

        return $this->base64UrlEncode(pack('H*', $random));
    }

    /**
     * We use an url-safe base64 encoding method so that the code challenge and its verifier can be passed safely as a
     * GET parameter to web requests.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4648#section-5
     */
    private function base64UrlEncode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function getCodeChallenge(string $codeVerifier): CodeChallenge
    {
        return new CodeChallenge(
            $this->base64UrlEncode(pack('H*', hash('sha256', $codeVerifier)))
        );
    }
}
