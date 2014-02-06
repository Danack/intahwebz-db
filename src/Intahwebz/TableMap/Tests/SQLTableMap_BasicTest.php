<?php

use Intahwebz\TableMap\SQLQueryFactory;

class SQLTableMap_BasicTest extends \PHPUnit_Framework_TestCase {

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

        $tablesToUpgrade = [
            new Intahwebz\TableMap\Tests\MockContentSQLTable(),
            new Intahwebz\TableMap\Tests\MockNoteSQLTable(),
            new Intahwebz\TableMap\Tests\MockHashSQLTable(),
            new Intahwebz\TableMap\Tests\MockRandDataSQLTable(),
            new Intahwebz\TableMap\Tests\MockCommentSQLTable(),
            new Intahwebz\TableMap\Tests\MockCommentTreePathSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', $tablesToUpgrade);

        foreach($tablesToUpgrade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $knownTable->generateObjectFile(
                realpath(__DIR__)."/DTO/",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }
    }

    function setUp() {
        $mocks = [
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $this->provider = createProvider($mocks);

        $this->sqlQueryFactory = $this->provider->make(Intahwebz\TableMap\SQLQueryFactory::class);
    }

    function testInsertDTO() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $contentDTO = new Intahwebz\TableMap\Tests\DTO\MockContentDTO;
        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertGreaterThan(0, $insertID, "Insert failed?");

        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertNotNull($insertID, "Insert is null");
    }






    function testInsertArray() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);

        $data = [
            'mockContentID' => 1,
            'title' => "My first Note",
            'text' => "Deep v 3 wolf moon bitters, ugh gluten-free disrupt pickled kale chips Banksy tattooed hella tofu Intelligentsia. Trust fund deep v PBR literally, 8-bit paleo DIY Odd Future cornhole polaroid try-hard hella butcher single-origin coffee sriracha. DIY mixtape irony messenger bag, tattooed banjo bicycle rights. Raw denim meggings pour-over bitters. Pour-over forage seitan lomo bicycle rights, flexitarian organic. Hoodie actually gluten-free VHS YOLO, polaroid Bushwick Pinterest Neutra Intelligentsia synth pour-over master cleanse fanny pack fingerstache. Organic keffiyeh trust fund, deep v ugh flexitarian narwhal freegan tofu.",
        ];

