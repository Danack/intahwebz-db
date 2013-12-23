<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockNoteSQLTableDTO {
	public $mockNoteID;
	public $mockContentID;
	public $title;
	public $text;

	public function __construct($mockNoteID = null, $mockContentID = null, $title = null, $text = null) {
		$this->mockNoteID = $mockNoteID;
		$this->mockContentID = $mockContentID;
		$this->title = $title;
		$this->text = $text;
	} 
	function mockNoteID($mockNoteID) { 
		$this->mockNoteID = $mockNoteID;
	}

	function mockContentID($mockContentID) { 
		$this->mockContentID = $mockContentID;
	}

	function title($title) { 
		$this->title = $title;
	}

	function text($text) { 
		$this->text = $text;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockNoteSQLTable \Intahwebz\TableMap\Tests\MockNoteSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockNoteSQLTable $mockNoteSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockNoteSQLTable, $data);
	$this->mockNoteID = $insertID;

        return $insertID;
    }
}


