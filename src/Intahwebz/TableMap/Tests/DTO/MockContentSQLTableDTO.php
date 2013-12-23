<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockContentSQLTableDTO {
	public $mockContentID;
	public $datestamp;

	public function __construct($mockContentID = null, $datestamp = null) {
		$this->mockContentID = $mockContentID;
		$this->datestamp = $datestamp;
	} 
	function mockContentID($mockContentID) { 
		$this->mockContentID = $mockContentID;
	}

	function datestamp($datestamp) { 
		$this->datestamp = $datestamp;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockContentSQLTable \Intahwebz\TableMap\Tests\MockContentSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockContentSQLTable $mockContentSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockContentSQLTable, $data);
	$this->mockContentID = $insertID;

        return $insertID;
    }
}


