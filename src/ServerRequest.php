<?php

namespace Meek\Http;

use Meek\Http\Request;
use Psr\Http\Message\ServerRequestInterface as PsrHttpServerRequest;
use Meek\Http\Uri;

class ServerRequest extends Request implements PsrHttpServerRequest
{
    /**
     * [$server description]
     *
     * @var [type]
     */
    protected $server;

    /**
     * [$query description]
     *
     * @var [type]
     */
    protected $query;

    /**
     * [$post description]
     *
     * @var [type]
     */
    protected $post;

    /**
     * [$cookies description]
     *
     * @var [type]
     */
    protected $cookies;

    /**
     * [$files description]
     *
     * @var [type]
     */
    protected $files;

    /**
     * [$attributes description]
     *
     * @var [type]
     */
    protected $attributes;

    /**
     * [__construct description]
     *
     * @param string|uri           $uri      [description]
     * @param string               $method   [description]
     * @param array                $headers  [description]
     * @param string|PsrHttpStream $body     [description]
     * @param string               $protocol [description]
     * @param array                $server   [description]
     */
    public function __construct($uri, $method = 'GET', array $headers = [], $body = '', $protocol = '1.1', array $server = [])
    {
        $this->server = $server;
        $this->query = [];
        $this->post = [];
        $this->cookies = [];
        $this->files = [];
        $this->attributes = [];

        parent::__construct($uri, $method, $headers, $body, $protocol);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $this->server;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $request = clone $this;

        array_merge($request->cookies, $cookies);

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $request = clone $this;

        array_merge($request->query, $query);

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $files)
    {
        $request = clone $this;

        array_merge($request->files, $files);

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->post;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $request = clone $this;

        $request->data = $data;

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return self::arrayGet($this->attributes, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $request = clone $this;
        $request->attributes[$name] = $value;

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $request = clone $this;

        unset($request->attributes[$name]);

        return $request;
    }

    /**
     * [createFromGlobals description]
     *
     * @return self
     */
    public static function createFromGlobals()
    {
        $method = self::arrayGet($_SERVER, 'REQUEST_METHOD', 'GET');
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = Uri::createFromRequest();
        $body = new Stream('php://input', 'r+');
        $protocol = str_replace('HTTP/', '', self::arrayGet($_SERVER, 'SERVER_PROTOCOL', '1.1'));
        $request = new static($uri, $method, $headers, $body, $protocol, $_SERVER);

        return $request->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody(!empty($_POST) ? $_POST : null)
            ->withUploadedFiles($_FILES);
    }

    private static function arrayGet(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}
