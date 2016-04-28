<?php

namespace Meek\Http;

use Meek\Http\Message;
use Psr\Http\Message\RequestInterface as PsrHttpRequest;
use Psr\Http\Message\UriInterface as PsrHttpUri;
use Meek\Http\DataCollection;
use Meek\Http\CookieCollection;
use Meek\Http\Collections\ServerData as ServerDataCollection;
use Meek\Http\HeaderCollection;
use Meek\Http\Collections\FileList as FileListCollection;
use Meek\Http\Uri;
use Meek\Http\Session;
use InvalidArgumentException;

class Request implements PsrHttpRequest
{
    use Message;

    protected $uri;
    protected $method;
    protected $protocol;
    protected $requestTarget;

    public function __construct($uri, $method = 'GET', $headers = [], $body = '', $protocol = '1.1')
    {
        $this->uri = $uri instanceof $uri ? $uri : new Uri($uri);
        $this->method = $method;
        $this->setHeaders($headers);
        $this->body = $body instanceof PsrHttpStream ? $body : new Stream('php://temp', 'w+');
        $this->protocol = $protocol;

        // set request target
        $path = $this->uri->getPath();

        if (empty($path)) {
            $path = '/';
        }

        $query = $this->uri->getQuery();

        if (!empty($query)) {
            $path = $path . '?' . $query;
        }

        $this->requestTarget = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $request = clone $this;
        $request->requestTarget = $requestTarget;

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $request = clone $this;
        $request->method = $method;

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(PsrHttpUri $uri, $preserveHost = false)
    {
        $request = clone $this;
        $request->$uri = $uri;

        return $request;
    }
}
