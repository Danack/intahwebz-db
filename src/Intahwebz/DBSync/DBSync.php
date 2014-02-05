<?php

namespace Intahwebz\DBSync;

use Intahwebz\DB as DB;

define('UTF8_CHARSET', 'utf8mb4');
define('UTF8_COLLATION', 'utf8mb4_unicode_ci');

//define('UTF8_CHARSET', 'utf8');
//define('UTF8_COLLATION', 'utf8_general_ci');

use Intahwebz\DB\Connection;
use Intahwebz\Exception\UnsupportedOperationException;


require_once('sqlKeywords.php');


define('OPERATION_TYPE_PRE_SYNC', 'OPERATION_TYPE_PRE_SYNC');
define('OPERATION_TYPE_CREATE_TABLE', 'OPERATION_TYPE_CREATE_TABLE');
define('OPERATION_TYPE_CREATE_COLUMN', 'OPERATION_TYPE_CREATE_COLUMN');
define('OPERATION_TYPE_TRANSFORM_DATA', 'OPERATION_TYPE_TRANSFORM_DATA');
define('OPERATION_TYPE_CREATE_INDEX', 'OPERATION_TYPE_CREATE_INDEX');
define('OPERATION_TYPE_CREATE_CONSTRAINT', 'OPERATION_TYPE_CREATE_CONSTRAINT');
define('OPERATION_TYPE_POST_SYNC', 'OPERATION_TYPE_POST_SYNC');
define('OPERATION_TYPE_REMOVE_TABLE', 'OPERATION_TYPE_REMOVE_TABLE');
define('OPERATION_TYPE_REMOVE_INDEX', 'OPERATION_TYPE_REMOVE_INDEX');
define('OPERATION_TYPE_REMOVE_CONSTRAINT', 'OPERATION_TYPE_REMOVE_CONSTRAINT');
define('OPERATION_TYPE_REMOVE_COLUMN', 'OPERATION_TYPE_REMOVE_COLUMN');
define('OPERATION_TYPE_MODIFY_COLUMN', 'OPERATION_TYPE_MODIFY_COLUMN');
define('OPERATION_TYPE_MODIFY_INDEX', 'OPERATION_TYPE_MODIFY_INDEX');



class DBSync{

    /**
     * @var \Intahwebz\DB\Connection
     */
    private $dbConnection;


    public static  $operationPrecedence = array(
        OPERATION_TYPE_REMOVE_CONSTRAINT	=>	0,
        OPERATION_TYPE_REMOVE_INDEX			=>	10,
        OPERATION_TYPE_PRE_SYNC				=>	20,
        OPERATION_TYPE_CREATE_TABLE			=>	30,
        OPERATION_TYPE_CREATE_COLUMN		=>	40,
        OPERATION_TYPE_TRANSFORM_DATA		=>	50,

        OPERATION_TYPE_MODIFY_COLUMN		=>	52,
        OPERATION_TYPE_MODIFY_INDEX			=>	54,

        OPERATION_TYPE_CREATE_INDEX			=>	60,
        OPERATION_TYPE_CREATE_CONSTRAINT	=>	70,
        OPERATION_TYPE_REMOVE_COLUMN		=>	80,
        OPERATION_TYPE_REMOVE_TABLE			=>	90,
        OPERATION_TYPE_POST_SYNC			=>	100,
    );


    /**
     * @var MySQLOperation[]
     */
    private $mySQLOperations = array();

