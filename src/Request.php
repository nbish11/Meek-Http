<?php

namespace Meek\Http;

use Meek\Http\DataCollection;
use Meek\Http\CookieCollection;
use Meek\Http\Collections\ServerData as ServerDataCollection;
use Meek\Http\HeaderCollection;
use Meek\Http\Collections\FileList as FileListCollection;
use Meek\Http\Uri;
use Meek\Http\Session;

class Request
{
    private $get;
    private $post;
    private $cookies;
    public $server;
    private $headers;
    private $files;
    private $body;
    private $attributes;
    private $uri;
    public $session;

    /**
     * [__construct description]
     * @param array  $get     [description]
     * @param array  $post    [description]
     * @param array  $cookies [description]
     * @param array  $server  [description]
     * @param array  $files   [description]
     * @param string $body    [description]
     */
    public function __construct(
        array $get = [],
        array $post = [],
        array $cookies = [],
        array $server = [],
        array $files = [],
        $body = null
    ) {
        $this->get = new DataCollection($get);
        $this->post = new DataCollection($post);
        $this->cookies = new CookieCollection($cookies);
        $this->server = new ServerDataCollection($server);
        $this->headers = new HeaderCollection($this->server->getHeaders());
        $this->files = new FileListCollection($files);
        $this->body = $body;
        $this->attributes = new DataCollection();
    }

    /**
     * [create description]
     * @param  string $uri     [description]
     * @param  string $method  [description]
     * @param  array  $cookies [description]
     * @param  array  $files   [description]
     * @param  array  $server  [description]
     * @param  string $body    [description]
     * @return [type]          [description]
     */
    public static function create(
        $uri,
        $method = 'GET',
        array $cookies = [],
        array $files = [],
        array $server = [],
        $body = null
    ) {

    }

    /**
     * [createFromGlobals description]
     * @return Request [description]
     */
    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES, null);
    }

    /**
     * [getUri description]
     * @return Uri [description]
     */
    public function getUri()
    {
        // cache URI
        if (is_null($this->uri)) {
            $this->uri = Uri::createFromRequest();
        }

        return $this->uri;
    }

    /**
     * [getMethod description]
     * @return string [description]
     */
    public function getMethod()
    {
        return array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    /**
     * [getParam description]
     * @param  string $key     [description]
     * @param  mixed  $default [description]
     * @return mixed           [description]
     */
    public function getParam($key, $default = null)
    {
        // check query parameters
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];

        // check request
        } else if (array_key_exists($key, $this->post)) {
            return $this->post[$key];

        // finally, check user set data
        } else if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * [setSession description]
     * @param Session $session [description]
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * [getQueryParams description]
     * @return DataCollection [description]
     */
    public function getQueryParams()
    {
        return $this->get;
    }

    /**
     * [getRequestParams description]
     * @return DataCollection [description]
     */
    public function getRequestParams()
    {
        return $this->post;
    }

    /**
     * [getCookies description]
     * @return CookieCollection [description]
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * [getServer description]
     * @return ServerCollection [description]
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * [getHeaders description]
     * @return HeaderCollection [description]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * [getFiles description]
     * @return FileCollection [description]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * [getBody description]
     * @return [type] [description]
     */
    public function getBody()
    {
        // cache body
        if (is_null($this->body)) {
            $this->body = file_get_contents('php://input');
        }

        return $this->body;
    }

    /**
     * [__get description]
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function __get($key)
    {
        return $this->__isset($key) ? $this->attributes[$key] : null;
    }

    /**
     * [__set description]
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function __set($key, $value)
    {
        if ($key === null) {
            throw new InvalidArgumentException('A key was not provided.');
        }

        $this->attributes[$key] = $value;
    }

    /**
     * [__isset description]
     * @param  [type]  $key [description]
     * @return boolean      [description]
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * [__unset description]
     * @param [type] $key [description]
     */
    public function __unset($key)
    {
        if ($this->__isset($key)) {
            unset($this->attributes[$key]);
        }
    }
}
