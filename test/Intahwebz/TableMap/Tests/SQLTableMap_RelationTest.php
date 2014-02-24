<?php

namespace Intahwebz\TableMap\Tests;


use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\TableMapWriter;

class SQLTableMap_RelationTest extends \PHPUnit_Framework_TestCase {

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

        $tablesToUpgrade = [
            new \Intahwebz\TableMap\Tests\Table\Person(),
            new \Intahwebz\TableMap\Tests\Table\PhoneNumber(),
            new \Intahwebz\TableMap\Tests\Table\PersonPhoneNumberJoinTable()
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
    }

    function setUp() {
        $this->provider = createProvider();
        $this->sqlQueryFactory = $this->provider->make('Intahwebz\TableMap\SQLQueryFactory');
    }

    function testOneToMany_Insert() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $personTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\Person');
        $phoneNumbertable = $this->provider->make('Intahwebz\TableMap\Tests\Table\PhoneNumber');

        $personDTO = new \Intahwebz\TableMap\Tests\DTO\PersonDTO;
        $personDTO->setName('Danack');
        $personDTO->insertInto($sqlQuery, $personTable);

        $mobileNumber = new \Intahwebz\TableMap\Tests\DTO\PhoneNumberDTO;
        $mobileNumber->setPhoneNumber('07000111222');

        $sqlQuery->insertIntoMappedTable(
            $phoneNumbertable, 
            $mobileNumber, 
            ['personID' => $personDTO->personID]
        );


        $mobileNumber = new \Intahwebz\TableMap\Tests\DTO\PhoneNumberDTO;
        $mobileNumber->setPhoneNumber('07000111333');

        $sqlQuery->insertIntoMappedTable(
            $phoneNumbertable,
            $mobileNumber,
            ['personID' => $personDTO->personID]
        );
    }


    function testOneToMany_fetch() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $personTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\Person');
        $phoneNumberTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\PhoneNumber');
        $sqlQuery->table($personTable)->whereColumn('personID', 1);
        $sqlQuery->table($phoneNumberTable);

        $result = $sqlQuery->fetchObjects(true);

        $this->assertInstanceOf(
            'Intahwebz\\TableMap\\Tests\\DTO\\PersonDTOXPhoneNumberDTO',
            $result
        );

        /** @var $result \Intahwebz\TableMap\Tests\DTO\PersonDTOXPhoneNumberDTO */
        $this->assertEquals($result->personDTO->name, 'Danack');
        $this->assertCount(2, $result->phoneNumberDTOCollection);
    }

}

