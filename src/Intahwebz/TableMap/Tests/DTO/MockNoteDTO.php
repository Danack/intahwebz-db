<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockNoteDTO {
	public $mockRandDataID;
	public $title;
	public $text;

	public function __construct($mockRandDataID = null, $title = null, $text = null) {
		$this->mockRandDataID = $mockRandDataID;
		$this->title = $title;
		$this->text = $text;
	} 
	function mockRandDataID($mockRandDataID) { 
		$this->mockRandDataID = $mockRandDataID;
	}

	function title($title) { 
		$this->title = $title;
	}

	function text($text) { 
		$this->text = $text;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockNote \Intahwebz\TableMap\Tests\MockRandDataSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockRandDataSQLTable $mockNote){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockNote, $data);
	$this->mockRandDataID = $insertID;

        return $insertID;
    }
}


