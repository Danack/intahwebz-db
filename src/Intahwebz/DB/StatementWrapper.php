<?php

namespace Intahwebz\DB;

use Intahwebz\Exception\UnsupportedOperationException;
use Psr\Log\LoggerInterface;

define('SERVER_REPORT_SQL_EXECUTE_TIMINGS', 'false');
define('SERVER_REPORT_SQL_EXECUTE_MINIMUM_TIME', 'false');

class StatementWrapper{

    var $createLine;

    /** @var \mysqli_stmt */
    var $statement;
    var $open;
    var $boundParameters;

    var $executedInternally = false;

    var $executeTime = 0;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    var $logger;

    static $activeStatements = array();

    static $statementsExecutedExternally = array();

    var $queryString = '';

    function __construct($statement, $createLine, LoggerInterface $logger){
        $this->createLine = $createLine;
        $this->statement = $statement;
        $this->open = true;

        $this->logger = $logger;
        if(defined('SKIP_MYSQL_LOGGING') == false || SKIP_MYSQL_LOGGING == false){
            self::$activeStatements[] = $this;
        }
    }

    /**
     * (PHP 5)<br/>
     * Fetch results from a prepared statement into the bound variables
     * @link http://php.net/manual/en/mysqli-stmt.fetch.php
     * @return bool
     */
    public function fetch () {
        return $this->statement->fetch();
    }

    function	setQueryString($queryString){
        $this->queryString = $queryString;
    }

    /**
     * @param $parameterArray Array of ($type, $reference)
     */
    function	bindParameterArray($parameterArray){
        $typesString = '';
        //$parameterCount = 0;
        $parametersArray = array();

        foreach($parameterArray as $parameterAndType){
            $typesString .= $parameterAndType[0];
            $parametersArray[] = $parameterAndType[1];
            //$parameterCount++;
        }

        $finalParamArray = array($typesString);
        $finalParamArray = array_merge($finalParamArray, $parametersArray);
        call_user_func_array(array($this->statement, "bind_param"), $finalParamArray);
    }


    function bindResult(/** @noinspection PhpUnusedParameterInspection */
        &$var0, &$var1, &$var2 = false, &$var3 = false, &$var4 = false, &$var5 = false,
                        &$var6 = false, &$var7 = false, &$var8 = false, &$var9 = false, &$var10 = false,
                        &$var11 = false, &$var12 = false, &$var13 = false, &$var14 = false, &$var15 = false,
                        &$var16 = false, &$var17 = false, &$var18 = false, &$var19 = false, &$var20 = false){

        $numberOfArguments = func_num_args();

        if($numberOfArguments > 21){
            $errorString = "Error: StatementWrapper::bindParam only supports up to 21 parameters, trying to set ".$numberOfArguments.".";

            throw new UnsupportedOperationException($errorString);
        }

        $arguments = array();

        for($count = 0; $count<$numberOfArguments; $count++){
            $varName = "var".$count;
            $arguments[] = &$$varName;
        }

        return call_user_func_array(array(&$this->statement, 'bind_result'), $arguments);
    }


    function	bindParam($types,
        /** @noinspection PhpUnusedParameterInspection */                        &$var1,
            /** @noinspection PhpUnusedParameterInspection */
                            &$var2 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var3 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var4 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var5 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var6 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var7 = false, /** @noinspection PhpUnusedParameterInspection */
                            &$var8 = false,
        /** @noinspection PhpUnusedParameterInspection */
                          &$var9 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var10 = false){

        $numberOfArguments = func_num_args();

        if($numberOfArguments >= 1){
            if($numberOfArguments != (1 + mb_strlen($types))){
                throw new UnsupportedOperationException("There is a mismatch in the number of types string being passed into the query and the actual number of types.");
            }
        }

        if($numberOfArguments > 11){
            $errorString = "Error: StatementWrapper::bindParam only supports up to 10 parameters, trying to set ".($numberOfArguments - 1).".";

            throw new UnsupportedOperationException($errorString);
        }

        $arguments = array();

        $arguments[] = $types;

        for($count = 1; $count<$numberOfArguments; $count++){
            $varName = "var".$count;
            $arguments[] = &$$varName;
        }

        $this->boundParameters = &$arguments;

        call_user_func_array(array(&$this->statement, 'bind_param'), $arguments);
    }


