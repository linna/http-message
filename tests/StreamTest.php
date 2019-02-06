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
use Linna\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Stream Test.
 */
class StreamTest extends TestCase
{
    /**
     * Tear down after class.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        foreach (glob(sys_get_temp_dir().'/stream_test*') as $file) {
            unlink($file);
        }
    }

    /**
     * Test new instance with stream identifier.
     *
     * @return void
     */
    public function testNewInstanceWithStreamIdentifier(): void
    {
        $this->assertInstanceOf(Stream::class, new Stream('php://memory', 'wb+'));
    }

    /**
     * Test new instance with stream resource.
     *
     * @return void
     */
    public function testNewInstanceWithStreamResource(): void
    {
        $this->assertInstanceOf(Stream::class, new Stream(fopen('php://memory', 'wb+')));
    }

    /**
     * Test new instance with invalid stream identifier.
     *
     * @return void
     */
    public function testNewInstanceWithInvalidStreamIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream identifier provided.");

        (new Stream('php://memor', 'wb+'));
    }

    /**
     * Test new instance with invalid stream resource.
     *
     * @return void
     */
    public function testNewInstanceWithInvalidStreamResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid resource provided.");

        (new Stream([], 'wb+'));
    }

    /**
     * Test new instance with not stream resource.
     *
     * @return void
     */
    public function testNewInstanceWithNotSreamResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Resource provided is not a stream.");

        (new Stream(socket_create(AF_UNIX, SOCK_STREAM, 0)));
    }

    /**
     * Test close.
     *
     * @return void
     */
    public function testClose(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');
        $resource = fopen($file, 'r');

        (new Stream($resource))->close();

        $this->assertFalse(is_resource($resource));
    }

    /**
     * Test close on already closed.
     *
     * @return void
     */
    public function testCloseOnAlreadyClosed(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');
        $resource = fopen($file, 'r');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertFalse(is_resource($resource));

        $stream->close();

        $this->assertFalse(is_resource($resource));
    }

    /**
     * Test detach.
     *
     * @return void
     */
    public function testDetach(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');

        $stream = new Stream(fopen($file, 'r'));

        $this->assertTrue(is_resource($stream->detach()));
    }

    /**
     * Test detach on already detached.
     *
     * @return void
     */
    public function testDetachOnAlreadyDetached(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');

        $stream = new Stream(fopen($file, 'r'));

        $this->assertTrue(is_resource($stream->detach()));
        $this->assertNull($stream->detach());
    }

    /**
     * Test get size.
     *
     * @return void
     */
    public function testGetSize(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');

        $stream = new Stream(fopen($file, 'w'));

        $stream->write('abcdefghijklmnopqrstuwxyz');

        $this->assertEquals(25, $stream->getSize());

        $stream->close();
    }

    /**
     * Test get size with no resource.
     *
     * @return void
     */
    public function testGetSizeWithNoResource(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test');

        $stream = new Stream(fopen($file, 'w'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->close();

        $this->assertEquals(0, $stream->getSize());
    }

    /**
     * Test tell.
     *
     * @return void
     */
    public function testTell(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));

        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $this->assertEquals(0, $stream->tell());

        $stream->seek(2);
        $this->assertEquals(2, $stream->tell());

        $stream->rewind();
        $this->assertEquals(0, $stream->tell());

        $stream->close();
    }

    /**
     * Test tell with no resource.
     *
     * @return void
     */
    public function testTellWithNoResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Resource not available.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));
        $stream->close();

        $stream->tell();
    }

    /**
     * Test eof.
     *
     * @return void
     */
    public function testEof(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));

        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        //not at the end of the file
        $this->assertEquals(0, $stream->tell());
        $this->assertFalse($stream->eof());

        //not at the end of the file
        $stream->read(25);
        $this->assertEquals(25, $stream->tell());
        $this->assertFalse($stream->eof());

        //at the end of the file
        $stream->read(1);
        $this->assertEquals(25, $stream->tell());
        $this->assertTrue($stream->eof());

        //true with stream closed
        $stream->close();
        $this->assertTrue($stream->eof());
    }

    /**
     * Test is seekable.
     *
     * @return void
     */
    public function testIsSeekable(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'r'));

        $this->assertTrue($stream->isSeekable());

        $stream->close();
        $this->assertFalse($stream->isSeekable());
    }

    /**
     * Test is seekable with not seekable stream.
     *
     * @return void
     */
    public function testIsSeekableWithNotSeekableStream(): void
    {
        $stream = new Stream('php://output', 'w');

        $this->assertFalse($stream->isSeekable());

        $stream->close();
    }

    /**
     * Test rewind.
     *
     * @return void
     */
    public function testRewind(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));

        $stream->write('abcdefghijklmnopqrstuwxyz');

        $this->assertEquals(25, $stream->tell());

        $stream->rewind();
        $this->assertEquals(0, $stream->tell());

        $stream->read(25);
        $this->assertEquals(25, $stream->tell());

        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * Test rewind with not seekable stream.
     *
     * @return void
     */
    public function testRewindWithNotSeekableStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Can not rewind the stream.");

        $stream = new Stream('php://output', 'w');
        $stream->rewind();
    }

    /**
     * Writable mode provider.
     *
     * @return array
     */
    public function writableModeProvider(): array
    {
        return [
            ['a', true],
            ['a+', true],
            ['a+b', true],
            ['ab', true],
            ['c', true],
            ['c+', true],
            ['c+b', true],
            ['cb', true],
            ['r', false],
            ['r+', true],
            ['r+b', true],
            ['rb', false],
            ['rw', true],
            ['w', true],
            ['w+', true],
            ['w+b', true],
            ['wb', true],
            ['x', true],
            ['x+', true],
            ['x+b', true],
            ['xb', true]
        ];
    }

    /**
     * Test is writable.
     *
     * @dataProvider writableModeProvider
     *
     * @return void
     */
    public function testIsWritable(string $mode, bool $result): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        //http://php.net/manual/en/function.fopen.php
        //'x' 	Create and open for writing only; place the file pointer at the beginning of the file.
        //If the file already exists, the fopen() call will fail by returning FALSE and generating an
        //error of level E_WARNING. If the file does not exist, attempt to create it.
        //This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
        if (strpos($mode, 'x') !== false) {
            unlink($file);
        }

        $stream = new Stream(fopen($file, $mode));

        $this->assertEquals($result, $stream->isWritable());
    }

    /**
     * Test is writable with no resource.
     *
     * @return void
     */
    public function testIsWritableWithNoResource(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));
        $stream->close();

        $this->assertFalse($stream->isWritable());
    }

    /**
     * Test write.
     *
     * @return void
     */
    public function testWrite(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));

        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $this->assertEquals('abcdefghijklmnopqrstuwxyz', $stream->read(25));
    }

    /**
     * Test write with no resource.
     *
     * @return void
     */
    public function testWriteWithNoResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Resource not available.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));
        $stream->close();

        $stream->write('abcdefghijklmnopqrstuwxyz');
    }

    /**
     * Test write with not writable stream.
     *
     * @return void
     */
    public function testWriteWithNotWritableStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not writable.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'r'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
    }

    /**
     * Readable mode provider.
     *
     * @return array
     */
    public function readableModeProvider(): array
    {
        return [
            ['a', false],
            ['a+', true],
            ['a+b', true],
            ['ab', false],
            ['c', false],
            ['c+', true],
            ['c+b', true],
            ['cb', false],
            ['r', true],
            ['r+', true],
            ['r+b', true],
            ['rb', true],
            ['rw', true],
            ['w', false],
            ['w+', true],
            ['w+b', true],
            ['wb', false],
            ['x', false],
            ['x+', true],
            ['x+b', true],
            ['xb', false]
        ];
    }

    /**
     * Test is readable.
     *
     * @dataProvider readableModeProvider
     *
     * @return void
     */
    public function testIsReadable(string $mode, bool $result): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        //http://php.net/manual/en/function.fopen.php
        //'x' 	Create and open for writing only; place the file pointer at the beginning of the file.
        //If the file already exists, the fopen() call will fail by returning FALSE and generating an
        //error of level E_WARNING. If the file does not exist, attempt to create it.
        //This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
        if (strpos($mode, 'x') !== false) {
            unlink($file);
        }

        $stream = new Stream(fopen($file, $mode));

        $this->assertEquals($result, $stream->isReadable());
    }

    /**
     * Test read.
     *
     * @return void
     */
    public function testRead(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));

        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $this->assertEquals('abcdefghijklmnopqrstuwxyz', $stream->read(25));
    }

    /**
     * Test read with no resource.
     *
     * @return void
     */
    public function testReadWithNoResource(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Resource not available.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->close();

        $stream->read(25);
    }

    /**
     * Test write with not readable stream.
     *
     * @return void
     */
    public function testReadWithNotReadableStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not readable.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'w'));
        $stream->write('abcdefghijklmnopqrstuwxyz');

        $stream->read(25);
    }

    /**
     * Test get contents.
     *
     * @return void
     */
    public function testGetContents(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'rw+'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $this->assertEquals('abcdefghijklmnopqrst', $stream->read(20));
        $this->assertEquals('uwxyz', $stream->getContents());
    }

    /**
     * Test get contents with not readable stream.
     *
     * @return void
     */
    public function testGetContentsWithNotReadableStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not readable.");

        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'w'));
        $stream->write('abcdefghijklmnopqrstuwxyz');

        $stream->getContents();
    }


    /**
     * Test get metadata.
     *
     * @return void
     */
    public function testGetMetadata(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'wr+'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $metadata = $stream->getMetadata();

        $this->assertArrayHasKey('timed_out', $metadata);
        $this->assertArrayHasKey('blocked', $metadata);
        $this->assertArrayHasKey('eof', $metadata);
        $this->assertArrayHasKey('unread_bytes', $metadata);
        $this->assertArrayHasKey('stream_type', $metadata);
        $this->assertArrayHasKey('wrapper_type', $metadata);
        //$this->assertArrayHasKey('wrapper_data', $metadata);
        $this->assertArrayHasKey('mode', $metadata);
        $this->assertArrayHasKey('seekable', $metadata);
        $this->assertArrayHasKey('uri', $metadata);
    }

    /**
     * Key provider.
     *
     * @return array
     */
    public function keyProvider(): array
    {
        return [
            ['timed_out'],
            ['blocked'],
            ['eof'],
            ['unread_bytes'],
            ['stream_type'],
            ['wrapper_type'],
            //['wrapper_data'],
            ['mode'],
            ['seekable'],
            ['uri']
        ];
    }

    /**
     * Test get metadata with specific key.
     *
     * @dataProvider keyProvider
     *
     * @param string $key
     *
     * @return void
     */
    public function testGetMetadataWithSpecificKey(string $key): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'wr+'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $metadata = $stream->getMetadata($key);

        $this->assertTrue(is_array($metadata));
        $this->assertEquals(1, count($metadata));
        $this->assertTrue(array_key_exists($key, $metadata));
    }

    /**
     * Test get metadata with unknown key.
     *
     * @return void
     */
    public function testGetMetadataWithUnknownKey(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'stream_test_');

        $stream = new Stream(fopen($file, 'wr+'));
        $stream->write('abcdefghijklmnopqrstuwxyz');
        $stream->rewind();

        $metadata = $stream->getMetadata('unknown');

        $this->assertTrue(is_array($metadata));
        $this->assertEquals(0, count($metadata));
    }
}
