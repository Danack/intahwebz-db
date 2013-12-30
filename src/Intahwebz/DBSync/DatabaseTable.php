<?php

namespace Intahwebz\DBSync;


use Intahwebz\DB\Connection;

class DatabaseTable{

    public $schemaName;
    public $tableName;

    /**
     * @var Field[]
     */
    public $fields = array();

    /** @var Index[] */
    public $indices = array();

    /**
     * @var Constraint[]
     */
    public $constraints = array();

    function __construct($schemaName, $tableName) {
        $this->schemaName = $schemaName;
        $this->tableName = $tableName;
    }

    function initFromDB(Connection $dbConnection) {
        $this->initFieldsForTableFromDB($dbConnection);
        $this->initFKConstraintsFromDB($dbConnection);
        $this->initIndicesFromDB($dbConnection);
    }

     function parseColumns(\Intahwebz\TableMap\TableMap $tableMap) {
        $columns = $tableMap->columns;

        foreach($columns as $column){
            $columnName = $column[0];

            $unique = false;
            $extra = '';

            if(array_key_exists('primary', $column) == true &&
                $column['primary'] == true){

                $keyName = 'PRIMARY';

                $index = new Index($keyName, $unique);
                $index->addColumn($column[0], 0);
                //$indices['PRIMARY'] = $index;
                $this->addIndex('PRIMARY', $index);
                $extra .= " AUTO_INCREMENT";
            }

            if(array_key_exists('foreignKey', $column) == true){
                $foreignKeyTableName = $column['foreignKey'];

                $constraintName = self::getConstraintName($tableMap->tableName, $foreignKeyTableName, array($columnName));

                $index = new Index($constraintName, false);
                $index->setConstraint(true);

                $index->addColumn($foreignKeyTableName."ID", 0);

                $constraint = new Constraint(
                    $this->schemaName,
                    $constraintName,
                    $this->tableName,
                    $foreignKeyTableName,
                    "CASCADE",
                    "CASCADE"
                );

                $columnName = $foreignKeyTableName."ID";
                $constraint->addColumn(1, $columnName, $columnName);

                $this->addIndex($constraintName, $index);
                $this->addConstraint($constraint);
            }

            $nullAllowed = false;
            if(array_key_exists('nullAllowed', $column)){
                $nullAllowed = 	$column['nullAllowed'];
            }
            $type = mb_strtolower(self::getColumnType($column));

            $field = new Field(
                $columnName,
                $type,
                $nullAllowed, //isNull
                //'unused',// $key,
                '', //$defaultString,
                $extra // $extra e.g. auto_increment
            );

            $this->addField($columnName, $field);
        }

        foreach($tableMap->indexColumns as $indexColumns){
            $index = Index::createFromDefintion($indexColumns);
            $this->addIndex($index->keyName, $index);
        }
    }


    function	addField($alias, Field $field) {
        $this->fields[$alias] = $field;
    }

    function	addIndex($alias, Index $index) {
        $this->indices[$alias] = $index;
    }

    function	addConstraint(Constraint $constraint) {
        $this->constraints[$constraint->name] = $constraint;
    }

    function	getTableCreateOperations(){

        $tableSchema = $this->getTableSchemaInfo();

        $finalQuery = " CREATE TABLE ".$this->schemaName.".".$this->tableName."( ";

        $finalQuery .= $this->getFieldCreationStatement();
        $finalQuery .= $this->getIndicesCreationStatement();

        $finalQuery .= " ) ";

        foreach($tableSchema as $key => $value){
            if($value === false){
                $finalQuery .= $key." ";
            }
            else{
                $finalQuery .= $key."=".$value." ";
            }
        }

        $finalQuery .= "; ";

        $operations = array();
        $operations[] = new MySQLOperation($finalQuery, OPERATION_TYPE_CREATE_TABLE);

        foreach($this->constraints as $constraint){
            $operations[] = $constraint->getConstraintCreateOperation();
        };

        return $operations;
    }

    /**
     * @param $fieldNameToGet
     * @return Field
     */
    function	getFieldByName($fieldNameToGet){
        if(array_key_exists($fieldNameToGet, $this->fields) == true){
            return $this->fields[$fieldNameToGet];
        }

        return null;
    }

