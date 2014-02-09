<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\Relation;

class PersonPhoneNumberRelation extends Relation {

    function getDefinition() {
        return array(
            //One-To-Many, Unidirectional with Join Table
            'type' => Relation::ONE_TO_MANY_UNIDIRECTIONAL,
            'owning' => 'Intahwebz\TableMap\Tests\Table\PhoneNumber',
            'inverse' => 'Intahwebz\TableMap\Tests\Table\Person',
            'tableName' => 'Intahwebz\TableMap\Tests\Table\PersonPhoneNumberJoinTable'
        );
    }
}

