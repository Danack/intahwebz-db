<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\Relation;

class commentRelation extends Relation {

    function getDefinition() {
        return array(
            'type' => Relation::SELF_CLOSURE,
            'owning' => 'Intahwebz\TableMap\Tests\Table\MockCommentTable',
            'inverse' => 'Intahwebz\TableMap\Tests\Table\MockCommentTable',
            'tableName' => 'Intahwebz\TableMap\Tests\Table\MockCommentTreePathTable'
        );
    }
}