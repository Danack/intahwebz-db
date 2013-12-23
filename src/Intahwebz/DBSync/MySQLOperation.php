<?php

namespace Intahwebz\DBSync;


class MySQLOperation{

	private $upgradeSQL = NULL;

	public $operationType = FALSE;

	public $comment;

	public $count;

	private static $instanceCount = 0;


	function __construct($upgradeSQL, $operationType, $comment = ""){
		$this->upgradeSQL = $upgradeSQL;
		$this->operationType = $operationType;
		$this->comment = $comment;

		$this->count = self::$instanceCount;
		self::$instanceCount++;
	}

	function	getOperationType(){
		if($this->operationType == FALSE){
			echo "Unknown operation type - aborting.";
			exit(0);
		}

		return	$this->operationType;
	}

	function 	getUpgradeSQL(){
		return	$this->upgradeSQL;
	}
}

