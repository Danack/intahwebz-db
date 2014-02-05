<?php

namespace Intahwebz\TableMap;


abstract class AbstractQuery {

    /**
     * @var SQLFragment[]
     */
    var  $sqlFragments = array();

    /**
     * @var array List of the names of the table names or aliases already used, so that if a table
     * is used multiple times in a query, the subsequent uses will use different alias.
     */

    protected $tableNamesUsed = array();

    /** @var int Number of aliases used so we can throw an exception if we run out. */
    protected $aliasCount = 0;

    //private $queryString;

    /**
     * @var array
     */
    var  $params = array();

    var  $paramsTypes = "";

    /**
     * @var array Stores the data returned from a query before being returned.
     */
    var  $data = array();

    //This binds the result
    var	$columnsArray = array();


    /** @var array */
    protected $outputClass = array();
    
    /**
     * @param TableMap $tableMap
     * @param \Intahwebz\TableMap\QueriedTable $joinTableMap
     * @return QueriedSQLTable
     */
    function table(TableMap $tableMap, QueriedTable $joinTableMap = null) {

        $newFragment = $this->makeTableFragment($tableMap, $joinTableMap);
        $this->sqlFragments[] = $newFragment;

        return $newFragment->tableMap;
    }

    function tableObject(TableMap $tableMap, QueriedTable $joinTableMap = null) {

        $this->addOutputClass($tableMap->getDTONamespace(), $tableMap->getDTOClassname());
        
        
        
        $newFragment = $this->makeTableFragment($tableMap, $joinTableMap);
        $this->sqlFragments[] = $newFragment;

        return $newFragment->tableMap;
    }
    
    
    /**
     * @param TableMap $tableMap
     * @param QueriedTable $joinTableMap
     * @return SQLTableFragment
     */
    function makeTableFragment(TableMap $tableMap, QueriedTable $joinTableMap = null) {
        $queriedTable = $this->aliasTableMap($tableMap);
        $newFragment = new SQLTableFragment($queriedTable, $joinTableMap);

        return $newFragment;
    }


    public function select(QueriedTable $tableMap, $column){
        $newFragment = new SQLSelectColumnFragment($tableMap, $column);
        $this->sqlFragments[] = $newFragment;
    }

    /**
     * @param TableMap $tableMap
     * @internal param $alias
     * @return QueriedTable
     */
    abstract function aliasTableMap(TableMap $tableMap);


    function getAliasForTable(TableMap $tableMap) {
        if(in_array($tableMap->tableName, $this->tableNamesUsed) == FALSE){
            $this->tableNamesUsed[] = $tableMap->tableName;
            return $tableMap->tableName;
        }

        $tableAliases = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',  );

        if($this->aliasCount >= 0 && $this->aliasCount < count($tableAliases)){
            $this->aliasCount++;
        }
        
        $alias = $tableAliases[$this->aliasCount];
        $this->tableNamesUsed[] = $alias;

        return $alias;
    }

//    /**
//     * @param TableMap $tableMap
//     * @return QueriedSQLTable
//     * @throws \Exception
//     */
//    function aliasTableMap(TableMap $tableMap) {
//
//        if(in_array($tableMap->tableName, $this->tableNamesUsed) == FALSE){
//            $this->tableNamesUsed[] = $tableMap->tableName;
//
//            return $this->aliasTableFromQuery($tableMap, $tableMap->tableName);
//
////            if ($tableMap instanceof YAMLTableMap) {
////                return new QueriedYAMLTable($tableMap, $tableMap->tableName, $this);
////            }
////            else if ($tableMap instanceof SQLTableMap) {
////                return new QueriedSQLTable($tableMap, $tableMap->tableName, $this);
////            }
////            else {
////                throw new \RuntimeException("Unknown table type ".get_class($tableMap));
////            }
//        }
//
//        $tableAliases = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',  );
//
//        if($this->aliasCount >= 0 && $this->aliasCount < count($tableAliases)){
//            $this->aliasCount++;
//
//            //return new QueriedSQLTable($tableMap, $tableAliases[$this->aliasCount], $this);
//
//            return $this->aliasTableFromQuery($tableMap, $tableAliases[$this->aliasCount]);
//
////            if ($tableMap instanceof YAMLTableMap) {
////                return new QueriedYAMLTable($tableMap, $tableAliases[$this->aliasCount], $this);
////            }
////            else if ($tableMap instanceof SQLTableMap) {
////                return new QueriedSQLTable($tableMap, $tableAliases[$this->aliasCount], $this);
////            }
////            else {
////                throw new \RuntimeException("Unknown table type ".get_class($tableMap));
////            }
//        }
//
//        throw new \Exception("Out of aliases");
//    }


