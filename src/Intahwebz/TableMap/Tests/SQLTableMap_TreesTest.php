<?php

use Intahwebz\TableMap\SQLQueryFactory;

class SQLTableMap_TreesTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Auryn\Provider
     */
    private $provider;

    /**
     * @var SQLQueryFactory
     */
    private $sqlQueryFactory;

    static function setUpBeforeClass() {

        $mocks = [
        ];

        $provider = createProvider($mocks);
        //This dumps all tables
        $dbSync = $provider->make(\Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', []);

        $tablesToUprade = [
            new Intahwebz\TableMap\Tests\MockCommentSQLTable(),
            new Intahwebz\TableMap\Tests\MockCommentTreePathSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);


        
        foreach($tablesToUprade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $knownTable->generateObjectFile(
                realpath(__DIR__)."/DTO/",
                "DTO.php",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }
    }

    function setUp() {
        $mocks = [
        ];

        $this->provider = createProvider($mocks);

        $this->sqlQueryFactory = $this->provider->make(Intahwebz\TableMap\SQLQueryFactory::class);
    }


    function testTreeSet() {

        $dataSets = [
            //[1, null,  "Fran What’s the cause of this bug?"],
            [1, 1,  "Fran What’s the cause of this bug?"],
            [2, 1, "Ollie I think it’s a null pointer."],
            [3, 2, "Fran No, I checked for that."],
            [4, 1 , "Kukla We need to check for invalid input."],
            [5, 4, "Ollie Yes, that’s a bug."],
            [6, 4, "Fran Yes, please add a check."],
            [7, 6, "Kukla That fixed it."],
        ];

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockCommentSQLTable::class);

        foreach ($dataSets as $dataSet) {
            $values = array();
            $values['parent'] = $dataSet[1];
            $values['text'] = $dataSet[2];
            $sqlQuery->insertIntoMappedTable($table, $values);
        }

        $stuff = $sqlQuery->getAncestors($table, 6);        
        $this->assertCount(3, $stuff);

        
        $ancestorIDs = array();
        foreach ($stuff as $item) {
            $ancestorIDs[] = $item['mockCommentID'];
        }

        $this->assertContains(6, $ancestorIDs);
        $this->assertContains(4, $ancestorIDs);
        $this->assertContains(1, $ancestorIDs);
        
        $stuff = $sqlQuery->getDecendants($table, 2);
        
        $descendentantIDs = array();
        foreach ($stuff as $item) {
            $descendentantIDs[] = $item['mockCommentID'];
        }
        $this->assertCount(2, $stuff);
        $this->assertContains(2, $descendentantIDs);
        $this->assertContains(3, $descendentantIDs);


        $beforeDelete = $sqlQuery->getDecendants($table, 2);
        
        $sqlQuery->deleteDecendants($table, 2);
        $afterDelete = $sqlQuery->getDecendants($table, 2);
        
        $this->assertEquals(2, count($beforeDelete) - count($afterDelete), "Failied to remove 2 + 3. ");
    }


    /*




    //Get ancestors of comment #6
    select c.* from Comments c
    join TreePaths t
    on (c.comment_id = t.ancestor)
    where t.descendant = 6;

    //Get descendants of comment #4
    select c.* from Comments c
    join treePaths t
    on (c.comment_id = t.descendant)
    where t.ancestor = 4;



    //Gives first child of comment 4
        Select c.* from comments c
    join treepaths t
    on (c.comment_id = t.descendant)
    where t.ancestor = 4
    and t.depth = 1;


    // Delete child comment 7
    Delete from TreePaths
    where descendant = 7;


    //Delete comments under 4

    delete from treePaths where descendant in
    ( select descendant from TreePaths
    where ancestor = 4);

    //Or
     delete p from TreePaths P
    join TreePaths a using (descendant)
    where a.ancestor = 4;


//Used


        //Insert  anew child of commen #5

    Insert in to comments...

    Insert into treePaths (ancestor, descendant)
    values (8, 8);

    Insert into treePaths (ancestor, descendant)
    select ancestor, 8 from TreePaths
    where descendant = 5;




    */


//    function testSimplest() {
//
//        $sqlQuery = $this->sqlQueryFactory->create();
//
//        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
//
//        $sqlQuery->tableAlready($table)->whereColumn('noteID', 1);
//        $contentArray = $sqlQuery->fetch();
//
//        if (isset($contentArray[0]) == false) {
//            return null;
//        }
//
//        return castToObject(\Intahwebz\Content\Note::class, $contentArray[0]);
//    }

//    function testSearch() {
//        $sqlQuery = $this->sqlQueryFactory->create();
//        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
//        $tableAlias = $sqlQuery->tableAlready($table);
//
//
//
//
//        $contentArray = $sqlQuery->fetch();
//
////        if (isset($contentArray[0]) == false) {
////            return null;
////        }
//
//        //return castToObject(\Intahwebz\Content\Note::class, $contentArray[0]);
//    }

}
 