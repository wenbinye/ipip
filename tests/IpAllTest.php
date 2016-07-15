<?php
namespace ipip;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use Zhuzhichao\IpLocationZh\Ip as ZzcIp;

class IpAllTest extends PHPUnit_Framework_TestCase
{
    public function testFind()
    {
        $ipfinder = Ip::getInstance();
        foreach ($ipfinder->getAllIp() as $ip) {
            $ret = ZzcIp::find($ip);
            if ($ret[0] === '保留地址') {
                $ret = ['','','',''];
            }
            $this->assertEquals($ret, $ipfinder->find($ip), "check $ip");
        }
    }
}