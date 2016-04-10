<?php

namespace Meek\Http\Collections;

use Meek\Http\DataCollection;

class ServerData extends DataCollection
{
    private static $headerPrefix = 'HTTP_';
    private static $nonPrefixedHeaders = ['CONTENT_LENGTH', 'CONTENT_TYPE', 'CONTENT_MD5'];

    public function getHeaders()
    {
        $headers = [];

        foreach ($this->all() as $key => $value) {
            if (static::hasPrefix($key, static::$headerPrefix)) {
                $headers[substr($key, strlen(static::$headerPrefix))] = $value;
            } else if (in_array($key, static::$nonPrefixedHeaders)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    protected static function hasPrefix($string, $prefix)
    {
        return strpos($string, $prefix) === 0;
    }
}
