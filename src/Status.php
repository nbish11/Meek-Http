<?php

namespace Meek\Http;

use InvalidArgumentException;

class Status
{
    private $code;
    private $message;

    protected static $messages = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    /**
     * Constructor.
     * @param integer $code    A valid HTTP status code.
     * @param string  $message An optional message for the status code.
     */
    public function __construct($code, $message = null)
    {
        $this->setCode($code);

        if ($message === null) {
            $message = static::getMessageFromCode($this->getCode());
        }

        $this->setMessage($message);
    }

    /**
     * Gets the HTTP status code.
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the HTTP status code.
     * @param integer $code A valid HTTP status code.
     */
    public function setCode($code)
    {
        if (!preg_match('/^[1-5][0-9][0-9]$/', $code)) {
            throw new InvalidArgumentException('Please provide a valid HTTP status code.');
        }

        $this->code = (integer) $code;

        return $this;
    }

    /**
     * Gets the HTTP status message.
     * @return string
     */
    public function getMessage()
    {
        return (string) $this->message;
    }

    /**
     * Sets the HTTP status message.
     * @param string $message A message to describe the HTTP status code.
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;

        return $this;
    }

    /**
     * Checks if the status is informational.
     * @return boolean
     */
    public function isInformational()
    {
        return $this->code >= 100 && $this->code < 200;
    }

    /**
     * Checks if the status is a success.
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->code >= 200 && $this->code < 300;
    }

    /**
     * Checks if the status is a redirect.
     * @return boolean
     */
    public function isRedirection()
    {
        return $this->code >= 300 && $this->code < 400;
    }

    /**
     * Checks if the status is a client error.
     * @return boolean
     */
    public function isClientError()
    {
        return $this->code >= 400 && $this->code < 500;
    }

    /**
     * Checks if the status is a server error.
     * @return boolean
     */
    public function isServerError()
    {
        return $this->code >= 500 && $this->code < 600;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString()
    {
        return $this->getFormattedStatusLine();
    }

    /**
     * [getFormattedStatusLine description]
     * @return [type] [description]
     */
    protected function getFormattedStatusLine()
    {
        $statusLine = (string) $this->code;

        if ($this->message !== null) {
            $statusLine = $statusLine . ' ' . $this->message;
        }

        return $statusLine;
    }

    /**
     * [getMessageFromCode description]
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public static function getMessageFromCode($code)
    {
        return array_key_exists($code, static::$messages) ? static::$messages[$code] : null;
    }
}
