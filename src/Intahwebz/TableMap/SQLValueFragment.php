<?php



namespace Intahwebz\TableMap;

class SQLValueFragment extends SQLFragment {

    var $name;
    var $value;

    function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }
}
