<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Utility;

use PHPUnit_Framework_TestCase;
use stdClass;

class JsonTest extends PHPUnit_Framework_TestCase
{
    public function testEncoding()
    {
        $input = new stdClass;
        $input->test1 = 'abcd1';
        $input->test2 = 'abcd2';

        $expected = <<<FORMATTED
{"test1":"abcd1","test2":"abcd2"}
FORMATTED;

        $json = new Json;
        $output = $json->encode($input);

        $this->assertSame($expected, $output);
    }

    public function testEncodingWithCustomEncodingOption()
    {
        $input = new stdClass;
        $input->test1 = 'abcd1';
        $input->test2 = 'abcd2';

        $expected = <<<FORMATTED
{
    "test1": "abcd1",
    "test2": "abcd2"
}
FORMATTED;

        $json = new Json;
        $json->setEncodingOptions(JSON_PRETTY_PRINT);
        $output = $json->encode($input);

        $this->assertSame($expected, $output);
    }

    public function testDecoding()
    {
        $input = <<<FORMATTED
{
    "test1": "abcd1",
    "test2": "abcd2"
}
FORMATTED;

        $expected = [
            'test1' => 'abcd1',
            'test2' => 'abcd2'
        ];

        $json = new Json;
        $json->setEncodingOptions(JSON_PRETTY_PRINT);
        $output = $json->decode($input);

        $this->assertSame($expected, $output);
    }

    public function testErrorWhileDecoding()
    {
        $input = <<<FORMATTED
{
    "test1"::"abcd1",
    "test2"::"abcd2"
}
FORMATTED;

        $expected = 'Invalid json (Syntax error)';

        $json = new Json;

        $output = $json->decode($input);
        $this->assertSame(null, $output);

        $this->assertSame($expected, $json($input));
    }
}
