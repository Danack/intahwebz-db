<?php



namespace Intahwebz\TableMap\Fragment;

class SQLOffsetFragment extends SQLFragment{

    var $offset;

    function __construct($offset) {
        $this->offset = $offset;
    }
}

