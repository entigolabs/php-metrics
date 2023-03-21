<?php
require __DIR__ . '/../vendor/autoload.php';

use Prometheus\CollectorRegistry;

$adapter = new Prometheus\Storage\APC();
$registry = new CollectorRegistry($adapter);
$increment_by = 1;

function pingDomain($domain){
    $starttime = microtime(true);
    $file      = fsockopen($domain, 443, $errno, $errstr, 5);
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

function getHTTPCode($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpcode;
}

$color = $_SERVER['REQUEST_URI'];
$color = substr($color, 1);

if ($color == 'red')
    $site_to_ping = pingDomain("appshell.qa.fleetcomplete.dev") &&
    $site_http_code = getHTTPCode("appshell.qa.fleetcomplete.dev");
elseif ($color == 'green')
    $site_to_ping = pingDomain("www.google.com") &&
    $site_http_code = getHTTPCode("www.google.com");
elseif ($color == 'blue')
    $site_to_ping = pingDomain("php-metrics-test.vpn-qa.fleetcomplete.dev") &&
    $site_http_code = getHTTPCode("php-metrics-test.vpn-qa.fleetcomplete.dev");

//***********************************************************
//** count how long the response time was on each endpoint **
//***********************************************************
$total_time = $site_to_ping;
$histogram = $registry->RegisterHistogram('test', 'response_time_histogram', 'it observes', ['type'], [0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4]);
$histogram->observe($total_time, [$color]);

//*******************************************************************************
//** count all the different http response codes each of the endpoints receive **
//*******************************************************************************
if ($color == 'red')
    $counter = $registry->registerCounter('test', 'http_response_counter_red', 'it increases', ['type']);
elseif($color == 'green')
    $counter = $registry->registerCounter('test', 'http_response_counter_green', 'it increases', ['type']);
elseif($color == 'blue')
    $counter = $registry->registerCounter('test', 'http_response_counter_blue', 'it increases', ['type']);
$counter->incBy($increment_by, [$site_http_code]);

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
