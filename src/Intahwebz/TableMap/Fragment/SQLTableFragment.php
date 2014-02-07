<?php

namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;

class SQLTableFragment extends SQLFragment {

    /**
     * @var QueriedTable
     */
    var $tableMap;
    
    /**
     * @var QueriedTable
     */
    //TODO - this is always null - probably code refactor available.
    //Or is it to allow a table to be joined to something other than the immediately
    //previous table.
    var $joinTableMap = null;

    function __construct(QueriedTable $tableMap, QueriedTable $joinTableMap = null) {
        parent::__construct('table');
        $this->tableMap = $tableMap;
        $this->joinTableMap = $joinTableMap;
    }
}
