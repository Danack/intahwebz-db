<?php

namespace Intahwebz\TableMap;

use Intahwebz\SafeAccess;


abstract class TableMap {

    use SafeAccess;

    var $schema = 'basereality';
    var $tableName;
    var $columns;

    var $indexColumns = array();

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
     */
    function initTableDefinition($tableDefinition) {

        $this->tableName = $tableDefinition['tableName'];

        $this->columns = $tableDefinition['columns'];

        if (array_key_exists('indexColumns', $tableDefinition) == true) {
            $this->indexColumns = $tableDefinition['indexColumns'];
        }

        //TODO - Delete this. The schema should always be defined.
        if (array_key_exists('schema', $tableDefinition) == true) {
            $this->schema = $tableDefinition['schema'];
        }

        if (array_key_exists('relatedTables', $tableDefinition) == true) {
            $this->initRelatedTables($tableDefinition['relatedTables']);
        }
    }

    /**
     * @param $relatedTables
     */
    function initRelatedTables($relatedTables) {

        foreach ($relatedTables as $relatedTableInfo) {
            $type = $relatedTableInfo[0];
            $relatedTableName = $relatedTableInfo[1];

            $relationName = $relatedTableInfo[2];
            $relatedTable = new $relatedTableName();

            $this->relatedTables[] = new RelatedTable($relatedTable, $type, $relationName);
        }
    }

    /**
     * @param TableMap $relatedTable
     */
    function addRelatedTable(TableMap $relatedTable) {
        $this->relatedTables[] = $relatedTable;
    }

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

    /**
     * 
     */
    function getRelationTable($relationName) {
        
    }

}