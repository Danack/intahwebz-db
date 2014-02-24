<?php



error_reporting(E_ALL);

require_once('../config.php');
require_once('./test/functions.php');

//Not require once because it's already been loaded by php unit.
$autoloader = require('./vendor/autoload.php');

$autoloader->add('Intahwebz', [realpath('./').'/test/']);





