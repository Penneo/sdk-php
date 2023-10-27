<?php

namespace Penneo\SDK\OAuth;

// TODO
class S256CodeChallenge implements CodeChallenge {

    public function serialize(): string
    {
        return 'dank';
    }

    public function getChallenge(): string
    {
    }

    public function getMethod(): string
    {
        return 'S256';
    }
}
