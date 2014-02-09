<?php


namespace Intahwebz\TableMap\Tests\Table;


class PhoneNumber extends \Intahwebz\TableMap\SQLTableMap {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema'        => 'mocks',
            'tableName'     => 'phoneNumber',
            'columns'       => array(
                ['phoneNumberID', 'primary' => true, 'autoInc' => true],
                ['phoneNumber'],
            ),

            'relations' => [
                \Intahwebz\TableMap\Tests\Table\PersonPhoneNumberRelation::class
            ]
        );

        return $tableDefinition;
    }
}


//CREATE TABLE Phonenumber (
//    id INT AUTO_INCREMENT NOT NULL,
//PRIMARY KEY(id)
//) ENGINE = InnoDB;