<?php

namespace Intahwebz\DBSync;


class Constraint{

    var $schema;	//Schema that the constraint operates on. Not necessarily the same as the schema for
                    //the tables, but it is in this project.

    var	$name;		//fk_contentID

    var $tableName;
    var $referencedTableName;

    var $columnNames = array();
    var $referencedColumnNames = array();

    var $updateRule;		//CASCADE
    var $deleteRule;		//CASCADE

    function	__construct($constraintSchema,
                            $constraintName,
                            $tableName,
                            $referencedTableName,
                            $updateRule,
                            $deleteRule){

        $this->schema = $constraintSchema;
        $this->name = $constraintName;

        $this->tableName = $tableName;
        $this->referencedTableName = $referencedTableName;

        $this->updateRule = $updateRule;
        $this->deleteRule = $deleteRule;
    }

    function addColumn($ordinalPosition, $columnName, $referencedColumnName) {
        $zeroBasedIndex = $ordinalPosition - 1;

        $this->columnNames[$zeroBasedIndex] = $columnName;
        $this->referencedColumnNames[$zeroBasedIndex] = $referencedColumnName;
    }

    function getRawCreationString() {
        $sqlString = "CONSTRAINT `".$this->name."` FOREIGN KEY (";

        $commaString = "";
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach($this->columnNames as $ordinal => $columnName){
            $sqlString .= $commaString.$columnName;
            $commaString = ", ";
        }

        $sqlString .= " ) ";

        $sqlString .= "REFERENCES `".$this->referencedTableName."` (";

        $commaString = "";

        foreach($this->referencedColumnNames as $columnName){
            $sqlString .= $commaString.$columnName;
            $commaString = ", ";
        }

        $sqlString .= " ) ";

        $sqlString .= " ON DELETE ".$this->deleteRule;

        $sqlString .= " ON UPDATE ".$this->updateRule;

        return		$sqlString;
    }


    function getConstraintCreateOperation() {
        $sqlString  = "ALTER TABLE `".$this->schema."`.`".$this->tableName."` ";
        $sqlString .= "ADD ";
        $sqlString .= $this->getRawCreationString();
        $sqlString .= ";";

        $comment = "Adding constraint ".$this->name." to the table ".$this->tableName;
        return new MySQLOperation($sqlString, OPERATION_TYPE_CREATE_CONSTRAINT, $comment);
    }

    function getConstraintChangeOperations(DatabaseTable $targetTable) {

        $operations = array();

        //TODO - why no used param inspection.
        $operations[] = $this->getConstraintDeleteOperation($targetTable);
        $operations[] = $this->getConstraintCreateOperation($targetTable);

        return $operations;
    }

    function getConstraintDeleteOperation() {

        $queryString = "ALTER TABLE `".$this->schema."`.`".$this->tableName."`
                        DROP FOREIGN KEY `".$this->name."` ;";

        $comment = "Dropping constraint.";

        return new MySQLOperation($queryString, OPERATION_TYPE_REMOVE_CONSTRAINT, $comment);
    }



    public static function createFromDefintion($column){

    }

}





// TODO MySQL schema queries can be incredibly slow as they take a 'file_exists' call per row in
// some cases. You can 'optimize' this by using appropriate where columns for certain queries.

//http://dev.mysql.com/doc/refman/5.5/en/information-schema-optimization.html


/*
select *
    from information_schema.table_constraints
where constraint_schema = 'YOUR_DB'
Use the information_schema.key_column_usage table to get the fields in each one of those constraints:

select *
    from information_schema.key_column_usage
where constraint_schema = 'YOUR_DB'
If instead you are talking about foreign key constraints, use information_schema.referential_constraints:

select *
    from information_schema.referential_constraints
where constraint_schema = 'YOUR_DB'

    */



//show columns
//	from information_schema.key_column_usage;
//where constraint_schema = 'basereality';

//http://dev.mysql.com/doc/refman/5.1/en/key-column-usage-table.html
//		Field,Type,Null,Key,Default,Extra
//def,          		CONSTRAINT_CATALOG,varchar(512),NO,,,
//basereality,  		CONSTRAINT_SCHEMA,varchar(64),NO,,,
//fk_contentID, 		CONSTRAINT_NAME,varchar(64),NO,,,
//def,          		TABLE_CATALOG,varchar(512),NO,,,
//basereality,  		TABLE_SCHEMA,varchar(64),NO,,,
//contentTag,   		TABLE_NAME,varchar(64),NO,,,
//contentID,    		COLUMN_NAME,varchar(64),NO,,,
//1,            		ORDINAL_POSITION,bigint(10),NO,,0,
//1,            		POSITION_IN_UNIQUE_CONSTRAINT,bigint(10),YES,,NULL,
//basereality,  		REFERENCED_TABLE_SCHEMA,varchar(64),YES,,NULL,
//content,      		REFERENCED_TABLE_NAME,varchar(64),YES,,NULL,
//contentID     		REFERENCED_COLUMN_NAME,varchar(64),YES,,NULL,

//select *
//	from information_schema.referential_constraints
//where constraint_schema = 'basereality';
//Field,Type,Null,Key,Default,Extra
//def,            //CONSTRAINT_CATALOG,varchar(512),NO,,,
//basereality,    //CONSTRAINT_SCHEMA,varchar(64),NO,,,
//fk_contentID,   //CONSTRAINT_NAME,varchar(64),NO,,,
//def,            //UNIQUE_CONSTRAINT_CATALOG,varchar(512),NO,,,
//basereality,    //UNIQUE_CONSTRAINT_SCHEMA,varchar(64),NO,,,
//PRIMARY,        //UNIQUE_CONSTRAINT_NAME,varchar(64),YES,,NULL,
//NONE,           //MATCH_OPTION,varchar(64),NO,,, - always none
//CASCADE,        //UPDATE_RULE,varchar(64),NO,,,
//CASCADE,        //DELETE_RULE,varchar(64),NO,,,
//contentTag,     //TABLE_NAME,varchar(64),NO,,,
//content         //REFERENCED_TABLE_NAME,varchar(64),NO,,,


