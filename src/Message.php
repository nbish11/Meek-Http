<?php

namespace Meek\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface as PsrHttpStream;

/**
 * Models the common features used by a HTTP request/response message.
 *
 * @author Nathan Bishop <nbish11@hotmail.com>
 * @copyright Copyright (c) 2016, Nathan Bishop
 * @package Meek\Http
 * @license MIT
 */
trait Message
{
    /**
     * The protocol version used by the request/response.
     *
     * @var string
     */
    private $version = '1.1';

    /**
     * The headers used by the request/response, but with the
     * header names formatted to lower case.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Mapping of normalized header names to original, user
     * provided header names; where key is the normalized header
     * name and value was the original header name.
     *
     * @var array
     */
    private $originalHeaderNames = [];

    /**
     * The request/response body.
     *
     * @var PsrHttpStream
     */
    private $body;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        if (!in_array($version, ['1.0', '1.1', '2'], true)) {
            throw new InvalidArgumentException('A valid protocol version was not provided.');
        }

        $instance = clone $this;
        $instance->version = $version;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return array_key_exists(strtolower($name), $this->originalHeaderNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        $original = $this->originalHeaderNames[strtolower($name)];
        $value = $this->headers[$original];

        return is_array($value) ? $value : [$value];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value) || count(array_filter($value, 'is_string')) !== count($value)) {
            throw new InvalidArgumentException('Header value must be a string or an array of strings.');
        }

        $normalized = strtolower($name);
        $instance = clone $this;

        // remove the original header name and set to the new one
        if ($instance->hasHeader($name)) {
            unset($instance->headers[$instance->originalHeaderNames[$normalized]]);
        }

        $instance->headers[$name] = $value;
        $instance->originalHeaderNames[$normalized] = $name;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value) || count(array_filter($value, 'is_string')) !== count($value)) {
            throw new InvalidArgumentException('Header value must be a string or an array of strings.');
        }

        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $original = $this->originalHeaderNames[strtolower($name)];
        $instance = clone $this;
        $instance->headers[$original] = array_merge($this->headers[$original], $value);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }

        $name = strtolower($name);
        $instance = clone $this;
        unset($instance->headers[$this->originalHeaderNames[$name]]);
        unset($instance->originalHeaderNames[$name]);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(PsrHttpStream $body)
    {
        $instance = clone $this;
        $instance->body = $body;

        return $this;
    }

    /**
     * Allows for bulk-setting the headers without worring about
     * immutability. Should only be used during first time class
     * instantiation.
     *
     * @param array $headers An associative array of the message's
     *                       headers. Each key must be a header
     *                       name, and each value can either be a
     *                       string for the header or an array of
     *                       strings for the header.
     */
    private function setHeaders(array $headers)
    {
        $this->headers = [];
        $this->originalHeaderNames = [];

        foreach ($headers as $name => $values) {
            if (!is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $this->headers[$name][] = $value;
                $this->originalHeaderNames[strtolower($name)] = $name;
            }
        }
    }
}
