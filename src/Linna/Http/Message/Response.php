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

use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

/**
 * PSR-7 Response implementation.
 */
class Response extends Message implements ResponseInterface, StatusCodeInterface
{
    /**
     * Class Constructor.
     *
     * @param int       $code               http status code
     * @param string    $reasonPhrase       http reason phrase
     * @param string    $body               response body
     * @param array     $headers            response headers
     * @param string    $protocolVersion    protocol version
     */
    public function __construct(
        protected int $code,
        protected string $reasonPhrase = '',
        string $body = '',
        array $headers = [],
        string $protocolVersion = '1.1'
    ) {
        $this->reasonPhrase = $this->validateCode($this->code);

        //create new stream
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($body);
        $stream->rewind();

        //message constructor
        parent::__construct(
            body: $stream,
            protocolVersion: $protocolVersion,
            headers: $headers
        );
    }

    /**
     * Validate status code and return reason phrase associated.
     *
     * @param int $code
     * @return string
     * @throws InvalidArgumentException
     */
    public function validateCode(int $code): string
    {
        $reasonPhrase = \array_map(
            //change text from ex: STATUS_MOVED_PERMANENTLY to Moved Permanently
            fn ($x) => \ucwords(\strtr(\substr(\strtolower($x), 7), '_', ' ')),
            //invert key with values array:
            //from ["STATUS_MOVED_PERMANENTLY" => 301]
            //to [301 => "STATUS_MOVED_PERMANENTLY"]
            \array_flip((new ReflectionClass($this))->getConstants())
        );

        //all letters capital
        $reasonPhrase[StatusCodeInterface::STATUS_OK] = 'OK';

        if (isset($reasonPhrase[$code])) {
            return $reasonPhrase[$code];
        }

        throw new InvalidArgumentException('Invalid status code.');
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $reasonPhrase = $this->validateCode($code);

        $new = clone $this;
        $new->code = $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
