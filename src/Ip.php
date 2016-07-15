<?php
namespace ipip;

use InvalidArgumentException;
use RuntimeException;

class Ip
{
    /**
     * @var string
     */
    private $dbfile;
    /**
     * @var bool
     */
    private $initialized;
    /**
     * @var string ip record index
     */
    private $index;
    /**
     * @var string ip location records
     */
    private $locations;
    /**
     * @var array ip offset index
     */
    private $ipOffsets;
    /**
     * @var int max offset of ip record index
     */
    private $maxOffset;
    /**
     * @var array ip location cache
     */
    private $cache;

    /**
     * @var static singleton instance
     */
    private static $INSTANCE;
    /**
     * @var array empty record
     */
    private static $EMPTY = ["","","",""];
    
    public function __construct($dbfile)
    {
        $this->dbfile = $dbfile;
    }

    public static function getInstance()
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new self(__DIR__.'/17monipdb.dat');
        }
        return self::$INSTANCE;
    }

    public function getAllIp()
    {
        if (!$this->initialized) {
            $this->init();
        }
        $pos = 0;
        while ($pos < $this->maxOffset) {
            $ip = unpack('N', substr($this->index, $pos*8, 4));
            yield long2ip($ip[1]);
            $pos++;
        }
    }

    public function find($ip)
    {
        if (!preg_match('/^(\d+\.){3}\d+$/', $ip)) {
            throw new InvalidArgumentException("ip '$ip' is invalid");
        }
        if (!$this->initialized) {
            $this->init();
        }
        $iplong = pack('N', ip2long($ip));
        if (!isset($this->cache[$iplong])) {
            $parts = explode('.', $ip);
            $first = intval($parts[0]);
            if ($first > 255) {
                throw new InvalidArgumentException("ip '$ip' is invalid");
            }
            $start = $this->getIpOffset($first);
            $end = $first==255 ? $this->maxOffset : $this->getIpOffset($first+1);
            $pos = $this->binarySearch($iplong, $start, $end) * 8;
            $offset = unpack('V', substr($this->index, $pos+4, 3)."\x0");
            if ($offset[1] === 0) {
                $this->cache[$iplong] = false;
            } else {
                $len = unpack('C', substr($this->index, $pos+7, 1));
                $this->cache[$iplong] = [$offset[1], $len[1]];
            }
        }
        return $this->getLocation($this->cache[$iplong]);
    }

    private function init()
    {
        $binary = file_get_contents($this->dbfile);
        if ($binary === false) {
            throw new InvalidArgumentException("cannot read ip database file '{$this->dbfile}'");
        }
        $offset = unpack('N', substr($binary, 0, 4));
        $this->ipOffsets = unpack('V*', substr($binary, 4, 1024));
        $this->index = substr($binary, 1028, $offset[1] - 1020);
        $this->locations = substr($binary, $offset[1]-1024);
        $this->maxOffset = $this->getMaxOffset();
        $this->initialized = true;
    }

    /**
     * the max offset cannot infer from file format
     */
    private function getMaxOffset()
    {
        $offset = $this->ipOffsets[256];
        $max = strlen($this->index)/8;
        $ip = $this->getIp($offset);
        $offset++;
        for (; $offset < $max; $offset++) {
            $next = $this->getIp($offset);
            if ($next < $ip) {
                break;
            }
            $ip = $next;
        }
        return $offset;
    }

    private function getIpOffset($index)
    {
        return $this->ipOffsets[$index+1];
    }

    private function getLocation($record)
    {
        if ($record === false) {
            return self::$EMPTY;
        }
        list($offset, $len) = $record;
        return explode("\t", substr($this->locations, $offset, $len));
    }

    private function getIp($offset)
    {
        return substr($this->index, $offset*8, 4);
    }

    private function binarySearch($ip, $start, $end)
    {
        while ($start < $end) {
            $mid = ceil(($start + $end)/2);
            if ($mid == $end) {
                return $this->getIp($start) >= $ip ? $start : $end;
            } else {
                $midIp = $this->getIp($mid);
                if ($midIp == $ip) {
                    return $mid;
                } elseif ($midIp < $ip) {
                    $start = $mid;
                } else {
                    $end = $mid;
                }
            }
        }
        return $start;
    }
}