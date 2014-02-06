<?php


namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLTableMap;


class EmailSQLTable extends SQLTableMap {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema'        => 'mocks',
            'tableName'     => 'email',
            'columns'       => array(
                ['emailID', 'primary' => true, 'autoInc' => true],
                ['address'],
            ),

            'relatedTables' => [
                ['one-to-many', 'Intahwebz\TableMap\Tests\UserTable']
            ]
        );

        return $tableDefinition;
    }
}

 