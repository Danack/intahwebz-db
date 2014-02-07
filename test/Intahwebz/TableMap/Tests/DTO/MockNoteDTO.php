<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockNoteDTO {
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
    function setMockNoteID($mockNoteID) { 
        $this->mockNoteID = $mockNoteID;
    }

    function setMockContentID($mockContentID) { 
        $this->mockContentID = $mockContentID;
    }

    function setTitle($title) { 
        $this->title = $title;
    }

    function setText($text) { 
        $this->text = $text;
    }



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockNoteDTO \Intahwebz\TableMap\Tests\Table\MockNoteSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\Table\MockNoteSQLTable $mockNoteDTO){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockNoteDTO, $data);
    $this->mockNoteID = $insertID;

        return $insertID;
    }
}


