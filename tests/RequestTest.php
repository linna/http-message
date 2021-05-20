<?php

/**
 * Linna Http Message.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@tim.it>
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

    /**
     * Test with uri with preserve host.
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     *
     * @return void
     */
    public function testWithUriWithPreserveHostCaseOne(): void
    {
        $request = self::$request
            ->withUri(new Uri('http://www.otherhost.com/foo/bar/baz/'), true);

        $this->assertSame('http://www.otherhost.com/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.otherhost.com']], $request->getHeaders());
    }

    /**
     * Test with uri with preserve host.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     *
     * @return void
     */
    public function testWithUriWithPreserveHostCaseTwo(): void
    {
        $request = self::$request
            ->withUri(new Uri('/foo/bar/baz/'), true);

        $this->assertSame('/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame([], $request->getHeaders());
    }

    /**
     * Test with uri with preserve host.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * @return void
     */
    public function testWithUriWithPreserveHostCaseThree(): void
    {
        $request = self::$request
            ->withHeader('host', ['www.foohost.com'])
            ->withUri(new Uri('http://www.otherhost.com/foo/bar/baz/'), true);

        $this->assertSame('http://www.otherhost.com/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.foohost.com']], $request->getHeaders());
    }

    /**
     * Test with uri no preserve host.
     * - If a Host header is not present, and the new URI contain a host
     *   component this method update the Host header in the returned request.
     *
     * @return void
     */
    public function testWithUriNoPreserveHostCaseOne(): void
    {
        $request = self::$request
            ->withUri(new Uri('http://www.otherhost.com/foo/bar/baz/'));

        $this->assertSame('http://www.otherhost.com/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.otherhost.com']], $request->getHeaders());
    }

    /**
     * Test with uri no preserve host.
     * - If a Host header is present, and the new URI contain a host
     *   component this method update the Host header in the returned request.
     *
     * @return void
     */
    public function testWithUriNoPreserveHostCaseTwo(): void
    {
        $request = self::$request
            ->withHeader('host', ['www.foohost.com'])
            ->withUri(new Uri('http://www.otherhost.com/foo/bar/baz/'));

        $this->assertSame('http://www.otherhost.com/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.otherhost.com']], $request->getHeaders());
    }

    /**
     * Test with uri no preserve host.
     * - If a Host header is present, and the new URI does not contain a host
     *   component this method doesn't update the Host header in the returned
     *   request.
     *
     * @return void
     */
    public function testWithUriNoPreserveHostCaseThree(): void
    {
        $request = self::$request
            ->withHeader('host', ['www.foohost.com'])
            ->withUri(new Uri('/foo/bar/baz/'));

        $this->assertSame('/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.foohost.com']], $request->getHeaders());
    }

    /**
     * Test with uri no preserve host with non standard port.
     *
     * @return void
     */
    public function testWithUriNoPreserveHostWithNonStandardPort(): void
    {
        $request = self::$request
            ->withHeader('host', ['www.foohost.com'])
            ->withUri(new Uri('http://www.otherhost.com:8080/foo/bar/baz/'));

        $this->assertSame('http://www.otherhost.com:8080/foo/bar/baz/', (string) $request->getUri());
        $this->assertSame(['Host' => [0 => 'www.otherhost.com:8080']], $request->getHeaders());
    }
}
