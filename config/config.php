<?php

$debug = false;

if($debug){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
ini_set('max_execution_time', 0);

ini_set('error_log',__DIR__."/../log/error.log");