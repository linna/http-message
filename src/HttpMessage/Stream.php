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
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * PSR-7 Stream implementation.
 */
class Stream implements StreamInterface
{
    /**
     * @var mixed The streem resource.
     */
    protected $resource;

    /**
     * Class Constructor.
     *
     * @param string|resource $stream
     * @param string $mode
     *
     * @throws InvalidArgumentException
     */
    public function __construct($stream, string $mode = 'r')
    {
        if (is_string($stream)) {
            $error = null;

            set_error_handler(function ($e) use (&$error) {
                if ($e === 2) {
                    $error = $e;
                }
            });

            $stream = fopen($stream, $mode);

            restore_error_handler();

            if ($error) {
                throw new InvalidArgumentException('Invalid stream identifier provided.');
            }
        }

        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid resource provided.');
        }

        if (get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Resource provided is not a stream.');
        }

        $this->resource = $stream;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (RuntimeException $e) {
            unset($e);
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void
    {
        if (!$this->resource) {
            return;
        }

        fclose($this->resource);
        $this->resource = null;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any.
     */
    public function detach()
    {
        if (!$this->resource) {
            return null;
        }

        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int Returns the size in bytes if known, or zero if unknown.
     */
    public function getSize(): int
    {
        return (!$this->resource) ? 0 : fstat($this->resource)['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer.
     *
     * @throws RuntimeException on error.
     */
    public function tell(): int
    {
        if (!$this->resource) {
            throw new RuntimeException('Resource not available.');
        }

        if (($position = ftell($this->resource)) === false) {
            throw new RuntimeException('Error occurred during tell operation.');
        }

        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        if (!$this->resource) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return (!$this->resource) ? false : stream_get_meta_data($this->resource)['seekable'];
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated based on the seek offset.
     *                    Valid values are identical to the built-in PHP $whence values for `fseek()`.
     *                    SEEK_SET: Set position equal to offset bytes.
     *                    SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws RuntimeException on failure.
     */
    public function seek(int $offset, int $whence = SEEK_SET)
    {
        if (!$this->resource) {
            throw new RuntimeException('Resource not available.');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Can not seek the stream.');
        }

        if (fseek($this->resource, $offset, $whence) !== 0) {
            throw new RuntimeException('Error seeking within stream.');
        }

        return true;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @throws RuntimeException on failure.
     */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->resource) === false) {
            throw new RuntimeException('Can not rewind the stream.');
        }
    }

    /**
     * Check modes.
     *
     * @param array $modes
     *
     * @return bool
     */
    protected function can(array $modes): bool
    {
        $metaMode = stream_get_meta_data($this->resource)['mode'];

        foreach ($modes as $mode) {
            if (strpos($metaMode, $mode) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return (!$this->resource) ? false : $this->can(['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     *
     * @throws RuntimeException on failure.
     */
    public function write(string $string): int
    {
        if (!$this->resource) {
            throw new RuntimeException('Resource not available.');
        }

        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable.');
        }

        if (($bytes = fwrite($this->resource, $string)) === false) {
            throw new RuntimeException('Error writing stream.');
        }

        return $bytes;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return (!$this->resource) ? false : $this->can(['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return them.
     *                    Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *                if no bytes are available.
     *
     * @throws RuntimeException if an error occurs.
     */
    public function read(int $length): string
    {
        if (!$this->resource) {
            throw new RuntimeException('Resource not available.');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        if (($data = fread($this->resource, $length)) === false) {
            throw new RuntimeException('Error reading stream.');
        }

        return $data;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @return string
     *
     * @throws RuntimeException if unable to read or an error occurs while
     *                          reading.
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        if (($content = stream_get_contents($this->resource)) === false) {
            throw new RuntimeException('Error reading stream.');
        }

        return $content;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array Returns an associative array if no key is provided.
     *               Returns an associative array with the specific  key value
     *               if a key is provided and the value is found, or a void
     *               array if the key is not found.
     */
    public function getMetadata(string $key = ''): array
    {
        if (!$this->resource) {
            throw new RuntimeException('Resource not available.');
        }

        $metadata = stream_get_meta_data($this->resource);

        //if key is empty string
        return ($key === '') ?
            //return metadata
            $metadata :
            //else check if key exist and if key exist return as array else return void array
            (isset($metadata[$key]) ? [$key => $metadata[$key]] : []);
    }
}
