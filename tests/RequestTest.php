<?php

/**
 * Linna Http Message.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2019, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Tests;

use InvalidArgumentException;
use Linna\Http\Message\Request;
use Linna\Http\Message\Stream;
use Linna\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Request Test
 */
class RequestTest extends TestCase
{
    /**
     * @var Request The request class
     */
    protected static $request;

    /**
     * Set up before class.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$request = new Request(new Uri('http://127.0.0.1/'));
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
        $this->assertInstanceOf(Request::class, self::$request);
    }

    /**
     * HTTP methods provider.
     *
     * @return array
     */
    public function httpMethodsProvider(): array
    {
        return [
            ['GET'],
            ['HEAD'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['CONNECT'],
            ['OPTIONS'],
            ['TRACE']
        ];
    }

    /**
     * Test new instance with all http methods.
     *
     * @dataProvider httpMethodsProvider
     *
     * @return void
     */
    public function testNewInstanceWithAllHttpMethods(string $method): void
    {
        $this->assertInstanceOf(Request::class, new Request(new Uri('http://127.0.0.1/'), $method));
    }

    /**
     * Test new instance with wrong http method.
     *
     * @return void
     */
    public function testNewInstanceWithWrongHttpMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid HTTP method.");

        $this->assertInstanceOf(Request::class, new Request(new Uri('http://127.0.0.1/'), 'FOO'));
    }

    /**
     * Uri provider.
     *
     * @return array
     */
    public function uriProvider(): array
    {
        return [
            ['http://127.0.0.1/', '/'],
            ['http://127.0.0.1/foo', '/foo'],
            ['http://127.0.0.1/foo/', '/foo/'],
            ['http://127.0.0.1/foo/bar', '/foo/bar'],
            ['http://127.0.0.1/foo/bar/', '/foo/bar/'],
            ['http://127.0.0.1/foo/foo.php?bar=baz', '/foo/foo.php?bar=baz'],
        ];
    }

    /**
     * Test get request target.
     *
     * @dataProvider uriProvider
     *
     * @return void
     */
    public function testGetRequestTarget(string $uri, string $result): void
    {
        $request = new Request(new Uri($uri));

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame($result, $request->getRequestTarget());
    }

    /**
     * Test with request target.
     *
     * @dataProvider uriProvider
     *
     * @return void
     */
    public function testWithRequestTarget(): void
    {
        $request = self::$request->withRequestTarget('/foo/bar/baz/');

        $this->assertSame('/foo/bar/baz/', $request->getRequestTarget());
    }
}
