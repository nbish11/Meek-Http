<?php

namespace Meek\Http;

use Psr\Http\Message\ResponseInterface as PsrHttpResponse;
use Psr\Http\Message\StreamInterface as PsrHttpStream;
use Meek\Http\Message;
use Meek\Http\Stream;
use Meek\Http\Status;

use Meek\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequest;

class Response implements PsrHttpResponse
{
    use Message;

    /**
     * [$status description]
     *
     * @var [type]
     */
    private $status;

    /**
     * [$charset description]
     *
     * @var [type]
     */
    private $charset;

    /**
     * [__construct description]
     *
     * @param string  $body    [description]
     * @param integer $status  [description]
     * @param array   $headers [description]
     */
    public function __construct(PsrHttpStream $body = null, $status = 200, array $headers = [])
    {
        $this->body = $body ?: new Stream('php://temp', 'wb+');
        $this->setStatus($status);
        $this->setHeaders($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->status->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $instance = clone $this;
        $instance->setStatus($code, $reasonPhrase);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->status->getMessage();
    }

    /**
     * https://github.com/symfony/http-foundation/blob/master/Response.php#L250
     * @param  PsrServerRequest $request [description]
     * @return self
     */
    public function prepare(PsrServerRequest $request)
    {
        $response = clone $this;

        if ($response->status->isInformational() || in_array($response->getStatusCode(), [204, 304])) {
            $response = $respone->withBody(new Stream('php://temp'))
                ->withoutHeader('Content-Type')
                ->withoutHeader('Content-Length');
        } else {
            // set content type based on request, if we haven't provided one
            if (!$response->hasHeader('Content-Type')) {

            }

            // fix content type
            $charset = $response->charset ?: 'UTF-8';
            if (!$response->hasHeader('Content-Type')) {
                $response = $response->withHeader('Content-Type', sprintf('text/html; charset=%s', $charset));
            } elseif (stripos($response->getHeaderLine('Content-Type'), 'text/') === 0 &&
                stripos($response->getHeaderLine('Content-Type'), 'charset') === false
            ) {
                $value = sprintf('%s; charset=%s', $response->getHeaderLine('Content-Type'), $charset);
                $response = $response->withHeader('Content-Type', $value);
            }

            // fix content length
            if ($response->hasHeader('Transfer-Encoding')) {
                $response = $response->withoutHeader('Content-Length');
            }

            if (!$response->hasHeader('Content-Length')) {
                $response->withHeader('Content-Length', (string) $this->getBody()->getSize());
            }

            // fix HEAD requests
            if ($request->getMethod() === 'HEAD') {
                // make sure content length is specified
                if (!$response->hasHeader('Content-Length')) {
                    $response = $response->withHeader('Content-Length', $response->getBody()->getSize());
                }

                // body should be empty
                $response = $respone->withBody(new Stream('php://temp'));
            }
        }

        // fix protocol
        if ($request->getProtocolVersion() !== '1.0') {
            $response = $response->withProtocolVersion('1.1');
        }

        // check if we need to send extra expire info headers
        if ($response->getProtocolVersion() === '1.0' &&
            in_array('no-cache', $response->getHeader('Cache-Control'))
        ) {
            $response = $response->withAddedHeader('Pragma', 'no-cache')
                ->withAddedHeader('Expires', -1);
        }

        return $response;
    }

    /**
     * [send description]
     */
    public function send()
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers have already been sent.');
        }

        $this->sendStatusLine();
        $this->sendHeaders();
        static::flush();
        $this->sendBody();
    }

    /**
     * [__toString description]
     *
     * @return string [description]
     */
    public function __toString()
    {
        $output = '';

        $output .= $this->getHttpStatusLine() . "\r\n";

        foreach (array_keys($this->getHeaders()) as $header) {
            $output .= sprintf("%s: %s\r\n", static::normalize($header), $this->getHeaderLine($header));
        }

        $output .= "\r\n";
        $output .= $this->body;

        return $output;
    }

    /**
     * @see self::send
     */
    public function __invoke()
    {
        $this->send();
    }

    /**
     * [__clone description]
     */
    public function __clone()
    {
        $this->status = clone $this->status;
        $this->body = clone $this->body;
    }

    protected function getHttpStatusLine()
    {
        return sprintf('HTTP/%s %s', $this->getProtocolVersion(), $this->status);
    }

    /**
     * [sendStatusLine description]
     */
    protected function sendStatusLine()
    {
        header($this->getHttpStatusLine());
    }

    /**
     * [sendHeaders description]
     */
    protected function sendHeaders()
    {
        if (!$this->hasHeader('Content-Length')) {
            $this->setHeaders([
                'Content-Length' => $this->getBody()->getSize()
            ]);
        }

        foreach (array_keys($this->getHeaders()) as $header) {
            header(sprintf('%s: %s', static::normalize($header), $this->getHeaderLine($header)));
        }
    }

    /**
     * [sendBody description]
     */
    protected function sendBody()
    {
        echo $this->getBody();
    }

    /**
     * [setStatus description]
     *
     * @param [type] $code    [description]
     * @param [type] $message [description]
     */
    private function setStatus($code, $message = null)
    {
        if (!($code instanceof Status)) {
            $code = new Status($code, $message);
        }

        if ($message !== null) {
            $code->setMessage($message);
        }

        $this->status = $code;
    }

    /**
     * [flush description]
     *
     * @param [type] $level [description]
     */
    private static function flush($level = null)
    {
        if ($level === null) {
            $level = ob_get_level();
        }

        while (ob_get_level() > $level) {
            ob_end_flush();
        }
    }

    /**
     * [normalize description]
     *
     * @param  [type] $header [description]
     * @return [type]         [description]
     */
    private static function normalize($header)
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $header))));
    }
}