    /**
     * @param $indexName
     * @return Index
     */
    function	getIndexByName($indexName){
        if(array_key_exists($indexName, $this->indices) == true){
            return $this->indices[$indexName];
        }

        return null;
    }

    function	getConstraintByName($constraintName){
        if(array_key_exists($constraintName, $this->constraints) == true){
            return $this->constraints[$constraintName];
        }
        return false;
    }

    function	getTableSchemaInfo(){
        $tableSchema = array(
            'ENGINE' => 'InnoDB',
            'DEFAULT' => false,
            'CHARSET' => UTF8_CHARSET,
            'COLLATE' => UTF8_COLLATION,
        );

        return	$tableSchema;
    }

    function	getFieldCreationStatement(){
        $finalQuery = "";
        $commaString = "";

        foreach($this->fields as $field){

            $finalQuery .= $commaString;

            $finalQuery .= " ".$field->fieldName." ";
            $finalQuery .= " ".$field->type;

            $finalQuery .= $field->nullability;

            if($field->isDefault != ''){
                $finalQuery .= "default '".$field->isDefault."'";
            }

            $finalQuery .= " ".$field->extra;

            $commaString = ", ";
        }

        return	$finalQuery;
    }


    function	getIndicesCreationStatement(){
        $finalQuery = "";
        $commaString = ", ";

        foreach($this->indices as $indexObject){

            if($indexObject->isConstraint){
                //Continue
            }
            else{
                $finalQuery .= $commaString;
                $finalQuery .= $indexObject->getCreationString();
            }
        }

        return	$finalQuery;
    }

    function	getConstraintsCreationStatement(){
        $finalQuery = "";
        $commaString = ", ";

        foreach($this->constraints as $constraint){

            $finalQuery .= $commaString;
            $finalQuery .= $constraint->getRawCreationString();
        }

        return	$finalQuery;
    }

    function	getTableDeleteOperation(){
        $tableName = $this->schemaName.".".$this->tableName;
        $upgradeQuery = "drop TABLE ".$tableName.";";
        return new MySQLOperation($upgradeQuery, OPERATION_TYPE_REMOVE_TABLE);
    }

    function	checkForForbiddenNames(){
        checkForbiddenNames($this->schemaName);
        checkForbiddenNames($this->tableName);
    }

    /**
     * @param $olderTable
     * @return MySQLOperation[]
     */
    function	getTableChangeOperations($olderTable){
        $tableChangeOperations = array();

        $fieldOperations = $this->getFieldChangeOperations($olderTable);
        $tableChangeOperations = array_merge($tableChangeOperations, $fieldOperations);

        $indexOperations = $this->getIndexChangeOperations($olderTable);
        $tableChangeOperations = array_merge($tableChangeOperations, $indexOperations);

        $constraintOperations = $this->getConstraintChangeOperations($olderTable);
        $tableChangeOperations = array_merge($tableChangeOperations, $constraintOperations);

        return	$tableChangeOperations;
    }

    function	initFieldsForTableFromDB(Connection $connection){
        $query = "SHOW FIELDS FROM ".$this->schemaName.".".$this->tableName;

        $statementWrapper = $connection->prepareAndExecute($query);


        $fieldName =
            $type =
            $null =
            $key =
              $default =
            $extra = false;

        $statementWrapper->statement->bind_result(
            $fieldName,
            $type,
            $null,
            $key,
            $default,
            $extra
        );

        while($statementWrapper->statement->fetch()) {
            $this->fields[$fieldName] = new Field(
                $fieldName,
                $type,
                $null,
                //$key,
                $default,
                $extra
            );
        }

        $statementWrapper->close();
    }

