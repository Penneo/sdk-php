<?php

declare(strict_types=1);

namespace Penneo\SDK;

use Psr\Http\Message\RequestInterface;

class WsseMiddleware
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $apiSecret;

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public function authorize(RequestInterface $request): RequestInterface
    {
        return $request
            ->withHeader('Authorization', 'WSSE profile="UsernameToken"')
            ->withHeader('X-WSSE', $this->makeWsseHeader());
    }

    private function makeDigest(string $nonce, string $created, string $password): string
    {
        return base64_encode(sha1(base64_decode($nonce) . $created . $password, true));
    }

    private function makeNonce(): string
    {
        return hash('sha512', uniqid('', true));
    }

    private function makeWsseHeader(): string
    {
        $nonce   = $this->makeNonce();
        $created = date('r');
        $digest  = $this->makeDigest($nonce, $created, $this->apiSecret);

        return sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $this->apiKey,
            $digest,
            $nonce,
            $created
        );
    }
}
