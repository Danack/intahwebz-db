<?php

namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\TableMapWriter;


class SQLTableMapTreesTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Auryn\Provider
     */
    private $provider;

    /**
     * @var SQLQueryFactory
     */
    private $sqlQueryFactory;
    
    /** @var  \Intahwebz\TableMap\SQLTableMap */
    private $treeTable;

    private  $treeDataSet = [
        //[1, null,  "Fran What’s the cause of this bug?"],
        [1, 1,  "Fran What’s the cause of this bug?"],
        [2, 1, "Ollie I think it’s a null pointer."],
        [3, 2, "Fran No, I checked for that."],
        [4, 1 , "Kukla We need to check for invalid input."],
        [5, 4, "Ollie Yes, that’s a bug."],
        [6, 4, "Fran Yes, please add a check for invalid input."],
        [7, 6, "Kukla That fixed it."],
    ];

    static function setUpBeforeClass() {
    }

    function setUp() {
        $mocks = [
        ];

        $this->provider = createProvider($mocks);

        $provider = createProvider();
        //This dumps all tables
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', []);

        $tablesToUpgrade = [
            new \Intahwebz\TableMap\Tests\Table\MockCommentTable(),
            new \Intahwebz\TableMap\Tests\Table\MockCommentTreePathTable(),
        ];

        /** @var $dbSync \Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', $tablesToUpgrade);


        $tableMapWriter = new TableMapWriter();
        foreach($tablesToUpgrade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $tableMapWriter->generateObjectFile(
                $knownTable,
                realpath(__DIR__)."/DTO/",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }

        $this->sqlQueryFactory = $this->provider->make('Intahwebz\TableMap\SQLQueryFactory');

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockCommentTable');

        foreach ($this->treeDataSet as $dataSet) {
            $values = array();
            $values['parent'] = $dataSet[1];
            $values['text'] = $dataSet[2];
            $sqlQuery->insertIntoMappedTable($table, $values);
        }

        $this->treeTable = $table;
    }

    function testTreeSet() {
        $sqlQuery = $this->sqlQueryFactory->create();

        $table = $this->treeTable;

        $stuff = $sqlQuery->getAncestors($table, 6);        
        $this->assertCount(3, $stuff);
        
        $ancestorIDs = array();
        foreach ($stuff as $item) {
            $ancestorIDs[] = $item['mockCommentID'];
        }

        $this->assertContains(6, $ancestorIDs);
        $this->assertContains(4, $ancestorIDs);
        $this->assertContains(1, $ancestorIDs);
        
        $stuff = $sqlQuery->getDescendants($table, 2);
        
        $descendentantIDs = array();
        foreach ($stuff as $item) {
            $descendentantIDs[] = $item['mockCommentID'];
        }
        $this->assertCount(2, $stuff);
        $this->assertContains(2, $descendentantIDs);
        $this->assertContains(3, $descendentantIDs);

        $stuff2 = $sqlQuery->getDescendants($table, 2, 1);
        $this->assertCount(1, $stuff2);

        $beforeDelete = $sqlQuery->getDescendants($table, 2);
        count($beforeDelete);
        $sqlQuery->deleteDescendants($table, 2);
        $afterDelete = $sqlQuery->getDescendants($table, 2);
        $count = count($beforeDelete) - count($afterDelete);

        $this->assertEquals(2, $count, "Failied to remove 2 + 3. ");
    }


    function testTreeDelete() {
        $sqlQuery = $this->sqlQueryFactory->create();

        $table = $this->treeTable;
        $comments1 = $sqlQuery->getDescendants($table, 1);
        $sqlQuery->deleteNode($this->treeTable, 4);
        $comments2 = $sqlQuery->getDescendants($table, 1);
        $this->assertEquals(1, (count($comments1) - count($comments2)));
    }

}
 