<?php

namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;
use Intahwebz\TableMap\SQLQuery;

class AncestorFragment extends SQLFragment implements BindableParams {

    /**
     * @var QueriedTable
     */
    public $queriedTableMap;
    
    public $ancestorID;
    
    public $queriedClosureTable;

    protected $isDescendant = false;

    function __construct(QueriedTable $queriedTable, QueriedTable $queriedClosureTable, $ancestorID, $isDescendant = false) {
        $this->queriedTableMap = $queriedTable;
        $this->ancestorID = $ancestorID;
        $this->queriedClosureTable = $queriedClosureTable;
        $this->isDescendant = $isDescendant;
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
        if ($this->isDescendant == true) {
            $sqlQuery->addSQL("on (".$alias.".".$tableMap->getPrimaryColumn()." = $closureAlias.descendant)");
        }
        else {
            $sqlQuery->addSQL("on (".$alias.".".$tableMap->getPrimaryColumn()." = $closureAlias.ancestor)");
        }
    }



//if ($maxRelativeDepth != null) {
//$this->addSQL("and t.depth = ?");
//
//$statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
//$statementWrapper->bindParam('ii', $nodeID, $maxRelativeDepth);
//}
//else {
//    $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
//    $statementWrapper->bindParam('i', $nodeID);
//}


    /**
     * @param SQLQuery $sqlQuery
     */
    function whereBit(SQLQuery $sqlQuery) {
        $alias = $this->queriedClosureTable->getAlias();

        if ($this->isDescendant == true) {
            $sqlQuery->addSQL(" $alias.ancestor = ?");
        }
        else { 
            $sqlQuery->addSQL(" $alias.descendant = ?");
        }
    }    
}
