<?php

/**
 * Gets the current HTTP request as sent by the client.
 *
 * @return Psr\Http\Message\RequestInterface The current request.
 */
function get_current_request() {
    static $request;

    if (!$request) {
        $request = Meek\Http\ServerRequest::createFromGlobals();
    }

    return $request;
}

/**
 * Sends a HTTP response to the client.
 *
 * @param string  $body    The response body to send to the client.
 * @param integer $code    The HTTP status code.
 * @param array   $headers A list of optional headers to add to the response.
 *
 * @return Psr\Http\Message\RequestInterface The sent response.
 */
function send_response($body = '', $code = 200, array $headers = []) {
    $response = new Meek\Http\Response(null, $code, $headers);
    $response->getBody()->write($body);

    return $response->prepare(get_current_request())->send();
}
