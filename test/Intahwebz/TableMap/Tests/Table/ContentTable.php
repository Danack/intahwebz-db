<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class ContentTable extends SQLTableMap {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'content',
            'columns' => array(
                array('contentID', 'primary' => true, 'autoInc' => true ),
                array('datestamp', 'type' => 'd'),
            ),
        );

        return $tableDefinition;
    }
}

 