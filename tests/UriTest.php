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
use Linna\Http\Message\Uri;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Uri Test
 */
class UriTest extends TestCase
{
    /**
     * @var string Uri for tests
     */
    protected static $uri = 'http://username:password@hostname.com:9090/path?arg=value#anchor';

    /**
     * Test new instance.
     *
     * @return void
     */
    public function testNewInstance(): void
    {
        $uri = new Uri(self::$uri);

        $this->assertInstanceOf(Uri::class, $uri);
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
     * @return void
     */
    public function testNewInstanceWithWrongUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bad URI provided.");

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
     *
     * @return void
     */
    public function testNewInstanceWithWrongArgumentType($argument): void
    {
        $this->expectException(TypeError::class);

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
     *
     * @return void
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
     *
     * @return void
     */
    public function testGetPort(string $scheme, string $port, int $expected): void
    {
        $this->assertEquals($expected, (new Uri("{$scheme}://username:password@hostname.com{$port}/path?arg=value#anchor"))->getPort());
    }

    /**
     * Test with scheme.
     *
     * @return void
     */
    public function testWithScheme(): void
    {
        $uri = (new Uri(self::$uri))->withScheme('https');

        $this->assertEquals('https', $uri->getScheme());
    }

    /**
     * Test with scheme with unsupported scheme.
     *
     * @return void
     */
    public function testWithSchemeWithUnsupportedScheme(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri(self::$uri))->withScheme('httpss');
    }

    /**
     * Test with scheme with wrong scheme type.
     *
     * @dataProvider wrongArgumentProvider
     *
     * @return void
     */
    public function testWithSchemeWithWrongSchemeType($argument): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withScheme($argument);
    }

    /**
     * Test with user info.
     *
     * @return void
     */
    public function testWithUserInfo(): void
    {
        $uri = (new Uri(self::$uri))->withUserInfo('testUser', 'password');

        $this->assertEquals('testUser:password', $uri->getUserInfo());
    }

    /**
     * Test user info without password.
     *
     * @return void
     */
    public function testWithUserInfoWithoutPassword(): void
    {
        $uri = (new Uri(self::$uri))->withUserInfo('testUser');

        $this->assertEquals('testUser', $uri->getUserInfo());
    }

    /**
     * Test user info without user and password.
     *
     * @return void
     */
    public function testWithUserInfoWithoutUserAndPassword(): void
    {
        $uri = (new Uri(self::$uri))->withUserInfo('');

        $this->assertEquals('', $uri->getUserInfo());
    }

    /**
     * Test with host.
     *
     * @return void
     */
    public function testWithHost(): void
    {
        $uri = (new Uri(self::$uri))->withHost('example.com');

        $this->assertEquals('example.com', $uri->getHost());
    }

    /**
     * Test with host with invalid host format.
     *
     * @return void
     */
    public function testWithHostWithInvalidHostFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri(self::$uri))->withHost('host?name');
    }

    /**
     * Test with host with wrong host type.
     *
     * @dataProvider wrongArgumentProvider
     *
     * @return void
     */
    public function testWithHostWithWrongHostType($argument): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withHost($argument);
    }

    /**
     * Test with port.
     *
     * @return void
     */
    public function testWithPort(): void
    {
        $uri = (new Uri(self::$uri))->withPort(8080);

        $this->assertEquals(8080, $uri->getPort());
    }

    /**
     * Test with null port.
     *
     * @return void
     */
    public function testWithPortNullPort(): void
    {
        $uri = (new Uri(self::$uri))->withPort();

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
     *
     * @return void
     */
    public function testWithPortWithOutOfRangePort(int $port): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri(self::$uri))->withPort($port);
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
     *
     * @return void
     */
    public function testWithPortWithWrongPortType($port): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withPort($port);
    }

    /**
     * Test with path.
     *
     * @return void
     */
    public function testWithPath(): void
    {
        $uri = (new Uri(self::$uri))->withPath('/otherpath');

        $this->assertEquals('/otherpath', $uri->getPath());
        $this->assertEquals('http://username:password@hostname.com:9090/otherpath?arg=value#anchor', (string) $uri);
    }

    /**
     * Test with path with wrong path type.
     *
     * @dataProvider wrongArgumentProvider
     *
     * @return void
     */
    public function testWithPathWithWrongPathType($path): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withPath($path);
    }

    /**
     * Test with path passing query string.
     *
     * @return void
     */
    public function testWithPathPassingQueryString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid path provided; must not contain a query string");

        (new Uri(self::$uri))->withPath('/otherPath?arg=value');
    }

    /**
     * Test with path passing fragment.
     *
     * @return void
     */
    public function testWithPathPassingFragment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid path provided; must not contain a URI fragment");

        (new Uri(self::$uri))->withPath('/otherPath#anchor');
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
     *
     * @return void
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
     *
     * @return void
     */
    public function testWithQuery(string $withQuery, string $expectedQuery, string $expectedUri): void
    {
        $uri = (new Uri(self::$uri))->withQuery($withQuery);

        $this->assertEquals($expectedQuery, $uri->getQuery());
        $this->assertEquals($expectedUri, (string) $uri);
    }

    /**
     * Test with query with wrong query type.
     *
     * @dataProvider wrongArgumentProvider
     *
     * @return void
     */
    public function testWithQueryWithWrongQueryType($query): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withQuery($query);
    }

    /**
     * Test with query passing.
     *
     * @return void
     */
    public function testWithQueryPassingFragment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uri(self::$uri))->withQuery('arg=foo#anchor2');
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
     *
     * @return void
     */
    public function testWithFragment(string $withFragment, string $expectedFragment, string $expectedUri): void
    {
        $uri = (new Uri(self::$uri))->withFragment($withFragment);

        $this->assertEquals($expectedFragment, $uri->getFragment());
        $this->assertEquals($expectedUri, (string) $uri);
    }

    /**
     * Test with fragment with wrong query type.
     *
     * @dataProvider wrongArgumentProvider
     *
     * @return void
     */
    public function testWithFragmentWithWrongFragmentType($fragment): void
    {
        $this->expectException(TypeError::class);

        (new Uri(self::$uri))->withFragment($fragment);
    }
}
