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

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Psr7 Stream implementation.
 */
class Stream implements StreamInterface
{
    /**
     * @var resource The streem resource.
     */
    protected $resource;

    /**
     * @var bool Is stream a proces file pointer?
     */
    protected $isPipe;

    /**
     * Constructor.
     *
     * @param Resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(__CLASS__.': Invalid resource provided');
        }

        if ('stream' !== get_resource_type($resource)) {
            throw new InvalidArgumentException(__CLASS__.': Resource provided is not a stream');
        }

        $this->resource = $resource;
        $this->isPipe = $this->checkFileMode($resource);
    }

    /**
     * Check if file is a pipe.
     * http://man7.org/linux/man-pages/man7/inode.7.html
     *
     * @param type $resource
     * @return bool
     */
    protected function checkFileMode($resource): bool
    {
        //file modes
        //check if resource is a process file pointer.
        //0140000   socket
        //0120000   symbolic link
        //0100000   regular file
        //0060000   block device
        //0040000   directory
        //0020000   character device
        //0010000   FIFO
        return ((fstat($resource)['mode'] & 0010000) !== 0) ? true: false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }

        if ($this->isPipe) {
            pclose($this->resource);
            $this->resource = null;
            return;
        }

        fclose($this->resource);
        $this->resource = null;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if (!$this->resource) {
            return;
        }

        $tmpResource = $this->resource;
        $this->resource = false;

        return $tmpResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return (!$this->resource) ? 0 : fstat($this->resource)['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!$this->resource) {
            throw new RuntimeException(__CLASS__.': No resource available; cannot tell position');
        }

        if (($position = ftell($this->resource)) === false) {
            throw new RuntimeException(__CLASS__.': Error occurred during tell operation');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return (!$this->resource) ? feof($this->resource) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return (!$this->resource) ? false : stream_get_meta_data($this->resource)['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException(__CLASS__.': Can not seek the stream');
        }

        if (fseek($this->resource, $offset, $whence) !== 0) {
            throw new RuntimeException(__CLASS__.': Error seeking within stream');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new RuntimeException(__CLASS__.': Can not rewind the stream');
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return (!$this->resource) ? false : $this->can(['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $string): int
    {
        //if (!$this->resource) {
        //    throw new RuntimeException(__CLASS__.': Resource not available; '.__METHOD__);
        //}

        if (!$this->isWritable()) {
            throw new RuntimeException(__CLASS__.': Stream is not writable; '.__METHOD__);
        }

        if (($bytes = fwrite($this->resource, $string)) === false) {
            throw new RuntimeException(__CLASS__.': Error writing stream; '.__METHOD__);
        }

        return $bytes;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return (!$this->resource) ? false : $this->can(['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        //if (!$this->resource) {
        //    throw new RuntimeException(__CLASS__.': Resource not available; '.__METHOD__);
        //}

        if (!$this->isReadable()) {
            throw new RuntimeException(__CLASS__.': Stream is not readable; '.__METHOD__);
        }

        if (($data = fread($this->resource, $length)) === false) {
            throw new RuntimeException(__CLASS__.': Error reading stream; '.__METHOD__);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException(__CLASS__.': Stream is not readable; '.__METHOD__);
        }

        if (($content = stream_get_contents($this->resource)) === false) {
            throw new RuntimeException(__CLASS__.': Error reading stream; '.__METHOD__);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata(string $key = ''): array
    {
        $metadata = stream_get_meta_data($this->resource);

        //if key is empty strung
        return ($key === '') ?
            //return metadata
            $metadata :
            //else check if key exist and if key exist return as array else return void array
            (isset($metadata[$key]) ? [$key => $metadata[$key]] : []);
    }
}
