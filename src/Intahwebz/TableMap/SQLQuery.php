<?php

namespace Intahwebz\TableMap;

use Intahwebz\DB\Connection;
use Intahwebz\DB\DBException;

use Intahwebz\TableMap\Fragment\SQLTableFragment;
use Intahwebz\TableMap\Fragment\SQLGroupFragment;
use Intahwebz\Exception\UnsupportedOperationException;
use Intahwebz\TableMap\Fragment\SQLLimitFragment;
use Intahwebz\TableMap\Fragment\SQLOffsetFragment;
use Intahwebz\TableMap\Fragment\SQLSelectColumnFragment;
use Intahwebz\TableMap\Fragment\SQLNullFragment;
use Intahwebz\TableMap\Fragment\SQLOrderFragment;
use Intahwebz\TableMap\Fragment\SQLRandOrderFragment;
use Intahwebz\TableMap\Fragment\SQLWhereFragment;


$GLOBALS['objecting'] = false;

class SQLQuery extends AbstractQuery {

    use \Intahwebz\SafeAccess;

    var $commaString = "";

    /**
     * @var Connection
     */
    protected $dbConnection;

    protected $queryString;

    static $showSQL = false;
    static $showSQLAndExit = false;

    function __construct(Connection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @param TableMap $tableMap
     * @throws \InvalidArgumentException
     * @internal param $tableName
     * @return QueriedTable
     */
    function aliasTableMap(TableMap $tableMap) {
        /** @var $tableAlias SQLTableMap */
        $tableAlias = $this->getAliasForTable($tableMap);
        
        if (!($tableMap instanceof SQLTableMap)) {
            throw new \InvalidArgumentException("\$tableMap must be of type SQLTableMap");
        }
        
        return new QueriedSQLTable($tableMap, $tableAlias, $this);
    }


    /**
     * @param $tableMap QueriedTable
     */
    private function addColumns(QueriedTable $tableMap) {
        $columnDefinitions = $tableMap->getColumns();
        foreach($columnDefinitions as $columnDefinition){
            $this->addColumn($tableMap, $columnDefinition[0]);
        }
    }

    /**
     * @param QueriedTable $tableMap
     * @param $column
     */
    private function addColumn(QueriedTable $tableMap, $column) {
        $this->addColumnFromTableAlias($tableMap->getAlias(), $column);
    }


    /**
     * @param $tableAlias
     * @param $column
     */
    private function addColumnFromTableAlias($tableAlias, $column) {
        $this->queryString .= $this->commaString;
        $this->queryString .= " ".$tableAlias.".".$column;
        $this->commaString = ', ';
        $resultName = $tableAlias.'.'.$column;
        $this->columnsArray[] = &$this->data[$resultName];
    }

    /**
     * Reset the query to allow it to be used for afresh.
     * @TODO - this is a bad design. People should just be using a new query object.
     */
    private function reset() {
        $this->queryString = "select ";
        $this->commaString = "";
        $this->params = array();
        $this->paramsTypes = "";

        $this->data = array();
        $this->columnsArray = array();

        $this->tableNamesUsed = array();
        $this->aliasCount = 0;
    }

    /**
     * @param $string
     */
    private function addSQL($string) {
        $this->queryString .= " ";
        $this->queryString .= $string;
        $this->queryString .= " ";
        $this->queryString .= "\n";
    }

    /**
     * 
     */
    function delete() {
        $this->fetch(false, true);
    }

    /**
     * @param SQLWhereFragment $sqlFragment
     * @throws \Exception
     */
    private function bindParams(SQLWhereFragment $sqlFragment) {
        if($sqlFragment->value !== NULL){

            if(is_array($sqlFragment->value) == TRUE){

                if(mb_strlen($sqlFragment->type) != count($sqlFragment->value)){
                    throw new \Exception("Number of values ".count($sqlFragment->value)." does not match number of types passed in [".$sqlFragment->type."]");
                }

                foreach($sqlFragment->value as &$value){
                    $this->params[] = &$value;
                }
                $this->paramsTypes .= $sqlFragment->type;
            }
            else{
                $this->params[] = &$sqlFragment->value;
                $this->paramsTypes .= $sqlFragment->type;
            }
        }
    }

    /**
     * Find the join column between two tables, where the second table
     * has a foreign key to the first table
     * @TODO replace with the relation stuff.
     * 
     * @param QueriedTable $tableMap
     * @param QueriedTable $joinTableMap
     * @return bool|null
     */
    function getJoinColumn(QueriedTable $tableMap, QueriedTable $joinTableMap) {

        //Try and join on the primary column of the previous table
        $joinColumn = $joinTableMap->getPrimaryColumn();
        foreach ($tableMap->getColumns() as $column) {
            if ($column[0] == $joinColumn) {
                return $joinColumn;
            }
        }

        //Try and join on the primary column of the this table
        $joinColumn = $tableMap->getPrimaryColumn();
        foreach ($joinTableMap->getColumns() as $column) {
            if ($column[0] == $joinColumn) {
                return $joinColumn;
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    function count() {
        return $this->fetch(true);
    }

    /**
     * Inserts SQLTableFragment's to allow tables to be joined. The SQLTableFragment are
     * created either from the tables defined relations or an examination of their
     * columns.
     * This kind of gets repeated later when the join is actually done.
     * @throws \Exception
     */
    function addJoiningRelationTables() {

        $modifiedSQLFragments = [];

        $previousTableMap = null;
        $first = true;

        foreach ($this->sqlFragments as $sqlFragment) {

            if($sqlFragment instanceof SQLTableFragment){

                if ($first == true) {
                    goto endSQLFragment; //yolo
                }

                $joinTableMap = $sqlFragment->queriedJoinTableMap;

                if ($joinTableMap == null) {            //If we were not told explicitly which table t join to
                    $joinTableMap = $previousTableMap;  //try to join to the previous one.
                }

                //Try and find a column to join on automatically.
                $autoJoinColumn = $this->getJoinColumn($sqlFragment->queriedTableMap, $joinTableMap);

                if ($autoJoinColumn == null) {
                    //We failed to join automatically - lets try the proper relation stuff
                    $relatedTable = $this->findRelationTable($sqlFragment->queriedTableMap, $joinTableMap);

                    if ($relatedTable) {
                        $modifiedSQLFragments[] = $this->makeTableFragment($relatedTable);
                    }
                }

endSQLFragment:
                $previousTableMap = $sqlFragment->queriedTableMap;
            }

            $modifiedSQLFragments[] = $sqlFragment;
            $first = false;
        }

        $this->sqlFragments = $modifiedSQLFragments;
    }

    /**
     * @param QueriedTable $queriedTableMap
     * @param QueriedTable $joinTableMap
     * @throws \Exception
     * @return null
     */
    function findRelationTable(QueriedTable $queriedTableMap, QueriedTable $joinTableMap) {
        $joinTable = null;

        $relations = $queriedTableMap->getTableMap()->getRelations();
        $relations = array_merge($relations, $joinTableMap->getTableMap()->getRelations());

        /** @var $relations Relation[] */
        
        foreach($relations as $relation) {
            $owningType = $relation->getOwning();
            $inverseType = $relation->getInverse();
            
            if ($queriedTableMap->getTableMap() instanceof $owningType && 
                $joinTableMap->getTableMap() instanceof $inverseType) {
                $tableName = $relation->getTableName();
                return new $tableName();
            }
            if ($queriedTableMap->getTableMap() instanceof $inverseType &&
                $joinTableMap->getTableMap() instanceof $owningType) {
                $tableName = $relation->getTableName();
                return new $tableName();
            }
        }
        
//        class Intahwebz\TableMap\Tests\Table\emailuserprimaryEmailRelation#2019 (4) {
//    private $type =>_-_-_- ""  ""    
//    string(22) "ONE_TO_ONE_DIRECTIONAL"
//    private $owning =>
//    string(40) "Intahwebz\TableMap\Tests\Table\UserTable"
//    private $inverse =>
//    string(41) "Intahwebz\TableMap\Tests\Table\EmailTable"
//    private $tableName =>
//    string(49) "Intahwebz\TableMap\Tests\Table\EmailUserJoinTable"
//  }
        
        
        throw new \Exception("Could not find relation in ".var_export($relations, true));
        
        
//        if ($relation = $queriedTableMap->findRelationTable($joinTableMap)) {
//            $joinTable = $relation->getInverseTable();
//        }
//        else {
//            $relation = $joinTableMap->findRelationTable($queriedTableMap);
//            if ($relation != null) {
//                $joinTable = $relation->getInverseTable();
//            }
//        }
        
        //return $joinTable;
    }
    
    
//    function foo(QueriedTable $tableMap, QueriedTable $joinTableMap) {
//        $relationTable = null;
//        $joinColumn = $this->getJoinColumn($tableMap, $joinTableMap);
//
//        if ($joinColumn == null) {
//
//            $newFragment = null;
//            $relatedTables = $tableMap->getTableMap()->getRelatedTables();
//
//            foreach($relatedTables as $relatedTable) {
//                if ($relatedTable->getTableMap() == $joinTableMap->getTableMap()) {
//                    $relationTable = $relatedTable->getRelationshipTable($tableMap->getTableMap());
//                    $newFragment = $this->makeTableFragment($relationTable);
//                    goto done;
//                }
//            }
//
//            $relatedTables = $joinTableMap->getTableMap()->getRelatedTables();
//
//            foreach($relatedTables as $relatedTable) {
//                if ($relatedTable->getTableMap() == $tableMap->getTableMap()) {
//                    $relationTable = $relatedTable->getRelationshipTable($joinTableMap->getTableMap());
//                    $newFragment = $this->makeTableFragment($relationTable);
//                    goto done;
//                }
//            }
//done:
//            if($newFragment == null) {
//                throw new \Exception("Join will fail, as failed to find join fragment.");
//            }
//
//            $modifiedSQLFragments[] = $newFragment;
//        }
//
//}
    
    
/*
     
    
    //                        $relatedTables = $tableMap->getTableMap()->getRelatedTables();
//
//                        foreach($relatedTables as $relatedTable) {
//                            if ($relatedTable->getTableMap() == $joinTableMap->getTableMap()) {
//                                $relationTable = $relatedTable->getRelationshipTable($tableMap->getTableMap());
//                                $newFragment = $this->makeTableFragment($relationTable);
//                                goto done;
//                            }
//                        }
//
//                        $relatedTables = $joinTableMap->getTableMap()->getRelatedTables();
//
//                        foreach($relatedTables as $relatedTable) {
//                            if ($relatedTable->getTableMap() == $tableMap->getTableMap()) {
//                                $relationTable = $relatedTable->getRelationshipTable($joinTableMap->getTableMap());
//                                $newFragment = $this->makeTableFragment($relationTable);
//                                goto done;
//                            }
//                        }
//done:
//                        if($newFragment == null) {
//                            throw new \Exception("Join will fail, as failed to find join fragment.");
//                        }
    
    */


    /**
     * @param $className
     * @return array|null
     * @throws \Exception
     */
    function fetchSingle($className) {
        $results = $this->fetch();
        
        if (count($results) == 0) {
            return null;
        }
        if (count($results) == 1) {
            return castToObject($className, $results[0]);    
        }

        throw new \Exception("multiple rows found, when only one expected.");
    }

    /**
     * @param bool $fuckPHP
     * @return array|null
     */
    function fetchObjects($fuckPHP = false) {

        $contentArray = $this->fetch();

        //todo - this should just return an empty array
        if (count($contentArray) == false) {
            return null;
        }

        if (count($this->outputClassnames) == 1) {
            return castArraysToObjects($this->outputClassnames[0], $contentArray);
        }

        $compositeClassname = $this->generateCompositeObjectClassname();
        
        if ($fuckPHP == true) {
            var_dump($this->sqlFragments);
        }
        
        $compositeObjects = array();
        foreach($contentArray as $content) {
            $objects = array();
            foreach ($this->outputClassnames as $classname) {
                $objects[] = castToObject($classname, $content);
            }
            $compositeObjects[] = new $compositeClassname($objects[0], $objects[1]);
        }

        return $compositeObjects;
    }



    function generateCompositeObjectClassname() {

        // Note - the two function names for parsing classnames are:
        // getNamespace($namespaceClass)
        // getClassName($namespaceClass);
        
        $namespace = getNamespace($this->outputClassnames[0]);
        
        $classnames = $this->outputClassnames;
        sort($classnames);

        $classnames = array_map(
            function ($namespacedClassname) {
                return getClassName($namespacedClassname);
            },
            $classnames
        );

        return $namespace.'\\'.implode('X', $classnames);
    }
    
    
    /**
     * @param bool $doACount
     * @param bool $doADelete
     * @return array|int|null
     * @throws \Intahwebz\DB\DBException
     * @throws \Exception
     */
    function fetch($doACount = false, $doADelete = false){
        $this->reset();
        $whereString = ' where ';

        //Automatically add all columns from tables i.e. no specific columns
        //were quried.
        $autoAddColumns = TRUE; 

        $this->addJoiningRelationTables();

        if ($doADelete == true) {
            $this->queryString = "";
            $schema = null;

            foreach($this->sqlFragments as $sqlFragment) {
                if ($sqlFragment instanceof SQLTableFragment) {
                    if ($schema == null) {
                        $schema = $sqlFragment->queriedTableMap->getSchema();
                    }
                }
            }

            if ($schema == null) {
                throw new \Exception("Trying to do a delete, but table has no schema.");
            }

            $this->dbConnection->selectSchema($schema);

            $this->queryString .= "delete ";

            $whereCount = 0;
            $separator = '';
            foreach($this->sqlFragments as $sqlFragment){
                if ($sqlFragment instanceof SQLTableFragment) {
                    /** @var  $sqlFragment SQLTableFragment */
                    $tableMap = $sqlFragment->queriedTableMap;
                    $this->addSQL($separator.$tableMap->getAlias());
                    $separator = ", ";
                }

                if ($sqlFragment instanceof SQLWhereFragment) {
                    $whereCount += 1;
                }
            }
            if ($whereCount == 0) {
                throw new \Exception("Trying to do a delete with no where fragments, which is too dangerous.");
            }
        }
        else if ($doACount == true) {
            $this->addSQL("COUNT(*)");
        }
        else{
            foreach($this->sqlFragments as $sqlFragment) {
                if($sqlFragment instanceof SQLSelectColumnFragment){
                    /** @var $sqlFragment SQLSelectColumnFragment */
                    $this->addColumn($sqlFragment->tableMap, $sqlFragment->column);
                    $autoAddColumns = FALSE;
                }
            }

            foreach($this->sqlFragments as $sqlFragment) {
                if($autoAddColumns == TRUE){
                    if($sqlFragment instanceof SQLTableFragment){
                        /** @var $sqlFragment SQLTableFragment */
                        $this->addColumns($sqlFragment->queriedTableMap);
                    }
                }
                if($sqlFragment instanceof SQLGroupFragment){
                    /** @var $sqlFragment SQLGroupFragment */
                    $this->addSQL(", count(1) as ".$sqlFragment->tableMap->getAlias()."_".$sqlFragment->column."_count ");
                    $resultName = $sqlFragment->tableMap->getAlias().'.count';
                    $this->columnsArray[] = &$this->data[$resultName];
                }
            }
        }

        $this->addSQL(" from ");

        $previousTableMap = NULL;
        $tableMap = null;

        foreach($this->sqlFragments as $sqlFragment) {
            if($sqlFragment instanceof SQLTableFragment){
                /** @var  $sqlFragment SQLTableFragment */
                $tableMap = $sqlFragment->queriedTableMap;

                $joinTableMap = $sqlFragment->queriedJoinTableMap;

                if ($joinTableMap == null){
                    $joinTableMap = $previousTableMap;
                }

                if($joinTableMap != NULL){
                    $this->addSQL(" inner join ");
                    $this->addSQL($tableMap->getSchema().".".$tableMap->getTableName().' as '.$tableMap->getAlias());
                    $joinColumn = $this->getJoinColumn($tableMap, $joinTableMap);

                    if ($joinColumn == null) {
                        throw new \Exception("Could not figure out the join columns between ".$tableMap->getTableName()." and ".$joinTableMap->getTableName());
                    }

                    $this->addSQL(' on ('.$joinTableMap->getAlias().".".$joinColumn.' = '.$tableMap->getAlias().'.'.$joinColumn.") ");
                }
                else{
                    $this->addSQL($tableMap->getSchema().".".$tableMap->getTableName().' as '.$tableMap->getAlias());
                }
            }
            else if($sqlFragment instanceof SQLNullFragment){
                /** @var  $sqlFragment SQLNullFragment */
                $tableMap = $sqlFragment->tableMap;
                $nullTableMap = $sqlFragment->nullTableMap;
                $this->addSQL(" left outer join ");
                $this->addSQL($nullTableMap->getSchema().".".$nullTableMap->getTableName().' as '.$nullTableMap->getAlias());

                $aliasedJoinColumn = $tableMap->getAliasedPrimaryColumn();
                $joinColumnName = $tableMap->getPrimaryColumn();
                $this->addSQL(' on ('.$aliasedJoinColumn.' = '.$nullTableMap->getAlias().'.'.$joinColumnName);
                $columnValues = $sqlFragment->columnValues;
                foreach($columnValues as $column => $value){
                    $this->addSQL(" && ".$nullTableMap->getAlias().'.'."$column = '$value'");
                }

                $this->addSQL(" ) ");
            }

            if ($sqlFragment instanceof SQLRandOrderFragment) {
                //http://jan.kneschke.de/projects/mysql/order-by-rand/
                /** @var  $sqlFragment SQLRandOrderFragment */
                $tableMap = $sqlFragment->tableMap;
                $tableMap2 = $sqlFragment->tableMap2;

                $this->addSQL(" inner join  (SELECT (RAND() *
                             (SELECT MAX(".$tableMap->getPrimaryColumn().")
                        FROM ".$tableMap2->getSchema().".".$tableMap2->getTableName().")) as ".$tableMap->getPrimaryColumn()." )
                    AS ".$tableMap2->getAlias()."_rand");

                $this->addSQL( " where ".$tableMap->getAliasedPrimaryColumn()."  >= ".$tableMap2->getAlias()."_rand.".$tableMap2->getPrimaryColumn() );

                
            }

            $previousTableMap = $tableMap;
        }


        $andString = '';

        foreach($this->sqlFragments as $sqlFragment){

            if($sqlFragment instanceof SQLNullFragment){
                /** @var $nullTableMap QueriedTable  */
                $nullTableMap = $sqlFragment->nullTableMap;

                //Add the ID column
                $this->addSQL($whereString);
                $this->addSQL($andString.' '.$nullTableMap->getAliasedPrimaryColumn()." is null " );
                $andString = ' and';
                $whereString = '';

                //Add the actual columns with values
                foreach($sqlFragment->columnValues as $column => $value){
                    $this->addSQL($whereString);
                    //TODO - alias should be $nullTableMapAlias?
                    $this->addSQL($andString.' '.$nullTableMap->getAlias().'.'."$column is null " );
                    $andString = ' and';
                    $whereString = '';
                }
            }

            if($sqlFragment instanceof SQLWhereFragment){
                /** @var $sqlFragment SQLWhereFragment */
                $this->addSQL( $whereString.$andString);
                $this->addSQL(' '.$sqlFragment->whereCondition);
                $whereString = '';
                $andString = ' and';
                $this->bindParams($sqlFragment);
            }
        }

        $groupByString = " group by ";
        foreach($this->sqlFragments as $sqlFragment){
            if($sqlFragment instanceof SQLGroupFragment){
                /** @var $sqlFragment SQLGroupFragment */
                $sqlGroup = $groupByString.$sqlFragment->tableMap->getAlias().".".$sqlFragment->column;
                $this->addSQL($sqlGroup);
                $groupByString = "";
            }
        }

        $commaString = "";
        $orderByString = " order  by ";

        foreach($this->sqlFragments as $sqlFragment){
            if($sqlFragment instanceof SQLOrderFragment){
                /** @var $sqlFragment SQLOrderFragment */
                $this->addSQL($commaString);
                $this->addSQL($orderByString);

                if ($sqlFragment->tableMap == null){
                    // The 'column' may actually be a group by result, and so isn't part of a table
                    // or tableAlias
                    $this->addSQL($sqlFragment->column);
                }
                else{
                    $this->addSQL($sqlFragment->tableMap->getAlias().".".$sqlFragment->column);
                }

                $this->addSQL(" ".$sqlFragment->orderValue);

                $commaString = ", ";
                $orderByString = "";
            }
        }

        foreach($this->sqlFragments as $sqlFragment){
            if($sqlFragment instanceof SQLLimitFragment){
                /** @var $sqlFragment SQLLimitFragment */
                $this->addSQL(" limit ".$sqlFragment->limit);
            }

            if($sqlFragment instanceof SQLOffsetFragment){
                /** @var $sqlFragment SQLOffsetFragment */
                $this->addSQL(" offset ".$sqlFragment->offset);
            }
        }

        $this->queryString .= ';';

        if(self::$showSQL == TRUE){
            echo "Query is [<br/>";
            echo str_replace("\n", "<br/>\n", $this->queryString);
            echo "<br/>]\r\n";
        }

        if(self::$showSQLAndExit == true){
            echo "Query is [<br/>";
            echo str_replace("\n", "<br/>\n", $this->queryString);
            echo "<br/>]\r\n";

            var_dump($this->paramsTypes);
            var_dump($this->params);
            exit(0);
        }

        $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
        
        if(count($this->params) > 0){
            $bindParams = array();
            $bindParams[] = $this->paramsTypes;
            $bindParams = array_merge($bindParams, $this->params);
            call_user_func_array(array($statementWrapper->statement, 'bind_param'), $bindParams);
        }

        $result = $statementWrapper->execute();

        if (!$result) {
            throw new DBException("Error executing query :".$this->dbConnection->getLastError());
        }

        if ($doADelete == true) {
            return null;
        }
        else if ($doACount == true) {
            $count = 0;
            $statementWrapper->statement->bind_result($count);

            if ($statementWrapper->statement->fetch()) {
                $statementWrapper->close();
                return $count;
            }
            throw new \Exception("Failed to get count");
        }
        else{
            call_user_func_array(array($statementWrapper->statement, 'bind_result'), $this->data);

            $linksArray = array();

            $i = 0;

            while($statementWrapper->statement->fetch()){
                foreach($this->data as $key => $value){
                    $linksArray[$i][$key] = $value;
                }
                $i++;
            }

            $statementWrapper->close();
            return 	$linksArray;
        }
    }


    //This stays table map
    function insertIntoMappedTable(TableMap $tableMap, $data, $foreignKeys = array()) {

        $connection = $this->dbConnection;
        $typesString = "";

        //Todo - check foreign keys meet mapping requirement.

        if (is_object($data)) {
            $data = convertObjectToArray($data);
        }

        $parameters = array();

        $queryString = "insert into ".$tableMap->schema.".".$tableMap->tableName." ( ";

        $commaString = '';

        foreach($tableMap->columns as $column){
            if(isset($column['autoInc']) == TRUE && $column['autoInc']){
                //primary keys are never set on insert
            }
            else{
                $queryString .= $commaString;
                $queryString .= $column[0];

                $columnType = $tableMap->getDataTypeForColumn($column[0], $data);

                if($columnType !== FALSE){
                    if ($columnType == 'hash'){
                        $columnType = 's';
                    }

                    //TODO check columnType is allowed
                    if (in_array($columnType, ['i', 'd', 's', 'b']) == false) {
                        throw new \Exception("Column type [$columnType] is not a single letter - bug in TableMap code.");
                    }

                    $typesString .= $columnType;
                }

                $commaString = ', ';
            }
        }


        $parameters[0] = $typesString;

        $queryString .= ") values (";

        $commaString = '';

        //Pass column as reference, so that if $column['default'] is passed as
        //param to MySQLi, next loop doesn't modify it.
        foreach($tableMap->columns as &$column){

            if(isset($column['autoInc']) == TRUE && $column['autoInc']){
                //primary keys are never set on insert
            }
            else{

                if (isset($column['type']) == TRUE &&
                    $column['type'] == 'd' &&
                    isset($data[$column[0]]) == FALSE){
                    $queryString .= $commaString;
                    $queryString .= 'now() ';
                }
                else{
                    $queryString .= $commaString;
                    $queryString .= "? ";

                    if(array_key_exists($column[0], $data) == FALSE ||
                        $data[$column[0]] == null){
                        if(array_key_exists('default', $column) == TRUE){
                            $parameters[] = &$column['default'];
                        }
                        else{
                            throw new \BadFunctionCallException("Data not set for column [".$column[0]."] and it has no default.");
                        }
                    }
                    else{
                        if (isset($column['type']) == TRUE &&
                            $column['type'] == 'hash'){
                            $allegedPassword = $data[$column[0]];
                            $options = array('cost' => 11);
                            $hash = password_hash($allegedPassword, PASSWORD_BCRYPT, $options);
                            $data[$column[0]] = $hash;
                        }

                        $parameters[] = &$data[$column[0]];
                    }
                }
                $commaString = ', ';
            }
        }

        $queryString .= "); ";

        $statementWrapper = $connection->prepareStatement($queryString);

        if(mb_strlen($typesString) > 0){ //If we have parameters that need binding.
            call_user_func_array(array($statementWrapper->statement, 'bind_param'), $parameters);
        }

        $statementWrapper->execute();
        $insertID = $statementWrapper->statement->insert_id;
        $statementWrapper->close();


        if ($tableMap->isTreeLike() == true) {
            $this->insertIntoTreePaths($tableMap, $insertID, $data['parent']);
        }

        $foreignKeys[$tableMap->getPrimaryColumn()] = $insertID;

        $this->insertIntoRelationTables($foreignKeys, $tableMap);

     

        return $insertID;
    }

    function insertIntoRelationTables($foreignKeys, TableMap $tableMap) {

        $relations = $tableMap->getRelations();
        
        foreach ($relations as $relation) {
            $tableToInsert = $relation->getOwningJoinTable($tableMap);
            if ($tableToInsert) {
                $this->insertIntoMappedTable($tableToInsert, $foreignKeys);
            }
        }
    }
    
    /**
     * @param Connection $dbConnection
     * @throws \Intahwebz\Exception\UnsupportedOperationException
     */
    function deleteFromMappedTableCount(Connection $dbConnection) {
        unused($dbConnection);
        throw new UnsupportedOperationException("deleteFromMappedTableCount is not yet implemented.");
    }

    /**
     * @param TableMap $tableMap
     * @param $params
     * @throws \Exception
     */
    function updateMappedTable(TableMap $tableMap, $params) {

        $typesString = "";
        $parameters = array(
            '', // first element has types injected later
        );

        if(array_key_exists('where', $params) == FALSE){
            throw new \Exception("Where conditions not set, aborting table update.");
        }

        $queryString = "update ".$tableMap->schema.".".$tableMap->tableName." set ";
        $commaString = '';

        foreach($params['columns'] as $columnName => &$value){
            $queryString .= $commaString;
            $queryString .= ' '.$columnName.' = ? ';
            $commaString = ', ';

            $type = $tableMap->getDataTypeForColumn($columnName, $params['columns']);

            if($type !== FALSE){
                $typesString .= $type;
            }

            $parameters[] = &$value;
        }

        $queryString .= " where ";
        $andString = '';

        foreach($params['where'] as $columnName => &$value){

            $queryString .= $andString;
            $queryString .= ''.$columnName.' = ? ';

            $andString = ' and ';

            $type = $tableMap->getDataTypeForColumn($columnName, $params['where']);

            if($type !== FALSE){
                $typesString .= $type;
            }

            $parameters[] = &$value;
        }

        $connection = $this->dbConnection;

        $parameters[0] = $typesString;
        $statementWrapper = $connection->prepareStatement($queryString);

        call_user_func_array(array($statementWrapper->statement, 'bind_param'), $parameters);

        $statementWrapper->execute();

        $statementWrapper->close();
        $connection->close();
    }

    /**
     * @param TableMap $tableMap
     * @param $insertID
     * @param $parentID
     */
    function insertIntoTreePaths(TableMap $tableMap, $insertID, $parentID) {

        $treePathTablename = $tableMap->schema.'.'.$tableMap->tableName.'_TreePaths';

        //TODO - where the fuck does depth come from.
        $queryString = ' insert into '.$treePathTablename.' (ancestor, descendant, depth)
                values (?, ?, 0)';

        $connection = $this->dbConnection;
        $statementWrapper = $connection->prepareStatement($queryString);

        $statementWrapper->bindParam('ii', $insertID, $insertID);

        $statementWrapper->execute();
        $statementWrapper->close();

        $queryString = '     Insert into '.$treePathTablename.' (ancestor, descendant, depth)
            select ancestor, ?, (depth + 1) from '.$treePathTablename.'
            where descendant = ? and 
            ancestor != ?;';
        
        $statementWrapper = $connection->prepareStatement($queryString);
        $statementWrapper->bindParam('iii', $insertID, $parentID, $insertID);

        $statementWrapper->execute();
        $statementWrapper->close();
    }

    
    /**
     * Get ancestors of comment #6
     * @param TableMap $tableMap
     * @param $nodeID
     * @return array
     */
    function getAncestors(TableMap $tableMap, $nodeID) {
        $this->reset();

        $tableName = $tableMap->schema.".".$tableMap->tableName;

        foreach($tableMap->columns as $columnDefinition){
            $this->addColumnFromTableAlias($tableMap->tableName, $columnDefinition[0]);
        }

        $this->addSQL("from ".$tableName." ".$tableMap->tableName);
        $this->addSQL("join ".$tableName."_TreePaths t");
        $this->addSQL("on (".$tableMap->tableName.".".$tableMap->getPrimaryColumn()." = t.ancestor)");
        $this->addSQL("where t.descendant = ?");

        $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
        $statementWrapper->bindParam('i', $nodeID);

        $blah = [];
        $blahblah = [];

        //echo $this->queryString."<br/>";
        $statementWrapper->execute();
        $statementWrapper->statement->bind_result($blah['mockCommentID'], $blah['text'], $blah['parent']);

        while ($statementWrapper->statement->fetch()) {
            $really = array();
            $really['mockCommentID'] = $blah['mockCommentID'];
            $really['text'] = $blah['text'];
            $really['parent'] = $blah['parent'];

            //TODO fix this.
            $blahblah[] = $really;
        }

        $statementWrapper->close();

        return $blahblah;
    }

    /**
     * @param TableMap $tableMap
     * @param $nodeID
     * @param null $maxRelativeDepth
     * @return array
     */
    function getDescendants(TableMap $tableMap, $nodeID, $maxRelativeDepth = null) {
        $this->reset();

        $tableName = $tableMap->schema.".".$tableMap->tableName;

        foreach($tableMap->columns as $columnDefinition){
            $this->addColumnFromTableAlias($tableMap->tableName, $columnDefinition[0]);
        }

        $this->addSQL(", t.mockCommentTreePathID, t.depth ");
        
        $this->addSQL("from ".$tableName." ".$tableMap->tableName);
        $this->addSQL("join ".$tableName."_TreePaths t");
        $this->addSQL("on (".$tableMap->tableName.".".$tableMap->getPrimaryColumn()." = t.descendant)");
        $this->addSQL("where t.ancestor = ?");

        if ($maxRelativeDepth != null) {
            $this->addSQL("and t.depth = ?");

            $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
            $statementWrapper->bindParam('ii', $nodeID, $maxRelativeDepth);
        }
        else {
            $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
            $statementWrapper->bindParam('i', $nodeID);
        }

        //TODO - this is embarrasing.
        $blah = [];
        $blahblah = [];

        $statementWrapper->execute();
        $statementWrapper->statement->bind_result(
            //These are covered by foreach($tableMap->columns as $columnDefinition){
            $blah['mockCommentID'], 
            $blah['text'], 
            $blah['parent'],
            //These are from the tree map
            $blah['treeID'],
            $blah['depth']
        );

        while ($statementWrapper->statement->fetch()) {
            $really = array();
            $really['mockCommentID'] = $blah['mockCommentID'];
            $really['text'] = $blah['text'];
            $really['parent'] = $blah['parent'];
            
            $really['treeID'] = $blah['treeID'];
            $really['depth'] = $blah['depth'];

            //TODO fix this.
            $blahblah[] = $really;
        }

        $statementWrapper->close();

        return $blahblah;
    }

    /**
     * Deletes a node.
     * @param TableMap $tableMap
     * @param $nodeID
     */
    function deleteNode(TableMap $tableMap, $nodeID) {
        $this->reset();
        $this->queryString = "";

        $tableName = $tableMap->schema.".".$tableMap->tableName;
        $this->addSQL("delete from ".$tableName."_TreePaths where descendant = ?");
        //TODO - shouldn't this also have
        //delete FROM `mocks`.`mockComment_TreePaths` where ancestor = 4;
        //And also update depths?

        $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
        $statementWrapper->bindParam('i', $nodeID);

        $statementWrapper->execute();
        $statementWrapper->close();
    }

    /**
     * Deletes the descendants of a node.
     * @param TableMap $tableMap
     * @param $nodeID
     */
    function deleteDescendants(TableMap $tableMap, $nodeID) {

        $this->reset();
        $this->queryString = "";

        $tableName = $tableMap->schema.".".$tableMap->tableName;
        $this->addSQL("delete ".$tableName."_TreePaths from ".$tableName."_TreePaths
    join ".$tableName."_TreePaths a using (descendant)
    where a.ancestor = ?;");
                
        $statementWrapper = $this->dbConnection->prepareStatement($this->queryString);
        $statementWrapper->bindParam('i', $nodeID);

        $statementWrapper->execute();
        $statementWrapper->close();
    }
}

