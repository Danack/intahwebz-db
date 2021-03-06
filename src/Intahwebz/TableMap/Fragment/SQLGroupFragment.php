<?php



namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;

class SQLGroupFragment extends SQLFragment{

    /**
     * @var QueriedTable
     */
    var $tableMap;

    /**
     * @var string
     */
    var $column;

    function __construct(QueriedTable $tableMap, $column) {
        $this->tableMap = $tableMap;
        $this->column = $column;
    }
}

