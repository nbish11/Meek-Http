<?php

namespace Meek\Http;

use Meek\Http\Request;
use Meek\Http\Response;

/*
// Create the request
$request = new Meek\Http\Request();
$request->setMethod('GET');
$request->setUri(new Uri('http://google.com'));
$request->setServer($_SERVER);

// Dispatch the request using CURL and retrieve the response.
$client = new Meek\Http\Client($request);
$response = $client->dispatch();
*/
class Client
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function dispatch()
    {
        // do stuff with CURL

        // return response
    }
}
