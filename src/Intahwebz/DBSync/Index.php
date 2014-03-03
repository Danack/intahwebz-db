<?php

namespace Intahwebz\DBSync;



class Index {

    var $unique;
    var $keyName;
    var $columns = array();

    var $isConstraint = false;

    function __construct($keyName, $unique) {
        $this->keyName = $keyName;

        if($unique == false){
            $unique  = false;
        }
        else{
            $unique = true;
        }

        $this->unique = $unique;

        checkForbiddenNames($this->keyName, array('PRIMARY'));
    }

    /**
     * @param $isConstraint
     */
    function setConstraint($isConstraint) {
        $this->isConstraint = $isConstraint;
    }

    /**
     * @param $columnName
     * @param $sequenceInIndex
     * @throws \Exception
     */
    function addColumn($columnName, $sequenceInIndex) {
        if (isset($this->columns[$sequenceInIndex]) == true) {
            throw new \Exception("Error, index already has column for sequenceInIndex $sequenceInIndex. " . $this->keyName);
        }

        $this->columns[$sequenceInIndex] = $columnName;
    }

    /**
     * @return string
     */
    function getCreationString() {
        return $this->getRawCreationString();
    }

    /**
     * @return string
     */
    function getRawCreationString() {
        $finalQuery = "";

        if ($this->keyName == 'PRIMARY') {
            $finalQuery .= " PRIMARY KEY ";
        } else {
            if ($this->unique == true) {
                $finalQuery .= " UNIQUE ";
            }
            $finalQuery .= " KEY ";
            $finalQuery .= $this->keyName . " ";
        }

        $finalQuery .= "( ";

        $commaString = "";

        ksort($this->columns);

        foreach ($this->columns as $column) {
            $finalQuery .= $commaString;
            $finalQuery .= "" . $column . " ";
            $commaString = ", ";
        }

        $finalQuery .= " )";

        return $finalQuery;
    }

    /**
     * @param $targetTable
     * @return MySQLOperation
     */
    function getIndexCreateOperation($targetTable) {
        $fullTableName = $targetTable->schemaName . "." . $targetTable->tableName;
        $rawCreationString = $this->getRawCreationString();
        $upgradeQuery = " ALTER TABLE $fullTableName ADD $rawCreationString; ";
        $comment = "Index ".$this->keyName." does not exist in older table, need to create it.\r\n";

        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_CREATE_INDEX, $comment);
    }

    /**
     * @param $targetTable
     * @return array
     */
    function getIndexChangeOperation($targetTable) {
        $targetRawCreationString = $this->getRawCreationString();
        $removeQuery = "ALTER TABLE ".$targetTable->schemaName.".".$targetTable->tableName." DROP INDEX " . $this->keyName . ";";
        $reAddQuery = "ADD INDEX $targetRawCreationString ;";

        $comment = "Index ".$this->keyName." in table ".$targetTable->tableName." has changed, so recreating.";

        $result = array(
            new MySQLOperation($removeQuery, OPERATION_TYPE_REMOVE_INDEX, $comment),
            new MySQLOperation($reAddQuery, OPERATION_TYPE_CREATE_INDEX, $comment),
    )	;

        return $result;
    }

    /**
     * @param $sourceTable
     * @return MySQLOperation
     */
    function getIndexDeleteOperation($sourceTable) {
        $fullTableName = $sourceTable->schemaName . "." . $sourceTable->tableName;

        $upgradeQuery = "ALTER TABLE $fullTableName DROP KEY " . $this->keyName . ";";

        $comment =  "Index ".$this->keyName." doesn't exist in newer version of table ".$sourceTable->tableName." , need to delete it.\r\n";

        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_REMOVE_INDEX, $comment);
    }

    /**
     * @param $indexColumns
     * @return Index
     */
    public static function createFromDefintion($indexColumns) {
        $indexName = "index";

        foreach ($indexColumns as $indexColumn) {
            $indexName .= "_".$indexColumn;
        }

        $index = new Index($indexName, false);

        $count = 0;

        foreach ($indexColumns as $indexColumn) {
            $index->addColumn($indexColumn, $count);
            $count++;
        }

        return $index;
    }
}
