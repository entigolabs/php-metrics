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
    $file      = fsockopen($domain, 8000, $errno, $errstr, 10);
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
$total_time = pingDomain("192.168.0.17");
$color = $_SERVER['REQUEST_URI'];
$color = substr($color, 1);
$histogram = $registry->RegisterHistogram('test', 'response_histogram', 'it observes', ['type'], [1,2,3,4,5,6,7,8,9,10]);
$histogram->observe($total_time, [$color]);

//*********************************************************************
//** count how many times the page received http_response_code '200' **
//*********************************************************************
$http_status = http_response_code();
if (http_response_code() == 200) {
    $counter = $registry->registerCounter('test', '200_counter', 'it increases', ['type']);
    $counter->incBy($increment_by, ['200']);
}

//****************************************************
//** count how many times the page has been visited **
//****************************************************
$counter = $registry->registerCounter('test', 'red_counter', 'it increases', ['type']);
$counter->incBy($increment_by, [$color]);

?>