    function __construct(Connection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @param $sqlOperationsForVersion
     */
    function addOperations($sqlOperationsForVersion) {
        foreach ($sqlOperationsForVersion as $sqlOperations) {
            foreach ($sqlOperations as $sqlOperation){
                $operationType = $sqlOperation[0];
                $sqlOperation = $sqlOperation[1];

                $this->mySQLOperations[] = new MySQLOperation($sqlOperation, $operationType);
            }
        }
    }

    /**
     * @param Connection $connection
     * @param $schemaName
     */
    function createSchema(Connection $connection, $schemaName) {
        $queryString = "CREATE SCHEMA IF NOT EXISTS $schemaName
    DEFAULT CHARACTER SET = ".UTF8_CHARSET."
    DEFAULT COLLATE = ".UTF8_COLLATION.";";

        $connection->directExecute($queryString);
    }

    /**
     * @param Connection $connection
     * @param $schemaName
     * @return bool
     */
    public static function checkSchemaExists(Connection $connection, $schemaName) {
        $result = false;
        $queryString = "show schemas where `Database` = '$schemaName';";
        $statementWrapper = $connection->prepareAndExecute($queryString);
        $schemaNameResult = '';
        $statementWrapper->statement->bind_result($schemaNameResult);

        while($statementWrapper->statement->fetch()) {
            $result = true;
        }

        $statementWrapper->close();
        return $result;
    }

    /**
     * @param $schemaNameToGet
     * @param $knownTables
     * @return Schema
     */
    function getSchemaFromDefinedTables($schemaNameToGet, $knownTables) {
        $schema = new Schema($schemaNameToGet);
        $schema->parseTables($knownTables);

        return $schema;
    }

    /**
     * @internal param \Intahwebz\DBSync\MySQLOperation[] $actionsToTake
     */
    function processDatabaseOperationsForUpgrade($schemaName) {
        $connection = $this->dbConnection;


        $connection->directExecute("SET FOREIGN_KEY_CHECKS=0");


        $schemaExists = $this->checkSchemaExists($connection, $schemaName);

        if($schemaExists == false){
            $this->createSchema($connection, $schemaName);
        }

        $connection->close();

        $count = 0;

        foreach ($this->mySQLOperations as $mySQLOperation) {
            $count++;
            $mySQLOperation->comment = $count;
        }

        usort($this->mySQLOperations, array($this, 'compareOperations'));

//        foreach ($this->mySQLOperations as $mySQLOperation) {
//        	echo  $mySQLOperation->operationType." ".$mySQLOperation->comment."\n";
//        }

        $connectionWrapper = $this->dbConnection;


        foreach($this->mySQLOperations as $mySQLOperation){
            $sqlString = $mySQLOperation->getUpgradeSQL();

            if($sqlString != false){
                //echo "\r\n";
                //echo $sqlString;
                //echo "\r\n";

                if ($mySQLOperation->operationType == OPERATION_TYPE_TRANSFORM_DATA) {
                    //TODO - this needs to modify the data
                }

                $connectionWrapper->directExecute($sqlString);
            }
        }

        $connection->directExecute("SET FOREIGN_KEY_CHECKS=1");
    }

    /**
     * @param $schemaName
     * @param array $tablesToUpgrade
     */
    function processUpgradeForSchema($schemaName, array $tablesToUpgrade) {
        $newSchema = $this->getSchemaFromDefinedTables($schemaName, $tablesToUpgrade);

        $currentSchema = new Schema($schemaName);
        $currentSchema->initFromDatabase($this->dbConnection);

        $changes = $newSchema->getChanges($currentSchema);

        $this->mySQLOperations = array_merge($this->mySQLOperations, $changes);

        $this->processDatabaseOperationsForUpgrade($schemaName);
    }

    /**
     * @param \Intahwebz\DBSync\MySQLOperation $a
     * @param \Intahwebz\DBSync\MySQLOperation $b
     * @throws UnsupportedOperationException
     * @return bool
     */
    function compareOperations(MySQLOperation $a, MySQLOperation $b) {

        if (array_key_exists($a->operationType, self::$operationPrecedence) == false) {
            throw new UnsupportedOperationException("Operation type a [".$a->operationType."] unknown, cannot sort.");
        }

        if (array_key_exists($b->operationType, self::$operationPrecedence) == false) {
            throw new UnsupportedOperationException("Operation type b [".$b->operationType."] unknown, cannot sort.");
        }

        $aValue = self::$operationPrecedence[$a->operationType];
        $bValue = self::$operationPrecedence[$b->operationType];

        if ($aValue == $bValue) {
            return $a->count > $b->count;
        }

        return  $aValue > $bValue;
    }
}