    /**
     * Adds a WHERE fragment to a query.
     *
     * @param $condition
     * @param null $value
     * @param null $type
     * @throws \Exception
     */
    function where($condition, $value = NULL, $type = NULL) {
        if($type === NULL){
            if($value !== NULL){
                throw new \BadFunctionCallException("Value is set for where fragment. You must also set type - currently not set.");
            }
        }

        $this->sqlFragments[] = new SQLWhereFragment($condition, $value, $type);
    }

    /**
     * Adds a GROUP BY fragment to a query.
     *
     * @param QueriedTable $table
     * @param $column
     * @return string
     */
    function group(QueriedTable $table, $column) {
        $this->sqlFragments[] = new SQLGroupFragment($table, $column);

        return $table->getAlias()."_".$column."_count";
    }

    function order($tableMap, $column, $orderValue = 'ASC') {
        $this->sqlFragments[] = new SQLOrderFragment($column, $tableMap, $orderValue);
    }

    function rand(QueriedTable $table, QueriedTable $table2) {
        $this->sqlFragments[] = new SQLRandOrderFragment($table, $table2);
    }


    /**
     * Adds a limit fragment to a query.
     * @param $limit
     */
    function limit($limit) {
        $this->sqlFragments[] = new SQLLimitFragment($limit);
    }

    /**
     * Adds an offset fragment to a query.
     * @param $offset
     * @throws \RuntimeException
     */
    function offset($offset) {

        $limitFragmentFound = false;
        
        foreach ($this->sqlFragments as $sqlFragment) {
            if ($sqlFragment instanceof SQLLimitFragment) {
                $limitFragmentFound = true;
            }
        }
        
        if ($limitFragmentFound == false) {
            throw new \RuntimeException("Cannot add offset without a limit.");
        }
        
        $this->sqlFragments[] = new SQLOffsetFragment($offset);
    }

    /**
     * Adds a left outer join fragment to a query.
     *
     * TODO - rename this to leftOuter or similar.
     *
     * @param \Intahwebz\TableMap\QueriedTable|\Intahwebz\TableMap\TableMap $joinTableMap
     * @param TableMap $nullTableMap
     * @param array $columnValues
     * @return \Intahwebz\TableMap\QueriedSQLTable
     * @internal param $nullTable
     */
    //TODO this should be $queriedTable $queriedTable
    function nullTable(QueriedTable $joinTableMap, TableMap $nullTableMap, $columnValues = array()) {

        $queriedTable = $this->aliasTableMap($nullTableMap);

        $newFragment = new SQLNullFragment(
            $joinTableMap,
            $queriedTable,
            $queriedTable->alias,
            $columnValues
        );

        $this->sqlFragments[] = $newFragment;

        return $queriedTable;
    }

//    //This is doing a join and search in one...
//    function searchAlready(QueriedTable $firstTable, TableMap $secondTable, $column, $searchTerm) {
//
//        //TODO - why isn't this cloning the table map?
//        $queriedSecondTable = $this->aliasTableMap($secondTable);
//
//        $newFragment = new SQLSearchFragment(
//            $firstTable,
//            $queriedSecondTable,
//            $column,
//            $searchTerm
//        );
//
//        $this->sqlFragments[] = $newFragment;
//
//        return $queriedSecondTable;
//    }

//    //TODO - this isn't used?
//    function search(
//        QueriedTable $firstTable,
//        QueriedTable $secondTable,
//        $column,
//        $searchTerm) {
//
//        $newFragment = new SQLSearchFragment(
//            $firstTable,
//            $secondTable,
//            $column,
//            $searchTerm
//        );
//
//        $this->sqlFragments[] = $newFragment;
//    }

    function setValue($name, $value){
        $newFragment = new SQLValueFragment($name, $value);
        $this->sqlFragments[] = $newFragment;
    }

    abstract function count();
    abstract function delete();
    abstract function fetch();

    abstract function fetchObjects();


    function addOutputClass($objectNamespace, $objectClassname) {
        $this->outputClass[] = $objectNamespace.'\\'.$objectClassname;
    }
    

}


