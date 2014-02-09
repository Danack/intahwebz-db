<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class PersonPhoneNumberJoinTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'personPhoneNumber',
            'columns' => array(
                array('personID', 'type' => 'i', 'foreignKey' => 'person'),
                array('phoneNumberID', 'type' => 'i', 'foreignKey' => 'phoneNumber'),
            ),
        );
        return $tableDefinition;
    }
}