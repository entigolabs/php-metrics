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
    $file      = fsockopen($domain, 80, $errno, $errstr, 5);
    $stoptime  = microtime(true);

    if (!$file){
        $status = -1;  // Site is down
    }
    else{
        fclose($file);
        $status = ($stoptime - $starttime) * 100;
    }
    return $status;
}
//this $domain value should be the IP address of the laravel server
$site_to_ping = pingDomain("https://appshell.qa.fleetcomplete.dev/p1/");
$total_time = $site_to_ping;
$color = $_SERVER['REQUEST_URI'];
$color = substr($color, 1);
$histogram = $registry->RegisterHistogram('test', 'response_time_histogram', 'it observes', ['type'], [0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4]);
$histogram->observe($total_time, [$color]);

//***********************************************************************
//** count all the different http response codes the endpoints receive **
//***********************************************************************
$http_status = http_response_code();
$counter = $registry->registerCounter('test', 'http_response_counter', 'it increases', ['type']);
$counter->incBy($increment_by, [$http_status]);

//****************************************************
//** count how many times the page has been visited **
//****************************************************
$counter = $registry->registerCounter('test', 'color_counter', 'it increases', ['type']);
$counter->incBy($increment_by, [$color]);

//***************************************************
//** Count the one-time latency of the connection **
//***************************************************

$gauge = $registry->registerGauge('test', 'latency_gauge', 'it sets', ['type']);
$gauge->set($total_time, [$color]);

?>
