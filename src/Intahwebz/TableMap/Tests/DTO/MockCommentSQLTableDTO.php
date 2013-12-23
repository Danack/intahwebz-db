<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockCommentSQLTableDTO {
	public $mockCommentID;
	public $text;
	public $parent;

	public function __construct($mockCommentID = null, $text = null, $parent = null) {
		$this->mockCommentID = $mockCommentID;
		$this->text = $text;
		$this->parent = $parent;
	} 
	function mockCommentID($mockCommentID) { 
		$this->mockCommentID = $mockCommentID;
	}

	function text($text) { 
		$this->text = $text;
	}

	function parent($parent) { 
		$this->parent = $parent;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockCommentSQLTable \Intahwebz\TableMap\Tests\MockCommentSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockCommentSQLTable $mockCommentSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockCommentSQLTable, $data);
	$this->mockCommentID = $insertID;

        return $insertID;
    }
}


