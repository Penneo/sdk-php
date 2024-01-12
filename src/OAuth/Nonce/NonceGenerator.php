<?php

namespace Penneo\SDK\OAuth\Nonce;

interface NonceGenerator
{
    public function generate(): string;
}
