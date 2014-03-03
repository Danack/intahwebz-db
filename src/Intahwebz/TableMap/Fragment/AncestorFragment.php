<?php

namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;
use Intahwebz\TableMap\SQLQuery;

class AncestorFragment extends SQLFragment implements BindableParams{

    /**
     * @var QueriedTable
     */
    public $queriedTableMap;
    
    public $ancestorID;
    
    public $queriedClosureTable;
    

    function __construct(QueriedTable $queriedTable, QueriedTable $queriedClosureTable, $ancestorID) {
        parent::__construct('table');
        $this->queriedTableMap = $queriedTable;
        $this->ancestorID = $ancestorID;

        $this->queriedClosureTable = $queriedClosureTable;
    }

    function &getValue() {
        return $this->ancestorID;
    }

    function getType() {
        return 'i';
    }

    /**
     * @return \Intahwebz\TableMap\QueriedTable
     */
    public function getQueriedTableMap() {
        return $this->queriedTableMap;
    }

    /**
     * @param SQLQuery $sqlQuery
     */
    function joinBit(SQLQuery $sqlQuery) {
        $closureTable = $this->queriedClosureTable;
        $alias = $this->queriedClosureTable->getAlias();
        $sqlQuery->addSQL("join ".$closureTable->getSchema().".".$closureTable->getTableName()." as $alias");
    }

    /**
     * @param SQLQuery $sqlQuery
     */
    function onBit(SQLQuery $sqlQuery) {
        $tableMap = $this->queriedTableMap->getTableMap();
        $alias = $this->queriedTableMap->getAlias();
        $closureAlias = $this->queriedClosureTable->getAlias();
        $sqlQuery->addSQL("on (".$alias.".".$tableMap->getPrimaryColumn()." = $closureAlias.ancestor)");
    }

    /**
     * @param SQLQuery $sqlQuery
     */
    function whereBit(SQLQuery $sqlQuery) {
        $alias = $this->queriedClosureTable->getAlias();
        $sqlQuery->addSQL(" $alias.descendant = ?");
    }    
}
