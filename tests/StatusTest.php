<?php

namespace Meek\Http\Tests;

use PHPUnit_Framework_TestCase;
use Meek\Http\Status;

class StatusTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidStatusCodes
     * @expectedException InvalidArgumentException
     */
    public function testAgainstInvalidStatusCodes($code)
    {
        $status = new Status($code);
    }

    public function invalidStatusCodes()
    {
        return [
            'can not be 1 digit' => [5],
            'can not be 2 digits' => [15],
            'range is too high' => [600],
            'can not be a float' => [200.14159]
        ];
    }
}
