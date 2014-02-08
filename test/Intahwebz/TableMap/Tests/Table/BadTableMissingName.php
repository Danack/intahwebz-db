<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class BadTableMissingName extends SQLTableMap {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema'        => 'mocks',

            'columns'       => array(
                ['emailID', 'primary' => true, 'autoInc' => true],
                ['address'],
            ),
        );

        return $tableDefinition;
    }
}

 