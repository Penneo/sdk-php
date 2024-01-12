<?php

namespace Penneo\SDK\OAuth;

class UniqIdGenerator implements UniqueIdGenerator
{
    public function generate(): string
    {
        return uniqid('', true);
    }
}
