<?php

namespace Penneo\SDK\OAuth;

interface UniqueIdGenerator
{
    public function generate(): string;
}
