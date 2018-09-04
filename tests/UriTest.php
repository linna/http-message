<?php

/**
 * Linna Psr7.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2018, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace Linna\Tests;

use Linna\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    protected $uri = 'http://username:password@hostname.com:9090/path?arg=value#anchor';

    /**
     * Test new instance.
     */
    public function testNewInstance(): void
    {
        $uri = new Uri($this->uri);

        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('username:password', $uri->getUserInfo());
        $this->assertEquals('hostname.com', $uri->getHost());
        $this->assertEquals(9090, $uri->getPort());
        $this->assertEquals('username:password@hostname.com:9090', $uri->getAuthority());
        $this->assertEquals('/path', $uri->getPath());
        $this->assertEquals('arg=value', $uri->getQuery());
        $this->assertEquals('anchor', $uri->getFragment());
    }

    /**
     * Test new instance with worng uri.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNewInstanceWithWrongUri(): void
    {
        (new Uri('http:///example.com'));
    }

    /**
     * Wrong argument provider.
     *
     * @return array
     */
    public function wrongArgumentProvider(): array
    {
        return [
            [1],
            [1.1],
            [[1]],
            [(object) [1]],
            [true],
            [function () {
                return 1;
            }],
        ];
    }

    /**
     * Test new instance with wrong argument type.
     *
     * @dataProvider wrongArgumentProvider
     * @expectedException TypeError
     */
    public function testNewInstanceWithWrongArgumentType($argument): void
    {
        (new Uri($argument));
    }

    /**
     * Authority provider.
     *
     * @return array
     */
    public function authorityProvider(): array
    {
        return [
            ['http://username:password@hostname.com:9090', 'username:password@hostname.com:9090'],
            ['http://username@hostname.com:9090', 'username@hostname.com:9090'],
            ['http://username@hostname.com', 'username@hostname.com'],
            ['http://hostname.com', 'hostname.com'],
            ['http://hostname.com:9090', 'hostname.com:9090'],
            ['', ''],
        ];
    }

    /**
     * Test get authority.
     *
     * @dataProvider authorityProvider
     */
    public function testGetAuthority(string $autority, string $expected): void
    {
        $this->assertEquals($expected, (new Uri("{$autority}/path?arg=value#anchor"))->getAuthority());
    }

    /**
     * Port provider.
     *
     * @return array
     */
    public function portProvider(): array
    {
        return [
            ['http', ':80', 0], //standard scheme, standard port - return zero
            ['http', ':9090', 9090], //standard scheme, non standard port - return port
            ['http', '', 80], //standard scheme, non present port - return standard port
            ['ftp', ':21', 21], //non standard scheme, port present - return port
            ['ftp', '', 0], //non standard scheme, port non present - return zero
        ];
    }

    /**
     * Test get port.
     *
     * @dataProvider portProvider
     */
    public function testGetPort(string $scheme, string $port, int $expected): void
    {
        $this->assertEquals($expected, (new Uri("{$scheme}://username:password@hostname.com{$port}/path?arg=value#anchor"))->getPort());
    }

    /**
     * Test with scheme.
     */
    public function testWithScheme(): void
    {
        $uri = (new Uri($this->uri))->withScheme('https');

        $this->assertEquals('https', $uri->getScheme());
    }

    /**
     * Test with scheme with unsupported scheme.
     *
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeWithUnsupportedScheme(): void
    {
        (new Uri($this->uri))->withScheme('httpss');
    }

    /**
     * Test with scheme with wrong scheme type.
     *
     * @expectedException TypeError
     * @dataProvider wrongArgumentProvider
     */
    public function testWithSchemeWithWrongSchemeType($argument): void
    {
        (new Uri($this->uri))->withScheme($argument);
    }

    /**
     * Test with user info.
     */
    public function testWithUserInfo(): void
    {
        $uri = (new Uri($this->uri))->withUserInfo('testUser', 'password');

        $this->assertEquals('testUser:password', $uri->getUserInfo());
    }

    /**
     * Test user info without password.
     */
    public function testWithUserInfoWithoutPassword(): void
    {
        $uri = (new Uri($this->uri))->withUserInfo('testUser');

        $this->assertEquals('testUser', $uri->getUserInfo());
    }

    /**
     * Test user info without user and password.
     */
    public function testWithUserInfoWithoutUserAndPassword(): void
    {
        $uri = (new Uri($this->uri))->withUserInfo('');

        $this->assertEquals('', $uri->getUserInfo());
    }

    /**
     * Test with host.
     */
    public function testWithHost(): void
    {
        $uri = (new Uri($this->uri))->withHost('example.com');

        $this->assertEquals('example.com', $uri->getHost());
    }

    /**
     * Test with host with invalid host format.
     *
     * @expectedException InvalidArgumentException
     */
    public function testWithHostWithInvalidHostFormat(): void
    {
        (new Uri($this->uri))->withHost('host?name');
    }

    /**
     * Test with host with wrong host type.
     *
     * @expectedException TypeError
     * @dataProvider wrongArgumentProvider
     */
    public function testWithHostWithWrongHostType($argument): void
    {
        (new Uri($this->uri))->withHost($argument);
    }

    /**
     * Test with port.
     */
    public function testWithPort(): void
    {
        $uri = (new Uri($this->uri))->withPort(8080);

        $this->assertEquals(8080, $uri->getPort());
    }

    /**
     * Test with null port.
     */
    public function testWithPortNullPort(): void
    {
        $uri = (new Uri($this->uri))->withPort();

        $this->assertEquals(80, $uri->getPort());
    }

    /**
     * Out of range port provider.
     *
     * @return array
     */
    public function outOfRangePortProvider(): array
    {
        return [
          [-1],
          [65536],
        ];
    }

    /**
     * Test with port with out of range port.
     *
     * @dataProvider outOfRangePortProvider
     * @expectedException InvalidArgumentException
     */
    public function testWithPortWithOutOfRangePort(int $port): void
    {
        (new Uri($this->uri))->withPort($port);
    }

    /**
     * Wrong port type provider.
     *
     * @return array
     */
    public function wrongPortTypeProvider(): array
    {
        return [
            ['1'],
            [1.1],
            [[1]],
            [(object) [1]],
            [true],
            [function () {
                return 1;
            }],
        ];
    }

    /**
     * Test with port with wrong port type.
     *
     * @param mixed $port
     *
     * @dataProvider wrongPortTypeProvider
     * @expectedException TypeError
     */
    public function testWithPortWithWrongPortType($port): void
    {
        (new Uri($this->uri))->withPort($port);
    }

    /**
     * Test with path.
     */
    public function testWithPath(): void
    {
        $uri = (new Uri($this->uri))->withPath('/otherpath');

        $this->assertEquals('/otherpath', $uri->getPath());
        $this->assertEquals('http://username:password@hostname.com:9090/otherpath?arg=value#anchor', (string) $uri);
    }

    /**
     * Test with path with wrong path type.
     *
     * @dataProvider wrongArgumentProvider
     * @expectedException TypeError
     */
    public function testWithPathWithWrongPathType($path): void
    {
        (new Uri($this->uri))->withPath($path);
    }

    /**
     * Test with path passing query string.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Linna\Psr7\Uri: Invalid path provided; must not contain a query string
     */
    public function testWithPathPassingQueryString(): void
    {
        (new Uri($this->uri))->withPath('/otherPath?arg=value');
    }

    /**
     * Test with path passing fragment.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Linna\Psr7\Uri: Invalid path provided; must not contain a URI fragment
     */
    public function testWithPathPassingFragment(): void
    {
        (new Uri($this->uri))->withPath('/otherPath#anchor');
    }

    /**
     * Uri provider.
     *
     * @return array
     */
    public function uriProvider(): array
    {
        return[
            ['http://username:password@hostname.com:8080/path?arg=value#anchor'],
            ['http://username:password@hostname.com:8080/path?arg=value'],
            ['http://username:password@hostname.com:8080/path'],
            ['http://username:password@hostname.com:8080/'],
            ['http://username:password@hostname.com:8080'],
            ['http://username:password@hostname.com/'],
            ['http://username:password@hostname.com'],
            ['http://hostname.com'],
            ['/hostname.com/path'],
            ['/hostname.com/path/'],
            ['hostname.com/path'],
            ['hostname.com/path/'],
        ];
    }

    /**
     * Test uri __toString.
     *
     * @dataProvider uriProvider
     */
    public function testUriToString(string $testUri): void
    {
        $this->assertEquals($testUri, (string) (new Uri($testUri)));
    }

    /**
     * Query provider.
     *
     * @return array
     */
    public function queryProvider(): array
    {
        return [
            ['?arg1=foo&arg2=foo&arg3=baz', 'arg1=foo&arg2=foo&arg3=baz', 'http://username:password@hostname.com:9090/path?arg1=foo&arg2=foo&arg3=baz#anchor'],
            ['arg1=foo&arg2=foo&arg3=baz', 'arg1=foo&arg2=foo&arg3=baz', 'http://username:password@hostname.com:9090/path?arg1=foo&arg2=foo&arg3=baz#anchor'],
            ['', '', 'http://username:password@hostname.com:9090/path#anchor'],
        ];
    }

    /**
     * Test with query.
     *
     * @param string $withQuery
     * @param string $expectedQuery
     * @param string $expectedUri
     *
     * @dataProvider queryProvider
     */
    public function testWithQuery(string $withQuery, string $expectedQuery, string $expectedUri): void
    {
        $uri = (new Uri($this->uri))->withQuery($withQuery);

        $this->assertEquals($expectedQuery, $uri->getQuery());
        $this->assertEquals($expectedUri, (string) $uri);
    }

    /**
     * Test with query with wrong query type.
     *
     * @dataProvider wrongArgumentProvider
     * @expectedException TypeError
     */
    public function testWithQueryWithWrongQueryType($query): void
    {
        (new Uri($this->uri))->withQuery($query);
    }

    /**
     * Test with query passing.
     *
     * @expectedException InvalidArgumentException
     */
    public function testWithQueryPassingFragment(): void
    {
        (new Uri($this->uri))->withQuery('arg=foo#anchor2');
    }

    /**
     * Fragment provider.
     *
     * @return array
     */
    public function fragmentProvider(): array
    {
        return [
            ['otherAnchor', 'otherAnchor', 'http://username:password@hostname.com:9090/path?arg=value#otherAnchor'],
            ['#otherAnchor', 'otherAnchor', 'http://username:password@hostname.com:9090/path?arg=value#otherAnchor'],
            ['', '', 'http://username:password@hostname.com:9090/path?arg=value'],
        ];
    }

    /**
     * Test with fragment.
     *
     * @param string $withFragment
     * @param string $expectedFragment
     * @param string $expectedUri
     *
     * @dataProvider fragmentProvider
     */
    public function testWithFragment(string $withFragment, string $expectedFragment, string $expectedUri): void
    {
        $uri = (new Uri($this->uri))->withFragment($withFragment);

        $this->assertEquals($expectedFragment, $uri->getFragment());
        $this->assertEquals($expectedUri, (string) $uri);
    }

    /**
     * Test with fragment with wrong query type.
     *
     * @dataProvider wrongArgumentProvider
     * @expectedException TypeError
     */
    public function testWithFragmentWithWrongFragmentType($fragment): void
    {
        (new Uri($this->uri))->withFragment($fragment);
    }
}
