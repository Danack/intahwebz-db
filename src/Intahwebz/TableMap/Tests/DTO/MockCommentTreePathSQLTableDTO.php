<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockCommentTreePathSQLTableDTO {
	public $mockCommentTreePathID;
	public $ancestor;
	public $descendant;
	public $depth;

	public function __construct($mockCommentTreePathID = null, $ancestor = null, $descendant = null, $depth = null) {
		$this->mockCommentTreePathID = $mockCommentTreePathID;
		$this->ancestor = $ancestor;
		$this->descendant = $descendant;
		$this->depth = $depth;
	} 
	function mockCommentTreePathID($mockCommentTreePathID) { 
		$this->mockCommentTreePathID = $mockCommentTreePathID;
	}

	function ancestor($ancestor) { 
		$this->ancestor = $ancestor;
	}

	function descendant($descendant) { 
		$this->descendant = $descendant;
	}

	function depth($depth) { 
		$this->depth = $depth;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockCommentTreePathSQLTable \Intahwebz\TableMap\Tests\MockCommentTreePathSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockCommentTreePathSQLTable $mockCommentTreePathSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockCommentTreePathSQLTable, $data);
	$this->mockCommentTreePathID = $insertID;

        return $insertID;
    }
}


