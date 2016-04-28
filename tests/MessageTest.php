<?php

namespace Meek\Http;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    private $message;

    public function setUp()
    {
        $this->message = $this->getMockForTrait(Message::class);
    }

    /**
     * @covers Meek\Http\Message::getProtocolVersion
     */
    public function testHasDefaultProtocol()
    {
        $this->assertEquals('1.1', $this->message->getProtocolVersion());
    }

    /**
     * @covers Meek\Http\Message::withProtocolVersion
     * @dataProvider invalidProtocolVersions
     * @expectedException InvalidArgumentException
     */
    public function testThrowsErrorWithIncorrectProtocolVersionOrTypes($invalidVersion)
    {
        $message = $this->message->withProtocolVersion($invalidVersion);
    }

    /**
     * @covers Meek\Http\Message::getProtocolVersion
     * @covers Meek\Http\Message::withProtocolVersion
     */
    public function testChangingProtocolKeepsMessageImmuttable()
    {
        $message = $this->message->withProtocolVersion('1.0');

        $this->assertNotSame($this->message, $message);
        $this->assertEquals('1.1', $this->message->getProtocolVersion());
        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    /**
     * @covers Meek\Http\Message::getHeaders
     */
    public function testDefaultsToNoHeaders()
    {
        $this->assertEmpty($this->message->getHeaders());
    }

    /**
     * @covers Meek\Http\Message::hasHeader
     * @dataProvider differentCases
     */
    public function testHeaderExists($name)
    {
        $message = $this->message->withHeader('Foo', 'bar');

        $this->assertTrue($message->hasHeader($name));
    }

    /**
     * @covers Meek\Http\Message::hasHeader
     * @dataProvider differentCases
     */
    public function testHeaderDoesNotExist($name)
    {
        $this->assertFalse($this->message->hasHeader($name));
    }

    /**
     * @covers Meek\Http\Message::getHeader
     * @dataProvider differentCases
     */
    public function testRetrievingANonExistantHeaderReturnsAnEmptyArray($name)
    {
        $value = $this->message->getHeader($name);

        $this->assertInternalType('array', $value);
        $this->assertEmpty($value);
    }

    /**
     * @covers Meek\Http\Message::getHeader
     * @dataProvider differentCases
     */
    public function testRetrievingHeaderReturnsCorrectValue($name)
    {
        $message = $this->message->withHeader('Foo', 'bar');
        $value = $message->getHeader($name);

        $this->assertInternalType('array', $value);
        $this->assertCount(1, $value);
        $this->assertEquals('bar', $value[0]);
    }

    /**
     * @covers Meek\Http\Message::getHeader
     * @dataProvider differentCases
     */
    public function testRetrievingHeaderReturnsCorrectValues($name)
    {
        $message = $this->message
            ->withHeader('Foo', 'bar')
            ->withAddedHeader('Foo', 'baz');
        $value = $message->getHeader($name);

        $this->assertCount(2, $value);
        $this->assertEquals('bar', $value[0]);
        $this->assertEquals('baz', $value[1]);
    }

    /**
     * @covers Meek\Http\Message::getHeaderLine
     * @dataProvider differentCases
     */
    public function testBuildsAnEmptyValueIfHeaderDoesNotExist($name)
    {
        $value = $this->message->getHeaderLine($name);

        $this->assertInternalType('string', $value);
        $this->assertEmpty($value);
    }

    /**
     * @covers Meek\Http\Message::getHeaderLine
     * @dataProvider differentCases
     */
    public function testBuildsValueIfOnlyOneValue($name)
    {
        $message = $this->message->withHeader('Foo', 'bar');
        $value = $message->getHeaderLine($name);

        $this->assertInternalType('string', $value);
        $this->assertEquals('bar', $value);
    }

    /**
     * @covers Meek\Http\Message::getHeaderLine
     * @dataProvider differentCases
     */
    public function testBuildsCommaSeparatedListIfMoreThanOneValue($name)
    {
        $message = $this->message
            ->withHeader('Foo', 'bar')
            ->withAddedHeader('Foo', 'baz');
        $value = $message->getHeaderLine($name);

        $this->assertEquals('bar, baz', $value);
    }

    /**
     * @covers Meek\Http\Message::withHeader
     * @dataProvider invalidDataTypes
     * @expectedException InvalidArgumentException
     */
    public function testThowsErrorWhenChangingHeaderWithInvalidType($invalidType)
    {
        $message = $this->message->withHeader('foo', $invalidType);
    }

    /**
     * @covers Meek\Http\Message::withHeader
     */
    public function testAddingANewHeaderKeepsTheMessageImmutable()
    {
        $message = $this->message->withHeader('Foo', 'bar');

        $this->assertNotSame($this->message, $message);
        $this->assertEquals('bar', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withHeader
     */
    public function testAddingtoAnExistingHeaderKeepsTheMessageImmutable()
    {
        $message = $this->message
            ->withHeader('Foo', 'bar')
            ->withAddedHeader('Foo', 'baz');

        $this->assertNotSame($this->message, $message);
        $this->assertEquals('bar, baz', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withHeader
     */
    public function testChangingAnExistingHeaderChangesTheOriginalHeaderName()
    {
        $message = $this->message
            ->withHeader('Foo', 'bar')
            ->withHeader('FOO', 'baz');
        $headers = $message->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('FOO', $headers);
    }

    /**
     * @covers Meek\Http\Message::withAddedHeader
     * @dataProvider invalidDataTypes
     * @expectedException InvalidArgumentException
     */
    public function testThrowsErrorWhenAddingHeadersWithInvalidTypes($invalidType)
    {
        $message = $this->message->withAddedHeader('foo', $invalidType);
    }

    /**
     * @covers Meek\Http\Message::withAddedHeader
     */
    public function testAddingANewHeaderKeepsTheMessageImmutableWithAddedHeader()
    {
        $message = $this->message->withAddedHeader('Foo', 'bar');

        $this->assertNotSame($this->message, $message);
        $this->assertEquals('bar', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withHeader
     */
    public function testAddingtoAnExistingHeaderKeepsTheMessageImmutableWithAddedHeader()
    {
        $message = $this->message
            ->withAddedHeader('Foo', 'bar')
            ->withAddedHeader('Foo', 'baz');

        $this->assertNotSame($this->message, $message);
        $this->assertEquals('bar, baz', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withHeader
     */
    public function testCanAddMultipleValues()
    {
        $message = $this->message->withHeader('Foo', ['bar', 'baz']);

        $this->assertEquals('bar, baz', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withAddedHeader
     */
    public function testDoesNotModifyOriginalHeaderName()
    {
        $message = $this->message
            ->withHeader('Foo', 'bar')
            ->withAddedHeader('FOO', 'baz');
        $headers = $message->getHeaders();

        $this->assertArrayHasKey('Foo', $headers);
    }

    /**
     * @covers Meek\Http\Message::withAddedHeader
     */
    public function testCanAddMultipleValuesWithHeader()
    {
        $message = $this->message
            ->withHeader('Foo', 'test')
            ->withAddedHeader('Foo', ['bar', 'baz']);

        $this->assertEquals('test, bar, baz', $message->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::withoutHeader
     */
    public function testMessageIsImmutableIfNoHeadersWereRemoved()
    {
        $message = $this->message->withoutHeader('Foo');

        $this->assertNotSame($this->message, $message);
    }

    /**
     * @covers Meek\Http\Message::withoutHeader
     * @dataProvider differentCases
     */
    public function testRemovingAHeaderKeepsTheMessageImmutable()
    {
        $message1 = $this->message->withHeader('Foo', 'bar');
        $message2 = $message1->withoutHeader('Foo');

        $this->assertNotSame($message1, $message2);
        $this->assertEmpty($message2->getHeaderLine('foo'));
    }

    /**
     * @covers Meek\Http\Message::setHeaders
     */
    public function testSettingHeadersCanBeMuttable()
    {
        $headers = [
            'Host' => 'www.example.com',
            'Cache-Control' => ['no-cache', 'private']
        ];

        $setHeaders = new \ReflectionMethod($this->message, 'setHeaders');
        $setHeaders->setAccessible(true);
        $setHeaders->invoke($this->message, $headers);

        $headers['Host'] = ['www.example.com'];
        $this->assertEquals($headers, $this->message->getHeaders());
    }

    public function invalidProtocolVersions()
    {
        return [
            'an empty string' => [''],
            'a boolean data type' => [true],
            'an array data type' => [[]],
            'an object' => [new \stdClass],
            'a float data type' => [1.1],
            'an integer data type' => [2],
            'an alpabetic string' => ['hello']
        ];
    }

    public function invalidDataTypes()
    {
        return [
            'a boolean' => [true],
            'an object' => [new \stdClass],
            'a float data type' => [3.14159],
            'an integer' => [4],
            'an array of booleans' => [[true]],
            'an array of arrays' => [[[]]],
            'an array of objects' => [[new \stdClass]],
            'an array of floats' => [[3.14159]],
            'an array of integers' => [[4]]
        ];
    }

    public function differentCases()
    {
        return [
            'lowercase' => ['foo'],
            'uppercase' => ['FOO'],
            'mixedcase' => ['FoO']
        ];
    }
}
