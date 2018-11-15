<?php

namespace Penneo\SDK;

use Psr\Http\Message\RequestInterface;

class WSSEHandler
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $secret;

    /**
     * WSSEHandler constructor.
     *
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function addAuthHeaders(RequestInterface $request)
    {
        $nonce = $this->getNonce();
        $created = date('r');
        $digest = $this->getDigest($nonce, $created, $this->secret);

        $wsseHeader = sprintf(
            'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $this->key,
            $digest,
            $nonce,
            $created
        );

        return $request
            ->withHeader('Authorization', 'WSSE profile="UsernameToken"')
            ->withHeader('X-WSSE', $wsseHeader);
    }

    private function getNonce()
    {
        return hash('sha256', uniqid('p-', true));
    }

    private function getDigest($nonce, $created, $secret)
    {
        return base64_encode(sha1(base64_decode($nonce) . $created . $secret, true));
    }
}
