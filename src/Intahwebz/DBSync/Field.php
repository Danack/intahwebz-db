<?php

namespace Intahwebz\DBSync;




class Field {

    var $fieldName;
    var $type;
    var $nullability;

    var $isDefault;		//Shouldn't this be default - and the value
    var $extra;

    function __construct($fieldName, $type, $nullAllowed, $isDefault, $extra) {

        $this->fieldName = $fieldName;

        checkForbiddenNames($this->fieldName);

        $this->type = mb_strtolower($type);

        if ($nullAllowed == "YES") {
            $this->nullability = "";
        }
        else {
            $this->nullability = " NOT NULL";
        }

        //$this->key = $key;//TODO - key is not used

        if($isDefault == NULL){
            $isDefault = '';
        }

        $this->isDefault = $isDefault;
        $this->extra = mb_strtoupper(trim($extra));
    }

    function    getFieldDeleteOperation($targetTable) {
        $fullTableName = $targetTable->schemaName . "." . $targetTable->tableName;
        $upgradeQuery = "ALTER TABLE $fullTableName drop COLUMN " . $this->fieldName . ";";
        $comment =  "Field ".$this->fieldName." doesn't exist in newer table [".$targetTable->tableName."], need to delete it.\r\n";

        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_REMOVE_COLUMN, $comment);
    }

    function    getFieldChangeOperation(DatabaseTable $targetTable, $targetField) {

        if (($this->type == $targetField->type) &&
            ($this->nullability == $targetField->nullability) &&
            ($this->isDefault == $targetField->isDefault) &&
            ($this->extra == $targetField->extra)
        ) {
            throw new \Exception("This is not meant to be possible any more - please compare fields and only get the change if there is one.");
        }

        $fullTableName = $targetTable->schemaName . "." . $targetTable->tableName;

        $upgradeQuery = " ALTER TABLE $fullTableName MODIFY COLUMN " . $this->fieldName . " " . $this->type . " " . $this->nullability . " " . $this->isDefault . " " . $this->extra . "; ";

        $comment = "Field ".$this->fieldName." has had it's definition changed.\r\n";

        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_MODIFY_COLUMN, $comment);
    }

    function    getFieldCreateOperation($targetTable) {
        $fullTableName = $targetTable->schemaName . "." . $targetTable->tableName;

        $upgradeQuery = " ALTER TABLE $fullTableName ADD COLUMN " . $this->fieldName . " " . $this->type . " " . $this->nullability . " " . $this->isDefault . " " . $this->extra . "; ";

        $comment = "Field ".$this->fieldName." does not exist in older table, need to create it.\r\n";

        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_CREATE_COLUMN, $comment);
    }
}
