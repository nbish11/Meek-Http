<?php

namespace Meek\Http;

use Meek\Http\HeaderCollection;
use Meek\Http\Status;
use Meek\Http\Request;
use InvalidArgumentException;

class Response
{
    protected $status;
    private $sent = false;
    private $body;
    public $headers;
    protected $cookies;
    private $protocol = '1.1';
    private $charset = 'utf-8';
    public $session;

    public function __construct($body = '', $status = 200, array $headers = [])
    {
        $this->headers = new HeaderCollection($headers);
        $this->setBody($body);
        $this->setStatus($status);
    }

    public function setBody($body, $contentType = 'text/html')
    {
        if ($body === null) {
            throw new InvalidArgumentException('Please provide a body!');
        }

        $this->headers['Content-Type'] = "$contentType; charset=$this->charset";
        $this->body = (string) $body;

        return $this;
    }

    public function setStatus($code, $message = null)
    {
        if (!($code instanceof Status)) {
            $code = new Status($code, $message);
        } else if ($message !== null) {
            $code->setMessage($message);
        }

        $this->status = $code;

        return $this;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function prepare(Request $request)
    {
        return $this;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else if ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }

        return $this;
    }

    public function __toString()
    {
        $output = '';

        $output .= $this->getHttpStatusLine() . "\r\n";
        $output .= (string) $this->headers;
        $output .= "\r\n";
        $output .= $this->body;

        return $output;
    }

    public function __invoke()
    {
        $this->send();
    }

    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    // borrowed from Symfony's Http component.
    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);
        // PHP_OUTPUT_HANDLER_* are not defined on HHVM 3.3
        $flags = defined('PHP_OUTPUT_HANDLER_REMOVABLE') ? PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE) : -1;
        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || $flags === ($s['flags'] & $flags) : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    protected function getHttpStatusLine()
    {
        return sprintf('HTTP/%s %s', $this->protocol, $this->status);
    }

    protected function sendHeaders()
    {
        header($this->getHttpStatusLine());

        $this->headers->send();

        $this->sendCookies();

        return $this;
    }

    protected function sendCookies()
    {
        //$this->cookies->send();
    }

    protected function sendBody()
    {
        echo $this->body;
    }
}