    function sendFile($parameterNumber, $filePath){

        $fp = fopen($filePath, "r");

        if($fp === false){
            throw new \Exception("Failed to open file [$filePath] for reading.");
        }

        $size = 0;

        while (!feof($fp)) {
            $this->statement->send_long_data($parameterNumber, fread($fp, 8192));

            $size += 8192;

            if($size > 4 * 1024 * 1024){
                throw new DBException("file is too large to upload, max size is 4 megabytes.");
            }
        }
        fclose($fp);

        return true;
    }

    function close(){
        $this->statement->close();
        $this->open = false;

        if(SERVER_REPORT_EXTERNAL_SQL_EXECUTE){
            if($this->executedInternally == false){
                if(defined('SKIP_MYSQL_LOGGING') == false || SKIP_MYSQL_LOGGING == false){
                    self::$statementsExecutedExternally[] = "Statement executed externally, created from ".$this->createLine." or statement created but not executed.";
                }
            }
        }
    }

    function getInsertID(){
        return $this->statement->insert_id;
    }

    static function finalise(){

        //echo "<h4>External Executes</h4>";

        foreach(self::$statementsExecutedExternally as $externalStatement){
            echo $externalStatement.'<br/>';
        }

        $totalTime = 0;

        $statementLineTimings = array();

        //echo "<h4>Statements left open</h4>";

        foreach(self::$activeStatements as $statementWrapper){

            if($statementWrapper->open){
                echo "<span class='processingError'>Oops, left open statement from ".$statementWrapper->createLine."</span><br/>";
            }
            else{
                //echo " closed.<br/>";
            }

//			$sqlTime = $statementWrapper->executeTime;

            if(SERVER_REPORT_SQL_EXECUTE_TIMINGS){

                if( isset($statementLineTimings[$statementWrapper->createLine])){
                    $statementLineTimings[$statementWrapper->createLine]['time'] += $statementWrapper->executeTime;
                    $statementLineTimings[$statementWrapper->createLine]['count'] ++;
                }
                else{
                    $statementLineTimings[$statementWrapper->createLine] = array();

                    $statementLineTimings[$statementWrapper->createLine]['time'] = $statementWrapper->executeTime;
                    $statementLineTimings[$statementWrapper->createLine]['count'] = 1;
                }

                $totalTime += $statementWrapper->executeTime;
            }
        }

        foreach($statementLineTimings as $line => $timeInfo){

            $time = $timeInfo['time'];
            $count = $timeInfo['count'];

            if(SERVER_REPORT_SQL_EXECUTE_TIMINGS){
                if($time < 0.001){
                    $timeString =' < 0.001';
                }
                else{
                    $timeString = $time;
                }
                echo "Statement called $count time, took $time seconds ".$timeString." from ".$line."\r\n";
            }
        }

        if(SERVER_REPORT_SQL_EXECUTE_TIMINGS){
            echo "<br/>Total SQL execute time is ".$totalTime."<br/>";
        }

        self::$activeStatements = array();
    }

