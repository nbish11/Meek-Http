<?php

namespace Meek\Http;

use Meek\Http\DataCollection;
use Meek\Http\Cookie;

class CookieCollection extends DataCollection
{
    public function __construct(array $cookies = [])
    {
        $this->replace($cookies);
    }

    public function set($key, $value)
    {
        if (!$value instanceof Cookie) {
            $value = new Cookie($key, $value);
        }

        return parent::set($key, $value);
    }

    public function replace(array $cookies)
    {
        $this->clear();

        foreach ($cookies as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }
}
