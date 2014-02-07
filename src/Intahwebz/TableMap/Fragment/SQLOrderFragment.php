<?php


namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;

class SQLOrderFragment extends SQLFragment{

    var $tableMap;
    var $column;
    var $orderValue;

    function __construct($column, QueriedTable $tableMap = null, $orderValue= 'ASC'){
        $this->tableMap = $tableMap;
        $this->column = $column;
        $this->orderValue = $orderValue;
    }
}

