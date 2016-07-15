<?php
namespace ipip;

use PHPUnit_Framework_TestCase;

class IpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testFind($ip, $record)
    {
        $this->assertEquals(
            $record,
            Ip::getInstance()->find($ip)
        );
    }

    public function dataProvider()
    {
        return [
            ['0.255.255.255', ['', '', '', '']],
            ['255.255.254.255', ['', '', '', '']],
            ['255.255.255.255', ['IPIP.NET', '2016060110', '', '']],
            [ '1.167.191.255', ['中国', '台湾', '新竹市', '']],
        ];
    }
}