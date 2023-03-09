<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require __DIR__ . '/../../../vendor/autoload.php';


class EndpointController extends Controller
{
    public function red()
    {
        include 'metrics.php';
    }

    public function green()
    {
        include 'metrics.php';
    }

    public function blue()
    {
        include 'metrics.php';
    }

    public function metrics()
    {
        include 'testFile.php';
    }
}
