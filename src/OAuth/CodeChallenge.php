<?php

namespace Penneo\SDK\OAuth;

interface CodeChallenge
{
    public function serialize(): string;

    public function getChallenge(): string;

    public function getMethod(): string;
}