<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class EmailTable extends SQLTableMap {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema'        => 'mocks',
            'tableName'     => 'email',
            'columns'       => array(
                ['emailID', 'primary' => true, 'autoInc' => true],
                ['address'],
            ),

            'relations' => [
                \Intahwebz\TableMap\Tests\Table\emailuserprimaryEmailRelation::class
            ]
        );

        return $tableDefinition;
    }
}

 