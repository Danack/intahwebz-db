<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class MockContentSQLTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockContent',
            'columns' => array(
                array('mockContentID', 'primary' => true, 'autoInc' => true ),
                array('datestamp', 'type' => 'd'),
            ),
        );
        return $tableDefinition;
    }
}

 