<?php

use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\TableMapWriter;



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
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', []);

        /** @var Intahwebz\TableMap\TableMap[] $tablesToUpgrade */
        $tablesToUpgrade = [
            new Intahwebz\TableMap\Tests\Table\MockContentSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockNoteSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockHashSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockCommentSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockCommentTreePathSQLTable(),
            
            new Intahwebz\TableMap\Tests\Table\ContentTable(),
            new Intahwebz\TableMap\Tests\Table\TagTable()
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', $tablesToUpgrade);

        $tableMapWriter = new TableMapWriter();

        foreach($tablesToUpgrade as $knownTable){
            
            $dtoNamespace = $knownTable->getDTONamespace();
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $tableMapWriter->generateObjectFile(
                $knownTable,
                realpath(__DIR__)."/DTO/",
                $dtoNamespace
            );
        }
    }

    function setUp() {
        $mocks = [
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $this->provider = createProvider($mocks);

        $this->sqlQueryFactory = $this->provider->make('Intahwebz\TableMap\SQLQueryFactory');
    }
    
    function testBadTable() {
        $this->setExpectedException('UnexpectedValueException');
        new Intahwebz\TableMap\Tests\Table\BadTableMissingName();
    }
    
    function testIndex() {
        new \Intahwebz\TableMap\Tests\Table\TagTable();
        //TODO - check that the index is actually used?
    }
    
    function testInsertDTO() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $contentDTO = new Intahwebz\TableMap\Tests\DTO\MockContentDTO;
        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertGreaterThan(0, $insertID, "Insert failed?");

        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertNotNull($insertID, "Insert is null");
    }


    function testInsertArray() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');

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
        $insertTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockHashSQLTable');

        $username = 'JohnDoe';
        $password = '12345';

        $sqlInsertQuery->insertIntoMappedTable($insertTable, ['username' => $username, 'passwordHash' => $password]);

        $sqlVerifyQuery = $this->sqlQueryFactory->create();
        $verifyTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockHashSQLTable');
        $queriedTable = $sqlVerifyQuery->table($verifyTable);
        $queriedTable->whereColumn('username', $username);

        $mockHashSQLTableDTO = $sqlVerifyQuery->fetchSingle('\Intahwebz\TableMap\Tests\DTO\MockHashDTO');

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
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);
        $contentArray = $sqlQuery->fetch();

        if (isset($contentArray[0]) == false) {
            return null;
        }

        return castToObject('\Intahwebz\TableMap\Tests\DTO\MockNoteDTO', $contentArray[0]);
    }



    function testUpdate() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
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

    function testCount() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);
        
        $result = $sqlQuery->count();
        $this->assertEquals($result, 1);
    }
    

    function testGroup() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
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
        $this->setExpectedException('\BadFunctionCallException');
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $tableAlias = $sqlQuery->table($table);
        $sqlQuery->where($tableAlias->getAliasedPrimaryColumn()." = ?", 1);
    }

    function testOrder() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $tableAlias = $sqlQuery->table($table);

        $sqlQuery->order($tableAlias, 'mockContentID', 'DESC');

        $contentArray = $sqlQuery->fetch();
        $this->assertEquals(2, count($contentArray));        
        $this->assertEquals(2, $contentArray[0]['mockContent.mockContentID']);
    }


    function testLimit() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $sqlQuery->table($table);

        $sqlQuery->limit(50);
        $sqlQuery->offset(1);

        $contentArray = $sqlQuery->fetch();
        $this->assertEquals(1, count($contentArray));
        $this->assertEquals(2, $contentArray[0]['mockContent.mockContentID']);
    }

    function testLimitMissing() {
        $this->setExpectedException('\RuntimeException');
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $sqlQuery->table($table);
        $sqlQuery->offset(2);
    }

    function testNull() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $noteTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $contentTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $tableAlias = $sqlQuery->table($contentTable);
        $sqlQuery->nullTable($tableAlias, $noteTable);
        $sqlQuery->fetch();
    }

    function testColumns() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $aliasedTable = $sqlQuery->table($table)->whereColumn('mockNoteID', 1);
        $sqlQuery->select($aliasedTable, "mockNoteID");
        $sqlQuery->fetch();
    }

    function testColumnIn() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $sqlQuery->table($table)->whereColumnIn('mockNoteID', [1, 2, 3]);
        $sqlQuery->fetch();
    }


    function testColumnFunction() {
        $year = date('Y');
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $sqlQuery->table($table)->whereColumnFunction('year', 'datestamp', $year);
        $sqlQuery->fetch();
    }

    function testRandSelect() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable');

        for ($x=0 ; $x<50 ; $x++) {
            $data['title'] = generateRandomString(10);
            $data['text'] = generateRandomString(50);
            $sqlQuery->insertIntoMappedTable($table, $data);
        }

        for ($x=0 ; $x<60 ; $x++) {
            $sqlQuery = $this->sqlQueryFactory->create();
            $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable');
            $sqlQuery->table($table)->rand();
            $result = $sqlQuery->fetch();

            $this->assertEquals(1, count($result));
            //TODO - how to test random results.
            //echo $result[0]['mockRandData.mockRandDataID'].", ";
        }
    }

    function testInsertDTODirectly() {
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $contentDTO = new Intahwebz\TableMap\Tests\DTO\MockContentDTO();
        $insertID =$this->sqlQueryFactory->insertIntoMappedTable($table, $contentDTO);
        $this->assertGreaterThan(0, $insertID, "Insert failed?");
    }
    
    
    function testFoo() {

        $contentTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\ContentTable');
        $tagTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\TagTable');
        
        
        $sqlQuery = $this->sqlQueryFactory->create();
        
        
        $tagsForContentArray = [
            ['dilbert', 'phb', 'funny'],
            ['dilbert', 'morag', 'IT'],
            ['dilbert', 'wally', 'sally', 'funny'],
        ];
        
        
        foreach ($tagsForContentArray as $tagsForContent) {

            $contentDTO = new Intahwebz\TableMap\Tests\DTO\ContentDTO();

            $contentID = $contentDTO->insertInto($sqlQuery, $contentTable);

            foreach ($tagsForContent as $tag) {
                $tagData = array(
                    'contentID' => $contentID,
                    'text'      => $tag,
                );
    
                $tagID = $this->sqlQueryFactory->insertIntoMappedTable($tagTable, $tagData);
            }
        }

        $tags = ['dilbert', 'funny'];
        
        $contentTableMap = $sqlQuery->table($contentTable);
        $sqlQuery->table($tagTable)->whereColumnIn('text', $tags);
        $suggestTableMap = $sqlQuery->table($tagTable, $contentTableMap);
        $sqlQuery->select($suggestTableMap, "text");
        $groupName = $sqlQuery->group($suggestTableMap, "text");

        $sqlQuery->order(null, $groupName, 'desc');
        $sqlQuery->limit(5 + count($tags));
        $resultArray = $sqlQuery->fetch();
        $suggestedTags = array();

        foreach($resultArray as $result){
            $suggestion = $result[$suggestTableMap->alias.".text"];
            $count = $result[$suggestTableMap->alias.".count"];
            $suggestedTags[$suggestion] = $count;
        }

        //We don't want to suggest tags that already exist for the content.
        foreach($tags as $tag){
            unset($suggestedTags[$tag]);
        }

        //TODO - add check that results are sane.
//        'phb' => int(32)
//          'wally' =>   int(24)
//          'sally' =>   int(24)
//          'morag' =>   int(16)
//          'IT' =>   int(16)
    }
}
 