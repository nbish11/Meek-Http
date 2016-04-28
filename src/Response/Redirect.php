<?php

namespace Meek\Http;

use Meek\Http\Response;
use Meek\Http\Status;

/**
 * Sends a redirected response.
 */
class RedirectedResponse extends Response
{
    public function __construct($url, $code = 302)
    {
        parent::__construct('', $code, [
            'Location' => $url
        ]);
    }
}
