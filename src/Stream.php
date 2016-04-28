<?php

namespace Meek\Http;

use Psr\Http\Message\StreamInterface as PsrHttpStream;
use InvalidArgumentException;
use RuntimeException;

class Stream implements PsrHttpStream
{
    /**
     * [__construct description]
     *
     * @param string|resource $resource [description]
     * @param string          $mode     [description]
     */
    public function __construct($resource, $mode = 'r')
    {
        $this->open($resource, $mode);
    }

    /**
     * [open description]
     *
     * @param  string|resource $resource [description]
     * @param  string          $mode     [description]
     */
    public function open($resource, $mode = 'r')
    {
        if (is_string($resource)) {
            $resource = @fopen($resource, $mode);
        }

        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new InvalidArgumentException('A valid resource URI or a stream resource was not provided.');
        }

        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->resource)) {
            $reource = $this->detach();
            fclose($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        return fstat($this->resource)['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $this->requireResource();

        $result = ftell($this->resource);

        if (!is_integer($result)) {
            throw new RuntimeException('An unknown error has occured while trying to tell.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        return stream_get_meta_data($this->resource)['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->requireResource();

        if (!$this->isSeekable()) {
            throw new RuntimeException('The resource is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw new RuntimeException('An unknown error has occured while trying to seek within the stream.');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $mode = stream_get_meta_data($this->resource)['mode'];

        // maybe preg_match??
        // return preg_match('/[xwca\+]{1}/', $mode);
        return strstr($mode, 'x') || strstr($mode, 'w')
            || strstr($mode, 'c') || strstr($mode, 'a') || strstr($mode, '+');
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $this->requireResource();

        if (!$this->isWritable()) {
            throw new RuntimeException('The stream is not writeable.');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new RuntimeException('An unknown error has occured while trying to write to the stream.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $mode = stream_get_meta_data($this->resource)['mode'];

        return strstr($mode, 'r') || strstr($mode, '+');
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $this->requireResource();
        $this->requireReadable();

        $result = fread($this->resource, $length);

        if ($result === false) {
            throw new RuntimeException('An unknown error has occured while trying to read from the stream.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $this->requireReadable();

        $result = stream_get_contents($this->resource);

        if ($result === false) {
            throw new RuntimeException('An unknown error has occured while attempting to get the stream contents.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $metadata;
        }

        return array_key_exists($key, $metadata) ? $metadata[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * [requireResource description]
     */
    private function requireResource()
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('The resource has either been detached or closed.');
        }
    }

    /**
     * [requireReadable description]
     */
    private function requireReadable()
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('The stream is not readable.');
        }
    }
}
