<?php

namespace Meek\Http;

use Meek\Http\Response;
use Meek\Http\Status;

/**
 * Send a JSON response.
 */
class JsonResponse extends Response
{
    public function __construct($body = '', $code = 200)
    {
        parent::__construct($body, $code, [
            'Content-Type' => 'application/json'
        ]);
    }

    public function setBody($body, $contentType = 'application/json')
    {
        parent::setBody(json_encode($body));

        return $this;
    }
}
