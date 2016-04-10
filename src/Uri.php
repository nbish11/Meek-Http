<?php

namespace Meek\Http;

/**
 * A class for manipulating URI's.
 *
 * @author Nathan Bishop <nbish11@hotmail.com>
 * @copyright Copyright (c) 2016, Nathan Bishop
 * @package Meek\Http
 * @version 0.8.7
 * @license MIT
 */
class Uri
{
    private $scheme;
    private $userInfo;
    private $host;
    private $port;
    private $path;
    private $query;
    private $fragment;

    public function __construct($uri = null)
    {
        if (!is_array($uri)) {
            $uri = static::parse((string) $uri);
        }

        $parsedUri = array_fill_keys([
            'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'
        ], '');

        $uri = array_merge($parsedUri, $uri);

        $this->setScheme($uri['scheme']);
        $this->setUserInfo($uri['user'], $uri['pass']);
        $this->setHost($uri['host']);
        $this->setPort($uri['port'] === '' ? null : (integer) $uri['port']);
        $this->setPath($uri['path']);
        $this->setQuery($uri['query']);
        $this->setFragment($uri['fragment']);
    }

    public function getScheme()
    {
        return (string) $this->scheme;
    }

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

    public function getUserInfo()
    {
        return (string) $this->userInfo;
    }

    public function getHost()
    {
        return (string) $this->host;
    }

    public function getPort()
    {
        // If a port is present, and it is non-standard for the current scheme,
        // this method MUST return it as an integer. If the port is the standard port
        // used with the current scheme, this method SHOULD return null.
        if (($this->port === 80 && $this->scheme === 'http') ||
            ($this->port === 443 && $this->scheme === 'https')) {
            return null;
        }

        return $this->port;
    }

    public function getPath()
    {
        return (string) $this->path;
    }

    public function getQuery()
    {
        return (string) $this->query;
    }

    public function getFragment()
    {
        return (string) $this->fragment;
    }

    public function setScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function setUserInfo($user, $pass = null)
    {
        if ($pass) {
            $user = $user . ':' . $pass;
        }

        $this->userInfo = $user;

        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    public function setFragment($fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    public static function createFromRequest()
    {
        return new static([
            'scheme' => $_SERVER['REQUEST_SCHEME'],
            'host' => $_SERVER['SERVER_NAME'],
            'port' => $_SERVER['SERVER_PORT'],
            'user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
            'pass' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
            'path' => strstr($_SERVER['REQUEST_URI'], '?', true) ?: $_SERVER['REQUEST_URI'],
            'query' => $_SERVER['QUERY_STRING']
        ]);
    }

    protected static function parse($uri)
    {
        return parse_url($uri);
    }

    // http://tools.ietf.org/html/rfc3986#section-5.3
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
}