    public static function	reportTimings(){

        $totalTime = 0;

        $statementLineTimings = array();

        foreach(self::$activeStatements as $statementWrapper){

            //$sqlTime = $statementWrapper->executeTime;

//			$timeString = $sqlTime;
//
//			if($sqlTime < 0.01){
//				$timeString =' < 0.01';
//			}

            if( isset($statementLineTimings[$statementWrapper->createLine])){
                    $statementLineTimings[$statementWrapper->createLine]['time'] += $statementWrapper->executeTime;

                    //echo "add ".$statementWrapper->executeTime." to time ".$statementLineTimings[$statementWrapper->createLine]['time']."<br/>";

                    $statementLineTimings[$statementWrapper->createLine]['count']++;
                }
                else{
                    $statementLineTimings[$statementWrapper->createLine] = array();
                    $statementLineTimings[$statementWrapper->createLine]['time'] = $statementWrapper->executeTime;
                    $statementLineTimings[$statementWrapper->createLine]['count'] = 1;
                }

            $totalTime += $statementWrapper->executeTime;
        }


        foreach($statementLineTimings as $line => $timeInfo){

            $time = $timeInfo['time'];
            $count = $timeInfo['count'];

            if($time < 0.001){
                $timeString =' < 0.001';
            }
            else{
                $timeString = $time;
            }
            echo "Statement called $count times, took  ".$timeString." seconds from ".$line." <br/>\r\n";
        }

        echo "<br/>Total SQL execute time is ".$totalTime."<br/>";
    }

    function sendBigString($paramID, $largeString){

        $stringArray = str_split($largeString, 4 * 1024);/*byte safe*/

        foreach($stringArray as $string){
            //echo "Sending blob packet\r\n";
            $this->statement->send_long_data($paramID, $string);
        }
    }

//	function	debugVars(){
//		if($this->log){
//			echo "Parameters are :";
//			var_dump($this->boundParameters);
//		}
//	}

//	function	dumpInnoDBStatus($deadlockString, $errorCode){
//		$innodbStatusString = getVar_DumpOutput(getInnoDBStatus());
//		$debugString =  $deadlockString."\r\n\r\nInnoDB Status\r\n\r\n".$innodbStatusString;
//		$this->logger->($debugString);
//	}

    function execute(){


        $this->executedInternally = true;

        $startTime = microtime();

        $retries = 5;

        $finished = false;

        while($finished == false){

            $result = $this->statement->execute();	/* execute prepared statement */

            if($result === false){

                $errorString = "Error executing ".$this->statement->error.",  errno is ".$this->statement->errno.". Query from ".$this->createLine." retries = $retries<br/>\r\n";

                if($this->statement->errno == 1205){//  ER_LOCK_WAIT_TIMEOUT
                    //$this->dumpInnoDBStatus($errorString, $this->statement->errno);
                    //TODO - put back in actual innoDBstatus
                    $this->logger->warning($errorString. "error code: ". $this->statement->errno);
                }
                else if($this->statement->errno == 1213 && $retries > 0){
                    //$this->dumpInnoDBStatus($errorString, $this->statement->errno);
                    //TODO - put back in actual innoDBstatus
                    $this->logger->warning($errorString. "error code: ". $this->statement->errno);
                    sleep(2);					//This will make the timings not be valid. Need to store flag saying this state
                    $retries--;					//has been slept.
                }
                else if($this->statement->errno == 1062){
                    throw new DBException("MySQL error 1062: Duplicate row detected:".getVar_DumpOutput($this). " Query was [".$this->queryString."]");
                }
                else{
                    //logToFileFatal($errorString);
                    throw new DBException($errorString);
                }
            }
            else{
                $finished = true; //no error so stop executing.
            }
        }

        //$this->executeTime = microtime_diff($startTime);

        return $this->statement;
    }
}



function closeStatement($statementWrapper){

    global $activeStatements;

//	$isCorrectType = is_obj($statementWrapper, 'StatementWrapper');
//
//	if($isCorrectType == FALSE){
//		echo "<span class='processingError'>Trying to close statement but passed in something other than a statementWrapper.</span><br/>";
//		echo emitCallStack();
//		return;
//	}

    $statementWrapper->statement->close();

    if(defined('SKIP_MYSQL_LOGGING') == false || SKIP_MYSQL_LOGGING == false){
        $key = array_search($statementWrapper, $activeStatements);

        if($key === false){
            echo "<span class='processingError'>Trying to close statement that was not recorded as being opened.</span><br/>";
            echo emitCallStack();
            return;
        }
        else{
            //echo "closed statement with key $key<br/>";
            //var_dump($statementWrapper);
            unset($activeStatements[$key]);
        }
    }
}