    function	initIndicesFromDB(Connection $connection){
        $query = "SHOW INDEX FROM ".$this->schemaName.".".$this->tableName;

        $statementWrapper = $connection->prepareAndExecute($query);


        $table =
            $nonUnique =
            $keyName =

            $sequenceInIndex =
            $columnName =
            $collation =

            $cardinality =
            $subPart =
            $packed =

            $null =
            $indexType =
            $comment =
            $indexComment = false;

        $statementWrapper->bindResult(
            $table,
            $nonUnique,
            $keyName,

            $sequenceInIndex,
            $columnName,
            $collation,

            $cardinality,
            $subPart,
            $packed,

            $null,
            $indexType,
            $comment,

            //Index comment only in version 5.1+?
            $indexComment
        );

        while($statementWrapper->statement->fetch()) {
            //$lcKeyName = mb_strtolower($keyName); why did we have lower case compare before?
            if(isset($this->indices[$keyName]) == true){
                //Already exists, do nothing
            }
//			else if(isset($this->constraints[$keyName]) == TRUE){
//				//Constraints are created through constraints - you don't need to worry about
//				//creating the index yourself.
//				continue;
//			}
            else{
                $unique = false;

                if (mb_strcasecmp("PRIMARY", $keyName) == 0){
                    $unique = false;
                }
                else if($nonUnique == false){	//MySQL has the bad naming convention here.
                    $unique = true;
                }

                $this->indices[$keyName] = new Index($keyName, $unique);
            }

            $this->indices[$keyName]->addColumn($columnName, $sequenceInIndex - 1);
        }

        $statementWrapper->close();
    }


    /**
     * @internal param $schema
     * @internal param $tableName
     * @param \Intahwebz\DB\Connection $dbConnection
     * @return array
     *
     * Note - apparently only one prepared statement can be open on the tables information_schema - so do things separately.
     */
    function	initFKConstraintsFromDB(Connection $dbConnection){
        //TODO - sort out the freaking table name cases.
        $queryStringConstraintInfo = "select	CONSTRAINT_SCHEMA,
                            CONSTRAINT_NAME,
                            TABLE_NAME,
                            REFERENCED_TABLE_NAME,
                            UPDATE_RULE,
                            DELETE_RULE
                        from
                            information_schema.referential_constraints
                        where constraint_schema like ?
                        and TABLE_NAME like ?;";

        $queryStringConstraintColumns = "select ORDINAL_POSITION,
                                            COLUMN_NAME,
                                            REFERENCED_COLUMN_NAME
                                     from information_schema.key_column_usage
                                     where constraint_schema like ?
                                     and TABLE_NAME like ?
                                     and CONSTRAINT_NAME like ?";

        //$connection = $this->dbConnection;//new ConnectionWrapper();

        $constraintInfoStatement = $dbConnection->prepareStatement($queryStringConstraintInfo);

        $constraintInfoStatement->bindParam('ss', $this->schemaName, $this->tableName);

        $constraintInfoStatement->execute();

        $constraintInfo = array();

        $constraintInfoStatement->bindResult(
            $constraintInfo['constraintSchema'],
            $constraintInfo['constraintName'],
            $constraintInfo['tableName'],
            $constraintInfo['referencedTableName'],
            $constraintInfo['updateRule'],
            $constraintInfo['deleteRule']
        );

        while($constraintInfoStatement->fetch()){

            $constraint = new Constraint(
                $constraintInfo['constraintSchema'],
                $constraintInfo['constraintName'],
                $constraintInfo['tableName'],
                $constraintInfo['referencedTableName'],
                $constraintInfo['updateRule'],
                $constraintInfo['deleteRule']);

            $this->constraints[$constraintInfo['constraintName']] = $constraint;

            //Indices which are constraints need to be handled slightly differently.
            //$this->indices[$constraintInfo['constraintName']]->setConstraint(TRUE);
        }

        $constraintInfoStatement->close();

        $constraintColumnsStatement = $dbConnection->prepareStatement($queryStringConstraintColumns);

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach($this->constraints as $constraintName => $constraint){
            $constraintColumnsStatement->bindParam('sss', $this->schemaName, $this->tableName, $constraint->name);

            $constraintColumnsStatement->execute();

            $columnInfo = array();
            $constraintColumnsStatement->bindResult(
                $columnInfo['ordinalPosition'],
                $columnInfo['columnName'],
                $columnInfo['referencedTableName']
            );

            while($constraintColumnsStatement->fetch()){
                $constraint->addColumn(
                    $columnInfo['ordinalPosition'],
                    $columnInfo['columnName'],
                    $columnInfo['referencedTableName']
                );
            }
        }

        $constraintColumnsStatement->close();
    }

