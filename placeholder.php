<?php

//This file isn't used, it's just here to define PHP internal stuff that PHPStorm isn't aware of
//e.g. extensions that aren't installed by default.


define('YAML_UTF8_ENCODING', 'YAML_UTF8_ENCODING');
define('YAML_CRLN_BREAK', 'YAML_CRLN_BREAK');

function unused($marker) {
}




/**
 * @param string $input
 * @param int $pos
 * @param int $ndocs
 * @param array $callbacks
 * @return mixed
 */
function yaml_parse ($input, $pos = 0, &$ndocs= 0, $callbacks = array() ){}


/**
 * @param mixed $data
 * @param int $encoding
 * @param int $linebreak
 * @param array $callbacks
 * @return string
 */
define('YAML_ANY_ENCODING', 1);
define('YAML_ANY_BREAK', 2);

function yaml_emit ($data , $encoding = YAML_ANY_ENCODING , $linebreak = YAML_ANY_BREAK, $callbacks = array()){
    return "foo";
}