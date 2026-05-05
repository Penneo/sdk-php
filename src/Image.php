<?php

namespace Penneo\SDK;

class Image extends Entity
{
    protected static $relativeUrl = 'images';

    protected $url;

    protected $customer;

    public function __construct(?Customer $customer = null)
    {
        $this->customer = $customer;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
