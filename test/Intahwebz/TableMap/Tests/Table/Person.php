<?php


namespace Intahwebz\TableMap\Tests\Table;


class Person extends \Intahwebz\TableMap\SQLTableMap {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema'        => 'mocks',
            'tableName'     => 'person',
            'columns'       => array(
                ['personID', 'primary' => true, 'autoInc' => true],
                ['name'],
            ),
            'relations' => [
                \Intahwebz\TableMap\Tests\Table\PersonPhoneNumberRelation::class
            ]
        );

        return $tableDefinition;
    }
}
