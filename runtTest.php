<?php

require_once('../config.php');
require_once('./test/functions.php');

$autoloader = require('./vendor/autoload.php');

$autoloader->add('Intahwebz', [realpath('./').'/test/']);

class Whatever {
    function __call($functionName, $params) {
        echo "called $functionName \n";
    }
}

class_alias("Whatever", "PHPUnit_Framework_TestCase");


xdebug_start_code_coverage();

try {

$test = new Intahwebz\TableMap\Tests\SQLTableMapTreesTest();

$test->setUpBeforeClass();

$test->setUp();
$test->testTreeSet();

}
catch (Exception $e) {
}

$coverage = xdebug_get_code_coverage();

var_dump($coverage);