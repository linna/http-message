<?php

/**
 * Linna Http Message.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@tim.it>
 * @copyright (c) 2019, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Http\Message;

use InvalidArgumentException;
use Linna\Http\Message\Helper\HttpMethod;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 Request implementation.
 */
class Request extends Message implements RequestInterface
{
    /**
     * @var string Request HTTP method (GET, POST, PUT.....).
     */
    protected string $method = '';

    /**
     * @var string Request target.
     */
    protected string $target = '';

    /**
     * @var UriInterface Request uri.
     */
    protected UriInterface $uri;

    /**
     * Class Constructor.
     *
     * @param string       $method
     * @param UriInterface $uri
     * @param array        $headers
     * @param string       $body
     * @param string       $version
     */
    public function __construct(
            string       $method,
            UriInterface $uri,
            array        $headers = [],
            string       $body    = 'php://memory',
            string       $version = '1.1'
        )
    {
        //from message abstract class
        //protected $body;
        //protected $headers = [];        
        //protected $protocolVersion = '1.1';
        
        //message body
        $this->body = new Stream($body, 'wb+');
        //message headers
        $this->headers = array_merge(['host' => $uri->getHost()], $headers);
        //message protocol version
        $this->protocolVersion = $version;


        //request method
        $this->method = $this->validateHttpMethod(\strtoupper($method));
        //request uri
        $this->uri = $uri;
        //request target
        $this->target = $this->getRequestTarget();
    }

    /**
     * Validate HTTP Method
     *
     * @param string $method HTTP Method
     *
     * @return string
     *
     * @throws InvalidArgumentException if passed method is not an HTTP valid method.
     */
    protected function validateHttpMethod(string $method): string
    {
        if (\in_array($method, [
                HttpMethod::METHOD_GET,
                HttpMethod::METHOD_HEAD,
                HttpMethod::METHOD_POST,
                HttpMethod::METHOD_PUT,
                HttpMethod::METHOD_PATCH,
                HttpMethod::METHOD_DELETE,
                HttpMethod::METHOD_PURGE,
                HttpMethod::METHOD_OPTIONS,
                HttpMethod::METHOD_TRACE,
                HttpMethod::METHOD_CONNECT
            ])) {
            return $method;
        }

        throw new InvalidArgumentException('Invalid HTTP method.');
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        if (!empty($this->target)) {
            return $this->target;
        }

        $target = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if (!empty($query)) {
            $target .= "?{$query}";
        }

        if (empty($target)) {
            return '/';
        }

        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target â€” e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form â€”
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *       request-target forms allowed in request messages)
     *
     * @param mixed $requestTarget
     *
     * @return static
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->target = $requestTarget;

        return $new;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     *
     * @throws InvalidArgumentException for invalid HTTP methods.
     *
     * @return static
     */
    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;
        $new->method = $this->validateHttpMethod(\strtoupper($method));

        return $new;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @return UriInterface Returns a UriInterface instance
     *                      representing the URI of the request.
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri          New request URI to use.
     * @param bool         $preserveHost Preserve the original state of the Host header.
     *
     * @return static
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if ($preserveHost && $this->hasHeader('Host')) {
            return $new;
        }

        if (empty($uri->getHost())) {
            return $new;
        }

        $host = $uri->getHost();
        $port = $uri->getPort();

        //exclude standard ports from host
        if (!\in_array($port, [80, 443])) {
            $host .= ':' . $uri->getPort();
        }

        $new = $new->withoutHeader('host');
        $new->headers['Host'] = [$host];

        return $new;
    }
}
