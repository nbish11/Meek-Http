<?php

namespace Meek\Http;

use Psr\Http\Message\UriInterface as PsrHttpUri;

 /**
  *A class for manipulating URI's.
  *
  * @version 0.1.0
  * @author Nathan Bishop (nbish11)
  * @copyright 2016 Nathan Bishop
  * @license MIT
  */
class Uri implements PsrHttpUri
{
    protected $scheme;
    protected $userInfo;
    protected $host;
    protected $port;
    protected $path;
    protected $query;
    protected $fragment;

    protected static $allowedSchemes = [];

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return (string) $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $authority = '';

        if ($userInfo = $this->getUserInfo()) {
            $authority .= $userInfo . '@';
        }

        $authority .= $this->getHost();

        if ($port = $this->getPort()) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return (string) $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return (string) $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        // If a port is present, and it is non-standard for the current scheme,
        // this method MUST return it as an integer. If the port is the standard port
        // used with the current scheme, this method SHOULD return null.
        if (($this->port === 80 && $this->scheme === 'http') ||
            ($this->port === 443 && $this->scheme === 'https')) {
            return null;
        }

        return (integer) $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return (string) $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return (string) $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return (string) $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $instance = clone $this;
        $instance->scheme = $scheme;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null)
    {
        $instance = clone $this;

        if ($pass) {
            $user = $user . ':' . $pass;
        }

        $instance->userInfo = $user;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $instance = clone $this;
        $instance->host = $host;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $instance = clone $this;
        $instance->port = $port;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $instance = clone $this;
        $instance->path = $path;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $instance = clone $this;
        $instance->query = $query;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $instance = clone $this;
        $instance->fragment = $fragment;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return static::build([
            'scheme' => $this->getScheme(),
            'authority' => $this->getAuthority(),
            'path' => $this->getPath(),
            'query' => $this->getQuery(),
            'fragment' => $this->getFragment()
        ]);
    }

    /**
     * [createFromRequest description]
     *
     * @param array $server [description]
     * @return self [description]
     */
    public static function createFromRequest(array $server)
    {
        return static::createFromArray([
            'scheme' => $server['REQUEST_SCHEME'],
            'host' => $server['SERVER_NAME'],
            'port' => $server['SERVER_PORT'],
            'user' => isset($server['PHP_AUTH_USER']) ? $server['PHP_AUTH_USER'] : '',
            'pass' => isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '',
            'path' => strstr($server['REQUEST_URI'], '?', true) ?: $server['REQUEST_URI'],
            'query' => $server['QUERY_STRING']
        ]);
    }

    /**
     * [createFromString description]
     *
     * @param string $uri [description]
     * @return self [description]
     */
    public static function createFromString($uri)
    {
        return static::createFromArray(static::parse($uri));
    }

    /**
     * [createFromArray description]
     *
     * @param array $uri [description]
     * @return self [description]
     */
    public static function createFromArray(array $uri)
    {
        $uri += [
            'scheme' => '',
            'user' => '',
            'pass' => '',
            'host' => '',
            'port' => null,
            'query' => '',
            'fragment' => ''
        ];

        return (new static())
            ->withScheme($uri['scheme'])
            ->withUserInfo($uri['user'], $uri['pass'])
            ->withHost($uri['host'])
            ->withPort($uri['port'])
            ->withPath($uri['path'])
            ->withQuery($uri['query'])
            ->withFragment($uri['fragment']);
    }

    /**
     * [parse description]
     *
     * @param string $uri [description]
     * @return array [description]
     */
    protected static function parse($uri)
    {
        $uri = (string) $uri;

        if (!is_string($uri) || empty($uri)) {
            throw new InvalidArgumentException('Invalid uri.');
        }

        $uri = parse_url((string) $uri);

        if ($uri === false) {
            throw new InvalidArgumentException('Malformed uri.');
        }

        return $uri;
    }

    /**
     * [compile description]
     *
     * @link http://tools.ietf.org/html/rfc3986#section-5.3
     * @param array $parts [description]
     * @return string [description]
     */
    protected static function compile(array $parts)
    {
        $result = '';

        if ($parts['scheme'] !== '') {
            $result .= $parts['scheme'];
            $result .= ':';
        }

        if ($parts['authority'] !== '') {
            $result .= '//';
            $result .= $parts['authority'];
        }

        $result .= $parts['path'];

        if ($parts['query'] !== '') {
            $result .= '?';
            $result .= $parts['query'];
        }

        if ($parts['fragment'] !== '') {
            $result .= '#';
            $result .= $parts['fragment'];
        }

        return $result;
    }

    /**
     * [build description]
     *
     * @param array $parsedUri [description]
     * @return string [description]
     */
    protected static function build(array $parsedUri)
    {
        $uri = '';

        // If a scheme is present, it MUST be suffixed by ":".
        if ($parsedUri['scheme'] !== '') {
            $uri .= $parsedUri['scheme'] . ':';
        }

        // If an authority is present, it MUST be prefixed by "//".
        if ($parsedUri['authority'] !== '') {
            $uri .= '//' . $parsedUri['authority'];
        }

        /**
         * - The path can be concatenated without delimiters. But there are two
         *   cases where the path has to be adjusted to make the URI reference
         *   valid as PHP does not allow to throw an exception in __toString():
         *     - If the path is rootless and an authority is present, the path MUST
         *       be prefixed by "/".
         *     - If the path is starting with more than one "/" and no authority is
         *       present, the starting slashes MUST be reduced to one.
         */
        $uri .= '/' . ltrim($parsedUri['path'], '/');

        // If a query is present, it MUST be prefixed by "?".
        if ($parsedUri['query'] !== '') {
            $uri .= '?' . $parsedUri['query'];
        }

        // If a fragment is present, it MUST be prefixed by "#".
        if ($parsedUri['fragment'] !== '') {
            $uri .= '#' . $parsedUri['fragment'];
        }

        return $uri;
    }
}
