<?php


namespace Intahwebz\TableMap;


class SQLSearchFragment extends SQLFragment {

    /**
     * @var QueriedTable
     */
    var $tableMap;
    /**
     * @var QueriedTable
     */
    var $searchTableMap;
    var $column;
    var $searchTerm;

    function __construct(QueriedTable $tableMap, QueriedTable $searchTableMap, $column, $searchTerm) {

        parent::__construct('search');

        $this->tableMap         = $tableMap;
        $this->searchTableMap   = $searchTableMap;
        $this->column           = $column;
        $this->searchTerm       = $searchTerm;
    }
}

