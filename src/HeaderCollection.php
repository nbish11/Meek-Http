<?php

namespace Meek\Http;

use Meek\Http\DataCollection;

/**
 * Inspired by how Klein.php handles headers.
 * @link https://github.com/klein/klein.php/blob/master/src/Klein/DataCollection/HeaderDataCollection.php
 */
class HeaderCollection extends DataCollection
{
    const NORMALIZE_NONE = 0;
    const NORMALIZE_TRIM = 1;
    const NORMALIZE_DELIMITERS = 2;
    const NORMALIZE_CASE = 4;
    const NORMALIZE_CANONICAL = 8;
    const NORMALIZE_ALL = -1;

    private $normalization;

    public function __construct(array $headers = [], $normalization = self::NORMALIZE_ALL)
    {
        $this->setNormalization($normalization);
        $this->replace($headers);
    }

    public function get($key, $default = null)
    {
        $key = $this->normalize($key);

        return parent::get($key, $default);
    }

    public function set($key, $value)
    {
        $key = $this->normalize($key);

        return parent::set($key, $value);
    }

    public function exists($key)
    {
        $key = $this->normalize($key);

        return parent::exists($key);
    }

    public function remove($key)
    {
        $key = $this->normalize($key);

        return parent::remove($key);
    }

    public function replace(array $headers)
    {
        $this->clear();

        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function getNormalization()
    {
        return $this->normalization;
    }

    public function setNormalization($normalization)
    {
        $this->normalization = (integer) $normalization;

        return $this;
    }

    public function send()
    {
        if (!headers_sent()) {
            foreach ($this->all() as $key => $value) {
                header(sprintf('%s: %s', $key, $value));
            }
        }

        return $this;
    }

    public function __toString()
    {
        $output = '';

        foreach ($this->all() as $key => $value) {
            $output .= sprintf("%s: %s\r\n", $key, $value);
        }

        return $output;
    }

    public function __invoke()
    {
        return $this->send();
    }

    protected function normalize($key)
    {
        if ($this->normalization & static::NORMALIZE_TRIM) {
            $key = trim($key);
        }

        if ($this->normalization & static::NORMALIZE_DELIMITERS) {
            $key = str_replace([' ', '_'], '-', $key);
        }

        if ($this->normalization & static::NORMALIZE_CASE) {
            $key = strtolower($key);
        }

        if ($this->normalization & static::NORMALIZE_CANONICAL) {
            $key = static::canonicalize($key);
        }

        return $key;
    }

    protected static function canonicalize($key)
    {
        return implode('-', array_map('ucfirst', explode('-', $key)));
    }
}
