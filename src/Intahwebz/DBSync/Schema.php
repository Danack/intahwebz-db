<?php

namespace Intahwebz\DBSync;


use Intahwebz\DB as DB;

use Intahwebz\DB\Connection;

use Intahwebz\TableMap\SQLTableMap;
use Intahwebz\TableMap\TableMap;
use Intahwebz\TableMap\TableMapWriter;

class Schema {

    var $name;

    /**
     * @var $tableMap DatabaseTable[]
     */
    var $tables = array();

    function __construct($name) {
        $this->name = $name;
    }

    /**
     * @param $tableName
     * @param DatabaseTable $table
     * @throws \Exception
     */
    function addTable($tableName, DatabaseTable $table){
        if (array_key_exists($tableName, $this->tables) == true) {
            throw new \Exception("Schema aleady contains table [$tableName] schemas cannot contain two tables with the same name.");
        }

        $this->tables[$tableName] = $table;
    }

    /**
     * @param $tableNameToGet
     * @return bool|DatabaseTable
     */
    function getTable($tableNameToGet) {
        foreach($this->tables as $tableName => $table){
            if(mb_strcasecmp($tableNameToGet, $tableName) == 0){
                return $table;
            }
        }

        return FALSE;
    }

    /**
     * @param Connection $connection
     */
    function initFromDatabase(Connection $connection) {
        $schemaExists = DBSync::checkSchemaExists($connection, $this->name);

        if($schemaExists == FALSE){
            //do nothing
        }
        else{
            $this->initFromDatabaseExisting($connection);
        }

        $connection->close();
    }


    /**
     * @param Connection $connection
     */
    function initFromDatabaseExisting(Connection $connection) {
        $query = "SHOW TABLES FROM ".$this->name;

        $statementWrapper = $connection->prepareAndExecute($query);

        $tableName = false;
        $statementWrapper->statement->bind_result($tableName);

        $tableNames = array();

        while($statementWrapper->statement->fetch()) {
            $tableNames[] = $tableName;
        }

        $statementWrapper->close();

        foreach($tableNames as $tableName){
            $table = new DatabaseTable($this->name, $tableName);
            $table->initFromDB($connection);

            $this->addTable($tableName, $table);
        }
    }

    /**
     * @param $olderSchema Schema
     * @return mixed
     */
    function getChanges(Schema $olderSchema) {

        $databaseOperations = array();
        foreach($this->tables as $newerTableName => $newerTableObject){

            $oldTable = $olderSchema->getTable($newerTableName);

            if($oldTable === FALSE){
                $tableCreateOperations = $newerTableObject->getTableCreateOperations();
                $databaseOperations = array_merge($databaseOperations, $tableCreateOperations);
            }
            else{
                $tableChangeOperations = $newerTableObject->getTableChangeOperations($oldTable);
                $databaseOperations = array_merge($databaseOperations, $tableChangeOperations);
            }
        }

        $databaseOperations = array_merge($databaseOperations, $olderSchema->getRemovedTableOperations($this));

        return	$databaseOperations;
    }

    /**
     * @param Schema $newerSchema
     * @return array
     */
    function getRemovedTableOperations(Schema $newerSchema) {
        //This is in the older schema
        $databaseOperations = array();

        //Find the removed tables
        foreach($this->tables as $tableName => $oldTable){
            $newTable = $newerSchema->getTable($tableName);

            if($newTable == FALSE){
                if(mb_stripos($tableName, '_backup') === FALSE){
                    //Tables with '_backup' in their name are not dropped. This allows use to:
                    //i) Rename a table before we start an upgrade.
                    //ii) Create the new tables
                    //iii) Copy the data from the backed up tables into the new tables.
                    $databaseOperations[] = $oldTable->getTableDeleteOperation();//create remove table operation
                }
            }
        }

        return $databaseOperations;
    }

    /**
     * @param $knownTables \Intahwebz\TableMap\TableMap[]
     */
    function parseTables($knownTables) {

        foreach ($knownTables as $tableMap) {
            $tableName = $tableMap->tableName;
            $schemaName = $tableMap->schema;

            if (!($tableMap instanceof SQLTableMap)) {
                continue;
            }

            if ($schemaName != $this->name) {
                continue;
            }

            $relationTables = $this->createRelatedDatabaseTables($tableMap);

            foreach ($relationTables as $relationTable) {
                $table = new DatabaseTable($schemaName, $relationTable->getTableName());
                $table->parseColumns($relationTable);
                $this->addTable($relationTable->getTableName(), $table);
            }

            $table = new DatabaseTable($schemaName, $tableName);

            $table->parseColumns($tableMap);
            $this->addTable($tableName, $table);
        }
    }

    /**
     * @param TableMap $tableMap
     * @return TableMap[]
     */
    function createRelatedDatabaseTables(TableMap $tableMap) {
        $relationTables = array();

        $tableMapWriter = new TableMapWriter();

        foreach ($tableMap->getRelatedTables() as $relatedTable) {

            $relatedTableMap = $relatedTable->getTableMap();
            $relationTableDefinition = $this->generateRelationDatabaseTableDefinition($tableMap, $relatedTableMap);

            $className = $tableMap->getTableName().$relatedTableMap->getTableName()."Relation";

            $this->generateObjectFileForRelationTable(
                 "./var/src/",
                 ".php",
                 "BaseReality\\RelationTable",
                 $className,
                 $relationTableDefinition
            );

            //$tableMap->getClassName();
            $namespace = getNamespace(get_class($tableMap));
            $namespaceClassName = $namespace."\\".$className;

            $relationTables[] = new $namespaceClassName();
        }

        return $relationTables;
    }

    /**
     * @param $directory
     * @param $extension
     * @param $namespace
     * @param $className
     * @param $definition
     */
    function generateObjectFileForRelationTable($directory, $extension, $namespace, $className, $definition) {

        $output = "<?php\n\n";
        $output .= "namespace $namespace;\n\n";

        $output .= "use Intahwebz\\TableMap\\SQLTableMap;\n\n";

        $output .= "class $className extends SQLTableMap { ";
        $output .= "\n";
        $output .= "//Stuff goes here\n";

        $output .= "    function getTableDefinition() {
            \$tableDefinition = ";


            $output .= var_export($definition, true);

        $output .= ";\n
        return \$tableDefinition;
    }\n\n";

        $output .= "}\n";
        $output .= "\n";

        $filename = $directory.str_replace('\\', '/', $namespace).'/'.$className.$extension;

        ensureDirectoryExists($filename);

        $fileHandle = fopen($filename, "w");
        fwrite($fileHandle, $output);
        fclose($fileHandle);
    }


    /**
     * @param TableMap $tableMap
     * @param TableMap $relatedTable
     * @return array
     */
    function generateRelationDatabaseTableDefinition(TableMap $tableMap, TableMap $relatedTable) {

        $primaryColumnForTable =  $tableMap->getPrimaryColumn();
        $primaryColumnForRelationTable =  $relatedTable->getPrimaryColumn();

         $tableDefinition = array(
            'schema' => $tableMap->schema,
            'tableName' => $tableMap->getTableName().$relatedTable->getTableName().'Relation',

            'columns' => array(
                array(
                    $primaryColumnForTable,
                    'type' => 'i',
                    'foreignKey' => $tableMap->getTableName()
                ),
                array(
                    $primaryColumnForRelationTable,
                    'type' => 'i',
                    'foreignKey' => $relatedTable->getTableName()
                ),
            ),
        );

        return $tableDefinition;
    }

}

