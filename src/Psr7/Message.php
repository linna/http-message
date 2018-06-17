<?php

/**
 * Linna Psr7.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2018, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Psr7;

use Linna\TypedArray;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP Psr7 Message implementation
 */
abstract class Message implements MessageInterface
{
    /**
    * @var string Protocol version.
    */
    protected $protocolVersion = '1.1';

    /**
    * @var array Allowed protocol versions.
    */
    protected static $allowedProtocolVersions = [
        '1.0' => true,
        '1.1' => true,
        '2.0' => true,
    ];

    /**
     * @var array Message headers .
     */
    protected $headers = [];

    /**
     * @var StreamInterface Body of the message.
     */
    protected $body;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if (!isset(self::$validProtocolVersions[$version])) {
            throw new InvalidArgumentException(__CLASS__.': Invalid HTTP protocol version. Must be 1.0, 1.1 or 2.0');
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        $this->normalize($name);

        return isset($this->headers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        $this->normalize($name);

        return isset($this->headers[$name]) ?  $this->headers[$name] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name): string
    {
        $this->normalize($name);

        return isset($this->headers[$name]) ?  implode(', ', $this->headers[$name]) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, array $value): MessageInterface
    {
        $this->normalize($name);

        $new = clone $this;

        //use typed array for assicure that array contains only strings
        $new->headers[$name] = (new TypedArray('string', $value))->getArrayCopy();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, array $value): MessageInterface
    {
        $this->normalize($name);

        //use typed array for assicure that array contains only strings
        $headerValue = (new TypedArray('string', $value))->getArrayCopy();

        $new = clone $this;

        //check if header exist
        if (!isset($this->headers[$name])) {
            $new->headers[$name] = $headerValue;

            return $new;
        }

        //at this point header exists
        //remain only to append new value to existing header
        $new->headers[$name] = array_merge($this->headers[$name], $headerValue);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $this->normalize($name);

        $new = clone $this;
        unset($new->headers[$name]);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * Normalize header name for case-unsensitive search.
     *
     * @param string $headerName
     */
    private function normalize(string &$headerName)
    {
        $headerName = ucwords(strtolower($headerName), '-');
    }
}
