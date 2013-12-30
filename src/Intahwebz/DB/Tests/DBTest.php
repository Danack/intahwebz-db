<?php




//DataNotSetException


class DBSyncTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \Auryn\Provider
     */
    private $provider;

    /**
     * @var \Intahwebz\DB\MySQLiConnection
     */
    private $dbConnection;

    static function setUpBeforeClass() {
        Intahwebz\DB\DBFunctions::load();
    }

    protected function setUp(){

        $this->provider = createProvider([]);
        $this->dbConnection = $this->provider->make(Intahwebz\DB\Connection::class);


        $mocks = [
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $provider = createProvider($mocks);

        // Intahwebz\TableMap\Tests\MockContentSQLTable::class
        // Intahwebz\TableMap\Tests\MockNoteSQLTable::class

        $tablesToUprade = [
            new Intahwebz\DB\Tests\MockFileTable(),
//            new Intahwebz\TableMap\Tests\MockNoteSQLTable(),
//            new Intahwebz\TableMap\Tests\MockHashSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);
    }



    function testBasic() {
        $queryString = "insert into mocks.mockFile (title, text) values (?, ?);";
        $statementWrapper = $this->dbConnection->prepareStatement($queryString);

        $null = null;
        $title = 'Test title';

        $statementWrapper->bindParam('ib', $title, $null);

        $statementWrapper->sendFile(1, realpath(__DIR__.'/testFile.txt'));
        $statementWrapper->execute();

        $null = null;
//        $title = 'Large string title';
        $statementWrapper->sendBigString(1, "This is'nt a very large string, but it could be.");


        $statementWrapper->close();
    }



    function test_getLikeQueryForMatching() {
        $plainTextSearchArray = array('Dog', 'cat', 'bat');
        $likeQuery = getLikeQueryForMatching('message', $plainTextSearchArray);

        $this->assertEquals("message like ? or message like ? or message like ?",$likeQuery, "LikeQuery not generatedCorrectly" );
    }



    function test_getDayNumberWithOffset() {

        $dayNumberToday = getDayNumberWithOffset($this->dbConnection, 200);
        $dayNumberTomorrow = getDayNumberWithOffset($this->dbConnection, 200 + (3600 * 24));

        $this->assertEquals(1, $dayNumberTomorrow - $dayNumberToday);
    }

//    function test_getMysqlTimeWithOffset() {
//        $dateTime = getMysqlTimeWithOffset($this->dbConnection, 0);
//
//        $sqlTimestamp = strtotime($dateTime);
//        $mktimeTimestap = time();
//
//        //TODO - how to actually test these - would need to setup mockDBConnection to return exact time.
//    }

}