<?php



use Intahwebz\TableMap\SQLQueryFactory;

use Intahwebz\TableMap\Tests\DTO\EmailSQLTableDTO;
use Intahwebz\TableMap\Tests\DTO\UserTableDTO;

use Intahwebz\TableMap\TableMapWriter;


class TableMap_JoinsTest extends \PHPUnit_Framework_TestCase {

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
            //'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $provider = createProvider($mocks);

        //This dumps all tables
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', []);


        /** @var  $tablesToUpgrade \Intahwebz\TableMap\SQLTableMap[] */
        $tablesToUpgrade = [
            new Intahwebz\TableMap\Tests\UserTable(),
            new Intahwebz\TableMap\Tests\EmailSQLTable(),
          //  new Intahwebz\TableMap\Tests\EmailUserJoinTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', $tablesToUpgrade);

        $tableMapWriter = new TableMapWriter();
        
        foreach ($tablesToUpgrade as $tableToUprade) {
            $tableMapWriter->generateObjectFile(
                $tableToUprade,
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

        $this->sqlQueryFactory = $this->provider->make('Intahwebz\TableMap\SQLQueryFactory');
    }

    function testInsertWithAutoJoinTable() {
        $sqlQuery = $this->sqlQueryFactory->create();

        $userTable = $this->provider->make(\Intahwebz\TableMap\Tests\UserTable::class);
        $emailTable = $this->provider->make(\Intahwebz\TableMap\Tests\EmailSQLTable::class);

        $userDTO = new UserTableDTO(null, null, "Dan", "Ackroyd");
        $userID = $userDTO->insertInto($sqlQuery, $userTable);

        $emailAddress = "email2@example.com";

        $emailDTO = new EmailSQLTableDTO(null, $emailAddress);
        $sqlQuery->insertIntoMappedTable($emailTable, $emailDTO, ['userID' => $userID]);

        $sqlReadQuery = $this->sqlQueryFactory->create();

        $sqlReadQuery->table($userTable)->wherePrimary($userID);
        $sqlReadQuery->table($emailTable);

        $result1 = $sqlReadQuery->fetch();
        $this->assertEquals(1, count($result1), "Failed to retrieve single result.");
        $entry = $result1[0];

        $this->assertEquals($emailAddress, $entry['email.address'], "Failed to retrieve email address correctly.");

        $sqlReadQuery = $this->sqlQueryFactory->create();

        $sqlReadQuery->table($emailTable);
        $sqlReadQuery->table($userTable)->wherePrimary($userID);

        $result2 = $sqlReadQuery->fetch();
        $this->assertEquals(1, count($result2), "Failed to retrieve single result.");
        $entry = $result2[0];

        $this->assertEquals($emailAddress, $entry['email.address'], "Failed to retrieve email address correctly.");
    }
}
 