        $insertID = $sqlQuery->insertIntoMappedTable($table, $data);
        $this->assertGreaterThan(0, $insertID, "Insert failed?");
    }


    function testInsertHash() {
        $sqlInsertQuery = $this->sqlQueryFactory->create();
        $insertTable = $this->provider->make(Intahwebz\TableMap\Tests\MockHashSQLTable::class);

        $username = 'JohnDoe';
        $password = '12345';

        $sqlInsertQuery->insertIntoMappedTable($insertTable, ['username' => $username, 'passwordHash' => $password]);

        $sqlVerifyQuery = $this->sqlQueryFactory->create();
        $verifyTable = $this->provider->make(Intahwebz\TableMap\Tests\MockHashSQLTable::class);
        $queriedTable = $sqlVerifyQuery->table($verifyTable);
        $queriedTable->whereColumn('username', $username);

        $mockHashSQLTableDTO = $sqlVerifyQuery->fetchSingle(\Intahwebz\TableMap\Tests\DTO\MockHashDTO::class);

        $validated = password_verify($password, $mockHashSQLTableDTO->passwordHash);

        $this->assertTrue($validated, 'Password hash is borked');

        $options = array('cost' => 12);
        $rehash = password_needs_rehash(
            $mockHashSQLTableDTO->passwordHash,
            PASSWORD_BCRYPT,
            $options
        );

        $this->assertTrue($rehash, "Required rehash not detected correctly.");
    }



    function testSimplest() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);
        $contentArray = $sqlQuery->fetch();

        if (isset($contentArray[0]) == false) {
            return null;
        }

        return castToObject(\Intahwebz\TableMap\Tests\DTO\MockNoteDTO::class, $contentArray[0]);
    }



    function testUpdate() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);

        $noteParams = array(
            'columns' => array(
                'title' => "A new title",
                'text' => "An updated piece of text.",
            ),

            'where' => array(
                'mockNoteID' => 1
            )
        );

        $sqlQuery->updateMappedTable($table, $noteParams);
    }


    function testGroup() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $tableAlias = $sqlQuery->table($table)->whereColumn('mockNoteID', 1);

        $sqlQuery->group($tableAlias, 'mockNoteID');

        $noteParams = array(
            'columns' => array(
                'title' => "A new title",
                'text' => "An updated peice of text.",
            ),

            'where' => array(
                'mockNoteID' => 1
            )
        );

        $sqlQuery->updateMappedTable($table, $noteParams);
    }


    function testMissingTypeException() {
        $this->setExpectedException(\BadFunctionCallException::class);
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $tableAlias = $sqlQuery->table($table);
        $sqlQuery->where($tableAlias->getAliasedPrimaryColumn()." = ?", 1);
    }
    
    

    function testOrder() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $tableAlias = $sqlQuery->table($table);

        $sqlQuery->order($tableAlias, 'mockContentID', 'DESC');

        $contentArray = $sqlQuery->fetch();
        $this->assertEquals(2, count($contentArray));        
        $this->assertEquals(2, $contentArray[0]['mockContent.mockContentID']);
    }


    function testLimit() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $sqlQuery->table($table);

        $sqlQuery->limit(50);
        $sqlQuery->offset(1);

        $contentArray = $sqlQuery->fetch();

        $this->assertEquals(1, count($contentArray));
        $this->assertEquals(2, $contentArray[0]['mockContent.mockContentID']);
    }

    function testLimitMissing() {
        $this->setExpectedException(\RuntimeException::class);

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $sqlQuery->table($table);

        $sqlQuery->offset(2);
    }


    function testNull() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $noteTable = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $contentTable = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);

        $tableAlias = $sqlQuery->table($contentTable);

        $sqlQuery->nullTable($tableAlias, $noteTable);

        //$contentArray =
            $sqlQuery->fetch();
        //TODO - actually check the contents
    }




    function testColumns() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $aliasedTable = $sqlQuery->table($table)->whereColumn('mockNoteID', 1);
        $sqlQuery->select($aliasedTable, "mockNoteID");
        //$sqlQuery->addColumn($aliasedTable, 'mockNoteID');
        //$contentArray =
            $sqlQuery->fetch();
        //var_dump($contentArray);
    }



    function testSelfJoin() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);

        $sqlQuery->table($table);
        $sqlQuery->fetch();
    }




    function testColumnIn() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        //$aliasedTable =
            $sqlQuery->table($table)->whereColumnIn('mockNoteID', [1, 2, 3]);

        //$sqlQuery->addColumn($aliasedTable, 'mockNoteID');
        //$contentArray =
            $sqlQuery->fetch();
        //var_dump($contentArray);
    }


    function testColumnFunction() {
        $year = date('Y');
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        //$aliasedTable =
            $sqlQuery->table($table)->whereColumnFunction('year', 'datestamp', $year);
        //$contentArray =
            $sqlQuery->fetch();
    }





    function testRandSelect() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockRandDataSQLTable::class);

        for ($x=0 ; $x<50 ; $x++) {
            $data['title'] = generateRandomString(10);
            $data['text'] = generateRandomString(50);
            $sqlQuery->insertIntoMappedTable($table, $data);
        }

        for ($x=0 ; $x<60 ; $x++) {
            $sqlQuery = $this->sqlQueryFactory->create();
            $table = $this->provider->make(Intahwebz\TableMap\Tests\MockRandDataSQLTable::class);
            $sqlQuery->table($table)->rand();
            $result = $sqlQuery->fetch();

            $this->assertEquals(1, count($result));
            //TODO - how to test random results.
            //echo $result[0]['mockRandData.mockRandDataID'].", ";
        }
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



        $sqlQuery->getAncestors($table, 6);

    }

    function testInsertDTODirectly() {

        //$sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockContentSQLTable::class);
        $contentDTO = new Intahwebz\TableMap\Tests\DTO\MockContentDTO();
        //$insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $insertID =$this->sqlQueryFactory->insertIntoMappedTable($table, $contentDTO);

        $this->assertGreaterThan(0, $insertID, "Insert failed?");
    }




    function testByObject() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\MockNoteSQLTable::class);
        $sqlQuery->tableObject($table)->whereColumn('mockNoteID', 1);

        $sqlQuery->fetchObjects();
        
        exit(0);
        
//        $contentArray = $sqlQuery->fetch();
//
//        if (isset($contentArray[0]) == false) {
//            return null;
//        }

        

        //return castToObject(\Intahwebz\TableMap\Tests\DTO\MockNoteDTO::class, $contentArray[0]);
    }




//$closureTable = [
//'ancestor',
//'descendant',
//'depth'
//];

//        1 NULL Fran What’s the cause of this bug?
//        2 1 Ollie I think it’s a null pointer.
//        3 2 Fran No, I checked for that.
//                                   4 1 Kukla We need to check for invalid input.
//
//
//        5 4 Ollie Yes, that’s a bug.
//        6 4 Fran Yes, please add a check.
//        7 6 Kukla That fixed it.



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
 