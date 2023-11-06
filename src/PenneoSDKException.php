<?php

namespace Penneo\SDK;

class PenneoSDKException extends \RuntimeException
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
