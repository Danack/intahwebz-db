<?php

define('SERVER_REPORT_SQL_EXECUTE_TIMINGS', 'false');
define('SERVER_REPORT_SQL_EXECUTE_MINIMUM_TIME', 'false');


class TimingProxy {

    static $activeStatements = array();

    static $statementsExecutedExternally = array();
    
    
    function __construct() {
        if(defined('SKIP_MYSQL_LOGGING') == false || SKIP_MYSQL_LOGGING == false){
            self::$activeStatements[] = $this;
        }
    }



    function close() {
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


function closeStatement($statementWrapper) {

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


