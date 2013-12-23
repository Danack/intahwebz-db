<?php



namespace Intahwebz\TableMap;

class SQLOffsetFragment extends SQLFragment{

    var $offset;

    function __construct($offset) {
        $this->offset = $offset;
    }
}

