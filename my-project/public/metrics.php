<?php
require __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;

$adapter = new Prometheus\Storage\APC();
$registry = new CollectorRegistry($adapter);
$increment_by = 1;

//***********************************************************
//** count how long the response time was on each endpoint **
//***********************************************************
function pingDomain($domain){
    $starttime = microtime(true);
    $file      = fsockopen($domain, 80, $errno, $errstr, 10);
    $stoptime  = microtime(true);

    if (!$file){
        $status = -1;  // Site is down
    }
    else{
        fclose($file);
        $status = ($stoptime - $starttime) * 1000;
    }
    return $status;
}
//this $domain value should be the IP address of the laravel server
$total_time = pingDomain("www.google.com");
$color = $_SERVER['REQUEST_URI'];
$color = substr($color, 1);
$histogram = $registry->RegisterHistogram('test', 'response_time_histogram', 'it observes', ['type'], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]);
$histogram->observe($total_time, [$color]);

//*********************************************************************
//** count how many times the page received http_response_code '200' **
//*********************************************************************
$http_status = http_response_code();
$counter = $registry->registerCounter('test', 'http_response_counter', 'it increases', ['type']);
$counter->incBy($increment_by, [$http_status]);

//****************************************************
//** count how many times the page has been visited **
//****************************************************
$counter = $registry->registerCounter('test', 'color_counter', 'it increases', ['type']);
$counter->incBy($increment_by, [$color]);

//***************************************************
//** Count the one-time latency of the connnection **
//***************************************************

$gauge = $registry->registerGauge('test', 'latency_gauge', 'it sets', ['type']);
$gauge->set($total_time, [$color]);

?>
