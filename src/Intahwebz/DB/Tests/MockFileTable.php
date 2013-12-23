<?php


namespace Intahwebz\DB\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockFileTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockFile',
            'columns' => array(
                array('mockFileID', 'primary' => true, 'autoInc' => true ),
                array('title'),
                array('text', 'type' => 'MEDIUMTEXT' ),
            )
        );

        return $tableDefinition;
    }

    function getClassName() {
        return "MockNote";
    }

}

 