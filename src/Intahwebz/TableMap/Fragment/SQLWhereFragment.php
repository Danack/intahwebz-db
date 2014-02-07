<?php

namespace Intahwebz\TableMap\Fragment;


class SQLWhereFragment extends SQLFragment{

    var $whereCondition;
    var $value;
    var $type;

    function __construct($whereCondition, $value, $type) {
        $this->whereCondition = $whereCondition;
        $this->value = $value;
        $this->type = $type;
    }
}

