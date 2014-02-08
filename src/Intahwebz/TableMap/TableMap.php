<?php

namespace Intahwebz\TableMap;

use Intahwebz\SafeAccess;


abstract class TableMap {

    use SafeAccess;

    var $schema;
    var $tableName;
    var $columns;

    var $indexColumns = array();

    /**
     * @var Relation[]
     */
    protected $relations = array();
    
    /**
     * @var RelatedTable[]
     */
    protected $relatedTables = [];

    //TODO - make a getter
    public $objectName = null;

    /**
     * @param $tableDefinition
     */
    private function __construct($tableDefinition) {
        //Must be implemented in class
    }

    /**
     * @return Relation[]
     */
    function getRelations() {
        return $this->relations;
    }
    
    function getObjectName() {
        return $this->objectName;
    }

    /**
     * @return string
     */
    function getClassName() {
        $className = get_class($this);

        $slashPosition = strrpos($className, '\\');

        if ($slashPosition !== false) {
            return substr($className, $slashPosition + 1);
        }

        return $className;
    }

    /**
     * @return string
     */
    function getDTONamespace() {
        return __NAMESPACE__."DTO";
    }

    /**
     * @return string
     */
    function getDTOClassname() {
        return ucfirst($this->getTableName())."DTO";
    }

    /**
     * @return bool
     */
    function isTreeLike() {
        return false;
    }

    /**
     * @return mixed
     */
    function getTableName() {
        return $this->tableName;
    }

    /**
     * @return \Intahwebz\TableMap\RelatedTable[]
     */
    function getRelatedTables() {
        return $this->relatedTables;
    }

    /**
     * @param $tableDefinition
     * @throws \UnexpectedValueException
     */
    function initTableDefinition($tableDefinition) {

        $requiredElements = array('tableName', 'columns', 'schema');
        
        foreach ($requiredElements as $requiredElement) {
            if (array_key_exists($requiredElement, $tableDefinition) == false) {
                throw new \UnexpectedValueException("Cannot initialise table without $requiredElement defined");
            }
        }

        if (array_key_exists('indexColumns', $tableDefinition) == true) {
            $this->indexColumns = $tableDefinition['indexColumns'];
        }

        $this->tableName = $tableDefinition['tableName'];
        $this->columns = $tableDefinition['columns'];
        $this->schema = $tableDefinition['schema'];
        

//        if (array_key_exists('relatedTables', $tableDefinition) == true) {
//            $this->initRelatedTables($tableDefinition['relatedTables']);
//        }
        if (array_key_exists('relations', $tableDefinition) == true) {
            $this->initRelations($tableDefinition['relations']);
        }
    }
    
    function initRelations($relations) {
        foreach ($relations as $relation) {
            $this->relations[] = new $relation();
        }
    }

//    /**
//     * @param $relatedTables
//     * @throws \Exception
//     */
//    function initRelatedTables($relatedTables) {
//
//        foreach ($relatedTables as $relatedTableInfo) {
//            $type = $relatedTableInfo[0];
//            $relatedTableName = $relatedTableInfo[1];
//            //$relatedTable = new $relatedTableName();
//            
//            if (isset($relatedTableInfo[2]) == false) {
//                throw new \Exception("Relation name not set.");
//            }
//
//            $relationName = $relatedTableInfo[2];
//            
//
//            $this->relatedTables[] = new RelatedTable($relatedTable, $type, $relationName);
//        }
//    }

    /**
     * @param $columnNameToFind
     * @param $arrayOrValue
     * @throws \Exception
     * @internal param $aliased
     * @internal param $columnName
     * @return bool|string
     */
    function getDataTypeForColumn($columnNameToFind, $arrayOrValue) {
        foreach($this->columns as $column){
            $columnNameToTest = $column[0];

            if(strcmp($columnNameToTest, $columnNameToFind) == 0){

                if (isset($column['primary']) && $column['primary']) {
                    //All primary keys are currently i.
                    return 'i';
                }
                //Found the column
                if(isset($column['type']) == true && $column['type'] == 'i'){
                    return 'i';
                }
                if(isset($column['type']) == true && $column['type'] == 'hash'){
                    return 'hash';
                }
                if(isset($column['type']) == true && $column['type'] == 'text'){
                    return 'text';
                }
                else if(isset($column['type']) == true &&
                    $column['type'] == 'd'){

                    if (is_scalar($arrayOrValue) == true){
                        return 's';
                    }
                    else if (isset($arrayOrValue[$column[0]]) == false){
                    //date types when not set default to NOW(), which doesn't add a parameter
                        return false;
                    }
                }
                else{
                    //Strings, hashes
                    return 's';
                }
            }
        }

        $columns = '['.var_export($this->columns).']';

        throw new \Exception("Failed to find columnName [$columnNameToFind] in tableMap: ".$this->schema.".".$this->tableName." Columns are: ".$columns);
    }


    /**
     * @return bool
     */
    function getPrimaryColumn() {
        foreach($this->columns as $tableColumn){
            if(array_key_exists('primary', $tableColumn) == true){
                if($tableColumn['primary'] == true){
                    return $tableColumn[0];
                }
            }
        }

        return false;
    }

    function findRelationTable(TableMap $joinTableMap, $relationName = null) {
        return null;
    }
}