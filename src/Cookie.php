<?php

namespace Meek\Http;

use InvalidArgumentException;
use DateTime;
use DateTimeInterface;

class Cookie
{
    protected $name;
    protected $value;
    protected $expire;
    protected $path;
    protected $domain;
    protected $secure;
    protected $httpOnly;
    
    public function __construct(
        $name,
        $value = '',
        $expire = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $httpOnly = true
    ) {
        $this->setName($name);
        $this->setValue($value);
        $this->setExpire($expire);
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($httpOnly);
    }

    public function setName($name)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $name)
            );
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }

        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setExpire($expire)
    {
        if ($expire instanceof DateTime || $expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        } else if (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if ($expire === false || $expire === -1) {
                throw new InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        $this->expire = $expire;

        return $this;
    }

    public function getExpire()
    {
        return $this->expire;
    }

    public function setPath($path)
    {
        $this->path = empty($path) ? '/' : $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setSecure($secure)
    {
        $this->secure = (boolean) $secure;

        return $this;
    }

    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = (boolean) $httpOnly;

        return $this;
    }

    public function isSecure()
    {
        return $this->secure;
    }

    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    public function __toString()
    {
        $output = '';

        // rawurlencode??
        $output .= urlencode($this->getName()) . '=';

        if (empty($this->getValue())) {
            $output .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001);
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
