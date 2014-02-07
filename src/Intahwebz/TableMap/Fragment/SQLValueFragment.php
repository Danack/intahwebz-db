<?php



namespace Intahwebz\TableMap\Fragment;

class SQLValueFragment extends SQLFragment {

    var $name;
    var $value;

    function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }
}