    function	getFieldChangeOperations(DatabaseTable $olderTable){

        $fieldChangeOperations = array();

        foreach($this->fields as $newerField){

            $olderField = $olderTable->getFieldByName($newerField->fieldName);

            if($olderField == false){ //"Field ".$newerField->fieldName." does not exist in older table, need to create it.\r\n";
                $fieldChangeOperations[] = $newerField->getFieldCreateOperation($this);
            }
            else{	// "Source field does exist, need to compare the two. \r\n";
                if($newerField != $olderField){
                    $fieldChangeOperations[] = $newerField->getFieldChangeOperation($this, $olderField);
                }
            }
        }

        foreach($olderTable->fields as $olderFieldName => $olderField){
            $newerField = $this->getFieldByName($olderFieldName);
            if($newerField == false){
                $fieldChangeOperations[] = $olderField->getFieldDeleteOperation($olderTable);
            }
        }

        return	$fieldChangeOperations;
    }


    function	getIndexChangeOperations(DatabaseTable $olderTable){
        $indexChangeOperations = array();

        foreach($this->indices as $newerIndexName => $newerIndexObject){
            $olderIndex = $olderTable->getIndexByName($newerIndexName);

            if($olderIndex == false){
                if($newerIndexObject->isConstraint == true){
                    //Constraints are added via the constraint.
                }
                else{
                    $indexChangeOperations[] = $newerIndexObject->getIndexCreateOperation($this);
                }
            }
            else{
                //echo "Field $newerIndexName exists in both tables, need to compare the two. \r\n";
                if($newerIndexObject != $olderIndex){
                //if($newerIndexObject->identical($olderTable->indices[mb_strtolower($newerIndexName)]) == FALSE){
                    if($newerIndexObject->isConstraint == true){
                        //Constraints are added via the constraint.
                    }
                    else{
                        $operations = $newerIndexObject->getIndexChangeOperation($this, $olderIndex);
                        $indexChangeOperations = array_merge($indexChangeOperations, $operations);
                    }
                }
            }
        }

        foreach($olderTable->indices as $olderIndexName => $olderIndexObject){
            $newerIndex = $this->getIndexByName($olderIndexName);
            if($newerIndex == false){
                if($olderIndexObject->isConstraint == true){
                    //Constraints are dropped in one go through the Constraint object.
                }
                else{
                    $indexChangeOperations[] = $olderIndexObject->getIndexDeleteOperation($olderTable);
                }
            }
        }

        return	$indexChangeOperations;
    }



    function	getConstraintChangeOperations(DatabaseTable $olderTable){
        $operations = array();

        foreach($this->constraints as $newerConstraintName => $newerConstraint){
            $olderConstraint = $olderTable->getConstraintByName($newerConstraintName);

            if($olderConstraint == false){
                $operations[] = $newerConstraint->getConstraintCreateOperation($this);
            }
            else{
                if($newerConstraint != $olderConstraint){
                    $changeOperations = $newerConstraint->getConstraintChangeOperations($this, $olderConstraint);
                    $operations = array_merge($operations, $changeOperations);
                }
            }
        }

        foreach($olderTable->constraints as $olderConstraintName => $olderConstraint){
            $newerConstraint = $this->getConstraintByName($olderConstraintName);
            if($newerConstraint == false){
                $operations[] = $olderConstraint->getConstraintDeleteOperation($olderTable);
            }
        }

        return	$operations;
    }


    static function getConstraintName(
        $constraintTableName,
        $referencedTableName,
        $constraintFields) {

        $name = "fk_";

        $name .= $constraintTableName;

        $name .= "_";

        $name .= $referencedTableName;

        foreach($constraintFields as $constraintField){
            $name .= "_".$constraintField;
        }

        return $name;
    }

    function getColumnType($column) {

        $mysqlNativeTypes = array(
            'TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB',
            'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT',
        );

        $type = 'varchar(2048)';

        if(array_key_exists('primary', $column) == true){
            $type = 'bigint(20)';
        }

        if(array_key_exists('type', $column) == true){

            switch($column['type']){
                case('i'):{
                    $type = 'bigint(20)';
                    break;
                }

                case('d'):{
                    $type = 'datetime';
                    break;
                }

                case('hash'):{
                    $type = 'varchar(100)';
                    break;
                }

                case('text'):{
                    $type = 'text';
                    break;
                }

                default:{
                if (in_array($column['type'], $mysqlNativeTypes) == true) {
                    return $column['type'];
                }

                throw new \Exception("Unknown column type [".$column['type']."]");
                }
            }
        }

        return $type;
    }

}


