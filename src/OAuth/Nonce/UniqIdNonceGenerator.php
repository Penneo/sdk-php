<?php

namespace Penneo\SDK\OAuth\Nonce;

class UniqIdNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        return substr(hash('sha512', uniqid('', true)), 0, 64);
    }
}
