<?php


namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockNoteSQLTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockNote',
            'columns' => array(
                array('mockNoteID', 'primary' => true, 'autoInc' => true ),
                array('mockContentID', 'type' => 'i', 'foreignKey' => 'mockContent'),
                array('title'),
                array('text', 'type' => 'MEDIUMTEXT' ),
            )
        );

        return $tableDefinition;
    }

//    function getClassName() {
//        return "MockNote";
//    }

//    function __construct(){
//        //$this->dbConnection = $dbConnection;
//        $this->initTableDefinition($this->tableDefinition);
//    }
}

 