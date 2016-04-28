<?php

namespace Meek\Http;

use InvalidArgumentException;
use DateTime;
use DateTimeInterface;

/**
 * A simple class for working with HTTP "cookies".
 *
 * @see https://tools.ietf.org/html/rfc6265
 * @version 0.1.0
 * @author Nathan Bishop (nbish11)
 * @copyright 2016 Nathan Bishop
 * @license MIT
 */
class Cookie
{
    /**
     * [$name description]
     *
     * @var string
     */
    protected $name;

    /**
     * [$value description]
     *
     * @var string
     */
    protected $value;

    /**
     * [$expire description]
     *
     * @var integer
     */
    protected $expire;

    /**
     * [$path description]
     *
     * @var string
     */
    protected $path;

    /**
     * [$domain description]
     *
     * @var string
     */
    protected $domain;

    /**
     * [$secure description]
     *
     * @var boolean
     */
    protected $secure;

    /**
     * [$httpOnly description]
     *
     * @var boolean
     */
    protected $httpOnly;

    /**
     * [__construct description]
     *
     * @param string  $name   [description]
     * @param string  $value  [description]
     * @param integer $expire [description]
     * @param string  $path   [description]
     * @param string  $domain [description]
     */
    public function __construct($name, $value = '', $expire = 0, $path = '', $domain = '')
    {
        $this->setName($name);
        $this->setValue($value);
        $this->setExpire($expire);
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure(false);
        $this->setHttpOnly(true);
    }

    /**
     * [setName description]
     *
     * @param string $name [description]
     *
     * @return self
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }

        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $name)
            );
        }

        $this->name = $name;

        return $this;
    }

    /**
     * [getName description]
     *
     * @return string [description]
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * [setValue description]
     *
     * @param string $value [description]
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * [getValue description]
     *
     * @return string [description]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * [setExpire description]
     *
     * @param string|DateTimeInterface|integer $expire [description]
     *
     * @return self
     */
    public function setExpire($expire)
    {
        if (is_string($expire)) {
            $expire = new DateTime($expire);
        }

        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        }

        if ($expire === -1 || !is_integer($expire)) {
            throw new InvalidArgumentException('The cookie expiration time is not valid.');
        }

        $this->expire = $expire;

        return $this;
    }

    /**
     * [getExpire description]
     *
     * @return integer [description]
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * [setPath description]
     *
     * @param string $path [description]
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = empty($path) ? '/' : $path;

        return $this;
    }

    /**
     * [getPath description]
     *
     * @return string [description]
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * [setDomain description]
     *
     * @param string $domain [description]
     *
     * @return self
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * [getDomain description]
     *
     * @return string [description]
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * [setSecure description]
     *
     * @param boolean $secure [description]
     *
     * @return self
     */
    public function setSecure($secure)
    {
        $this->secure = (boolean) $secure;

        return $this;
    }

    /**
     * [setHttpOnly description]
     *
     * @param boolean $httpOnly [description]
     *
     * @return self
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = (boolean) $httpOnly;

        return $this;
    }

    /**
     * [isSecure description]
     *
     * @return boolean [description]
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * [isHttpOnly description]
     *
     * @return boolean [description]
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * [__toString description]
     *
     * @return string [description]
     */
    public function __toString()
    {
        $output = '';

        // rawurlencode??
        $output .= urlencode($this->getName()) . '=';

        if (empty($this->getValue())) {
            $output .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() + 1);
        } else {
            $output .= urlencode($this->getValue());

            if ($this->getExpire() !== 0) {
                $output .= '; expires=' . gmdate('D, d-M-Y H:i:s T', $this->getExpire());
            }
        }

        if ($this->getPath()) {
            $output .= '; path=' . $this->getPath();
        }

        if ($this->getDomain()) {
            $output .= '; domain=' . $this->getDomain();
        }

        if ($this->isSecure()) {
            $output .= '; secure';
        }

        if ($this->isHttpOnly()) {
            $output .= '; httponly';
        }

        return $output;
    }
}
