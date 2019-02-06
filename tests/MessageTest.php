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
use Linna\Http\Message\Message;
use Linna\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

/**
 * Message Test
 */
class MessageTest extends TestCase
{
    /**
     * @var MessageInterface The message class
     */
    protected static $message;

    /**
     * Set up before class.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$message = new class() extends Message {
        };
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
     * Test get protocol.
     *
     * @return void
     */
    public function testGetProtocol(): void
    {
        $this->assertSame('1.1', self::$message->getProtocolVersion());
    }

    /**
     * Test with protocol version.
     *
     * @return void
     */
    public function testWithProtocolVersion(): void
    {
        $message = self::$message->withProtocolVersion('1.0');

        $this->assertSame('1.0', $message->getProtocolVersion());
    }

    /**
     * Test with protocol version with invalid protocol.
     *
     * @return void
     */
    public function testWithProtocolVersionWithInvalidProtocol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid HTTP protocol version. Must be 1.0, 1.1 or 2");

        $message = self::$message->withProtocolVersion('1');
    }

    /**
     * Test get headers.
     *
     * @return void
     */
    public function testGetHeaders(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withHeader('X-Bar', ['Bar'])
            ->withAddedHeader('x-foo', ['Baz']);

        $this->assertNotSame(self::$message, $message);
        $this->assertSame(['X-Foo' => ['Foo', 'Baz'], 'X-Bar' => ['Bar']], $message->getHeaders());
    }

    /**
     * Test has header.
     *
     * @return void
     */
    public function testHasHeader(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withHeader('X-Bar', ['Bar'])
            ->withAddedHeader('x-foo', ['Baz']);

        $this->assertNotSame(self::$message, $message);

        $this->assertTrue($message->hasHeader('X-Foo'));
        $this->assertFalse($message->hasHeader('X-Baz'));

        $this->assertTrue($message->hasHeader('x-foo'));
        $this->assertFalse($message->hasHeader('x-baz'));
    }

    /**
     * Test get header.
     *
     * @return void
     */
    public function testGetHeader(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withHeader('X-Bar', ['Bar'])
            ->withAddedHeader('x-foo', ['Baz']);

        $this->assertNotSame(self::$message, $message);

        $this->assertSame(['Foo', 'Baz'], $message->getHeader('X-Foo'));
        $this->assertSame(['Bar'], $message->getHeader('X-Bar'));
        $this->assertSame([], $message->getHeader('X-Baz'));

        $this->assertSame(['Foo', 'Baz'], $message->getHeader('x-foo'));
        $this->assertSame(['Bar'], $message->getHeader('x-bar'));
        $this->assertSame([], $message->getHeader('x-baz'));
    }

    /**
     * Test get header line.
     *
     * @return void
     */
    public function testGetHeaderLine(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withHeader('X-Bar', ['Bar'])
            ->withAddedHeader('x-foo', ['Baz']);

        $this->assertNotSame(self::$message, $message);

        $this->assertSame('Foo, Baz', $message->getHeaderLine('X-Foo'));
        $this->assertSame('Bar', $message->getHeaderLine('X-Bar'));
        $this->assertSame('', $message->getHeaderLine('X-Baz'));

        $this->assertSame('Foo, Baz', $message->getHeaderLine('x-foo'));
        $this->assertSame('Bar', $message->getHeaderLine('x-bar'));
        $this->assertSame('', $message->getHeaderLine('x-baz'));
    }

    /**
     * Test with header.
     *
     * @return void
     */
    public function testWithHeader(): void
    {
        $message = self::$message->withHeader('X-Foo', ['Foo']);

        $this->assertNotSame(self::$message, $message);
        $this->assertSame([], self::$message->getHeaders());
        $this->assertSame(['X-Foo' => ['Foo']], $message->getHeaders());
    }

    /**
     * Test with added header.
     *
     * @return void
     */
    public function testWithAddedHeader(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withAddedHeader('x-foo', ['Bar'])
            ->withAddedHeader('X-Foo', ['Baz']);

        $this->assertSame([], self::$message->getHeaders());
        $this->assertSame(['X-Foo' => ['Foo', 'Bar', 'Baz']], $message->getHeaders());
    }

    /**
     * Test without header.
     *
     * @return void
     */
    public function testWithoutHeader(): void
    {
        $message = self::$message
            ->withHeader('X-Foo', ['Foo'])
            ->withHeader('X-Bar', ['Bar'])
            ->withAddedHeader('x-foo', ['Baz']);

        $otherMessage = $message->withoutHeader('x-bar');

        $this->assertNotSame(self::$message, $message);
        $this->assertNotSame($message, $otherMessage);

        $this->assertSame(['X-Foo' => ['Foo', 'Baz'], 'X-Bar' => ['Bar']], $message->getHeaders());
        $this->assertSame(['X-Foo' => ['Foo', 'Baz']], $otherMessage->getHeaders());

        $otherMessage = $message->withoutHeader('X-Bar');

        $this->assertNotSame(self::$message, $message);
        $this->assertNotSame($message, $otherMessage);

        $this->assertSame(['X-Foo' => ['Foo', 'Baz'], 'X-Bar' => ['Bar']], $message->getHeaders());
        $this->assertSame(['X-Foo' => ['Foo', 'Baz']], $otherMessage->getHeaders());
    }

    /**
     * Test with body.
     *
     * @return void
     */
    public function testWithBody(): void
    {
        $stream = new Stream(fopen('php://memory', 'rwb+'));
        $stream->write('parameter1=value1&parameter2=value2');
        $stream->rewind();

        $message = self::$message->withBody($stream);

        $this->assertNotSame(self::$message, $message);
        $this->assertSame($stream, $message->getBody());
    }
}
