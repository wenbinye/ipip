<?php
namespace ipip;

require(__DIR__.'/../vendor/autoload.php');

error_log(date('c') . " start");
$finder = Ip::getInstance();
$allIp = iterator_to_array($finder->getAllIp());
error_log(date('c') . " load all ip");
$start = microtime(true);
foreach ($allIp as $ip) {
    $finder->find($ip);
}
$time = microtime(true) - $start;

error_log(sprintf(
    '%s find %d ip records in %.2f senconds, memory usage %4.2fMB',
    date('c'), count($allIp), $time, memory_get_peak_usage(true)/1048576
));
