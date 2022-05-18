<?php

/**
 * Linna Http Message.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2019, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Http\Message;

use InvalidArgumentException;
use Linna\Http\Message\Request;
//use Linna\Http\Message\Stream;
use Linna\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Request Test
 */
class ResponseTest extends TestCase
{
    /**
     * @var Request The response class
     */
    protected static $response;

    /**
     * Set up before class.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$response = new Response(Response::STATUS_OK);
    }

    /**
     * Tear down after class.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * Test new instance with defaults arguments.
     *
     * @return void
     */
    public function testNewInstanceWithDefaultArguments(): void
    {
        $this->assertInstanceOf(Response::class, self::$response);
    }

    /**
     * HTTP status code provider.
     *
     * @return array
     */
    public function statusCodeProvider(): array
    {
        return [
            [100, 'Continue'],
            [101, 'Switching Protocols'],
            [102, 'Processing'],
            [103, 'Early Hints'],
            [200, 'OK'],
            [201, 'Created'],
            [202, 'Accepted'],
            [203, 'Non Authoritative Information'],
            [204, 'No Content'],
            [205, 'Reset Content'],
            [206, 'Partial Content'],
            [207, 'Multi Status'],
            [208, 'Already Reported'],
            [226, 'Im Used'],
            [300, 'Multiple Choices'],
            [301, 'Moved Permanently'],
            [302, 'Found'],
            [303, 'See Other'],
            [304, 'Not Modified'],
            [305, 'Use Proxy'],
            [306, 'Reserved'],
            [307, 'Temporary Redirect'],
            [308, 'Permanent Redirect'],
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [402, 'Payment Required'],
            [403, 'Forbidden'],
            [404, 'Not Found'],
            [405, 'Method Not Allowed'],
            [406, 'Not Acceptable'],
            [407, 'Proxy Authentication Required'],
            [408, 'Request Timeout'],
            [409, 'Conflict'],
            [410, 'Gone'],
            [411, 'Length Required'],
            [412, 'Precondition Failed'],
            [413, 'Payload Too Large'],
            [414, 'Uri Too Long'],
            [415, 'Unsupported Media Type'],
            [416, 'Range Not Satisfiable'],
            [417, 'Expectation Failed'],
            [418, 'Im A Teapot'],
            [421, 'Misdirected Request'],
            [422, 'Unprocessable Entity'],
            [423, 'Locked'],
            [424, 'Failed Dependency'],
            [425, 'Too Early'],
            [426, 'Upgrade Required'],
            [428, 'Precondition Required'],
            [429, 'Too Many Requests'],
            [431, 'Request Header Fields Too Large'],
            [451, 'Unavailable For Legal Reasons'],
            [500, 'Internal Server Error'],
            [501, 'Not Implemented'],
            [502, 'Bad Gateway'],
            [503, 'Service Unavailable'],
            [504, 'Gateway Timeout'],
            [505, 'Version Not Supported'],
            [506, 'Variant Also Negotiates'],
            [507, 'Insufficient Storage'],
            [508, 'Loop Detected'],
            [510, 'Not Extended'],
            [511, 'Network Authentication Required']
        ];
    }

    /**
     * Test new instance with all http methods.
     *
     * @dataProvider statusCodeProvider
     *
     * @return void
     */
    public function testNewInstanceWithAllStatusCodes(int $code, string $reasonPhrase): void
    {
        $response = new Response($code);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals($reasonPhrase, $response->getReasonPhrase());
    }

    /**
     * Test new instance with wrong http method.
     *
     * @return void
     */
    public function testNewInstanceWithWrongStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid status code.");

        (new Response(600));
    }

    /**
     * Test with status code.
     *
     * @return void
     */
    public function testWithStatus(): void
    {
        $response = self::$response->withStatus(Response::STATUS_FORBIDDEN);

        $this->assertNotEquals($response->getStatusCode(), self::$response->getStatusCode());
        $this->assertNotEquals($response->getReasonPhrase(), self::$response->getReasonPhrase());
        $this->assertEquals(Response::STATUS_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('Forbidden', $response->getReasonPhrase());
    }

    /**
     * Test with status, with wrong status code.
     *
     * @return void
     */
    public function testWithStatusWithWrongStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid status code.");

        self::$response->withStatus(600);
    }
}
