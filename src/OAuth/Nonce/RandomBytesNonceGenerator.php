<?php

namespace Penneo\SDK\OAuth\Nonce;

use Exception;

class RandomBytesNonceGenerator implements NonceGenerator
{
    /** @throws Exception */
    public function generate(): string
    {
        return \random_bytes(64);
    }
}
