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

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Psr7 Uri Implementation.
 */
class Uri implements UriInterface
{
    use UriTrait;

    /**
     * @var array Standard schemes.
     */
    protected $standardSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    /**
     * @var array Url description.
     */
    protected $url = [
        'scheme'   => '',
        'host'     => '',
        'port'     => 0,
        'user'     => '',
        'pass'     => '',
        'path'     => '',
        'query'    => '',
        'fragment' => '',
    ];

    /**
     * Constructor.
     *
     * @param string $uri
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $uri)
    {
        if (($parsedUrl = parse_url($uri)) === false) {
            throw new InvalidArgumentException(__CLASS__.': Bad URI provided for '.__METHOD__);
        }

        $this->url = array_merge($this->url, $parsedUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme() : string
    {
        return strtolower($this->url['scheme']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority() : string
    {
        if ($this->url['host'] === '') {
            return '';
        }

        $authority = $this->url['host'];

        if ($this->url['user'] !== '') {
            $authority = $this->getUserInfo().'@'.$authority;
        }

        if ($this->url['port'] !== 0) {
            $authority .= ':'.$this->url['port'];
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo() : string
    {
        $user = $this->url['user'];

        if ($this->url['pass'] !== '' && $this->url['pass'] !== null) {
            $user .= ':'.$this->url['pass'];
        }

        return ($user !== '') ? $user : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHost() : string
    {
        return strtolower($this->url['host']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort() : int
    {
        $scheme = $this->url['scheme'];
        $port = $this->url['port'];

        $standardPort = $this->checkStandardPortForCurretScheme($scheme, $port, $this->standardSchemes);
        $standardScheme = array_key_exists($scheme, $this->standardSchemes);

        //scheme present and port standard - return 0
        //scheme present and port non standard - return port
        if ($standardPort && $standardScheme) {
            return $this->getPortForStandardScheme($standardPort, $port);
        }

        //scheme present and standard, port non present - return port
        //scheme non standard, port present - return port
        return $this->getNonStandardPort($port, $scheme, $standardScheme, $this->standardSchemes);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath() : string
    {
        return $this->url['path'];
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery() : string
    {
        return $this->url['query'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment() : string
    {
        return $this->url['fragment'];
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme(string $scheme) : UriInterface
    {
        if (!array_key_exists($scheme, $this->standardSchemes)) {
            throw new InvalidArgumentException(__CLASS__.': Invalid or unsupported scheme provided for '.__METHOD__);
        }

        $new = clone $this;
        $new->url['scheme'] = $scheme;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo(string $user, string $password = '') : UriInterface
    {
        $new = clone $this;
        $new->url['user'] = $user;
        $new->url['pass'] = $password;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost(string $host) : UriInterface
    {
        if (filter_var($host, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME) === false) {
            throw new InvalidArgumentException(__CLASS__.': Invalid host provided for '.__METHOD__);
        }

        $new = clone $this;
        $new->url['host'] = $host;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort(int $port = 0) : UriInterface
    {
        if ($port !== 0 && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException(__CLASS__.': Invalid port ('.$port.') number provided for '.__METHOD__);
        }

        $new = clone $this;
        $new->url['port'] = $port;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath(string $path) : UriInterface
    {
        if (strpos($path, '?') !== false) {
            throw new \InvalidArgumentException(__CLASS__.': Invalid path provided; must not contain a query string');
        }

        if (strpos($path, '#') !== false) {
            throw new \InvalidArgumentException(__CLASS__.': Invalid path provided; must not contain a URI fragment');
        }

        $new = clone $this;
        $new->url['path'] = $path;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery(string $query) : UriInterface
    {
        if (strpos($query, '#') !== false) {
            throw new \InvalidArgumentException(__CLASS__.': Query string must not include a URI fragment');
        }

        $new = clone $this;
        $new->url['query'] = (strpos($query, '?') !== false) ? substr($query, 1) : $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment(string $fragment) : UriInterface
    {
        $new = clone $this;
        $new->url['fragment'] = (strpos($fragment, '#') !== false) ? substr($fragment, 1) : $fragment;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        $scheme = $this->url['scheme'];
        $query = $this->url['query'];
        $fragment = $this->url['fragment'];

        return $this->createUriString(
            ($scheme !== '') ? $scheme.'://' : '',
               $this->getAuthority(),
               $this->getPath(),
               ($query !== '') ? '?'.$query : '',
               ($fragment !== '') ? '#'.$fragment : ''
        );
    }
}
