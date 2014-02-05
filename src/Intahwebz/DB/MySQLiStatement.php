<?php

namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class MySQLiStatement implements Statement {

    /** @var \mysqli_stmt */
    var $statement;
    
    var $open;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    var $logger;

    var $queryString = '';

    var $boundParameters;

    function __construct(\mysqli_stmt $statement, $queryString, LoggerInterface $logger) {
        $this->queryString = $queryString;
        $this->statement = $statement;
        $this->open = true;
        $this->logger = $logger;
    }


    function getQueryString() {
        return $this->queryString;
    }

    function __destruct() {
        if ($this->open == true) {
            $this->logger->warning("Forgot to close statement ".$this->queryString);
        }
    }
    
    /**
     * Fetch results from a prepared statement into the bound variables
     * @link http://php.net/manual/en/mysqli-stmt.fetch.php
     * @return bool
     */
    function fetch() {
        return $this->statement->fetch();
    }

    /**
     * @param $parameterArray Array of ($type, $reference)
     */
    function bindParameterArray($parameterArray) {
        $typesString = '';
        $parametersArray = array();

        foreach($parameterArray as $parameterAndType){
            $typesString .= $parameterAndType[0];
            $parametersArray[] = $parameterAndType[1];
        }

        $finalParamArray = array($typesString);
        $finalParamArray = array_merge($finalParamArray, $parametersArray);

        $this->boundParameters = $parametersArray;
        
        call_user_func_array(array($this->statement, "bind_param"), $finalParamArray);
    }


    function bindResult(/** @noinspection PhpUnusedParameterInspection */
        &$var0, &$var1, &$var2 = false, &$var3 = false, &$var4 = false, &$var5 = false,
        &$var6 = false, &$var7 = false, &$var8 = false, &$var9 = false, &$var10 = false,
        &$var11 = false, &$var12 = false, &$var13 = false, &$var14 = false, &$var15 = false,
        &$var16 = false, &$var17 = false, &$var18 = false, &$var19 = false, &$var20 = false) {

        $numberOfArguments = func_num_args();

        if($numberOfArguments > 21){
            $errorString = "Error: StatementWrapper::bindParam only supports up to 21 parameters, trying to set ".$numberOfArguments.".";

            throw new \BadFunctionCallException($errorString);
        }

        $arguments = array();

        for($count = 0; $count<$numberOfArguments; $count++){
            $varName = "var".$count;
            $arguments[] = &$$varName;
        }

        $this->boundParameters = $arguments;
        
        return call_user_func_array(array(&$this->statement, 'bind_result'), $arguments);
    }


    function bindParam($types,
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
                throw new \BadFunctionCallException("There is a mismatch in the number of types string being passed into the query and the actual number of types.");
            }
        }

        if($numberOfArguments > 11){
            $errorString = "Error: StatementWrapper::bindParam only supports up to 10 parameters, trying to set ".($numberOfArguments - 1).".";

            throw new \BadFunctionCallException($errorString);
        }

        $arguments = array();

        $arguments[] = $types;

        for($count = 1; $count<$numberOfArguments; $count++){
            $varName = "var".$count;
            $arguments[] = &$$varName;
        }

        $this->boundParameters = $arguments;
        
        call_user_func_array(array(&$this->statement, 'bind_param'), $arguments);
    }


    function sendFile($parameterNumber, $filePath) {

        $fp = fopen($filePath, "r");

        if($fp === false){
            throw new \Exception("Failed to open file [$filePath] for reading.");
        }

        $size = 0;
        
        //TODO - check filesize before sending.

        while (!feof($fp)) {
            $this->statement->send_long_data($parameterNumber, fread($fp, 8192));

            $size += 8192;

            if($size > 4 * 1024 * 1024){
                throw new \BadFunctionCallException("file is too large to upload, max size is 4 megabytes.");
            }
        }
        fclose($fp);

        return true;
    }

    function close() {
        $this->statement->close();
        $this->open = false;
    }

    function getInsertID() {
        return $this->statement->insert_id;
    }

    function sendBigString($paramID, $largeString){
        $stringArray = str_split($largeString, 4 * 1024);/*byte safe*/

        foreach($stringArray as $string){
            $this->statement->send_long_data($paramID, $string);
        }
    }

    function execute() {

        $retries = 5;

        $finished = false;

        while($finished == false){

            $result = $this->statement->execute();  /* execute prepared statement */

            if($result === false){

                $errorString = "Error executing ".$this->statement->error.",  errno is ".$this->statement->errno.". Query from ".$this->queryString." retries = $retries<br/>\r\n";

                if($this->statement->errno == 1205){//  ER_LOCK_WAIT_TIMEOUT
                    //$this->dumpInnoDBStatus($errorString, $this->statement->errno);
                    //TODO - put back in actual innoDBstatus
                    $this->logger->warning($errorString. "error code: ". $this->statement->errno);
                }
                else if($this->statement->errno == 1213 && $retries > 0){
                    //$this->dumpInnoDBStatus($errorString, $this->statement->errno);
                    //TODO - put back in actual innoDBstatus
                    $this->logger->warning($errorString. "error code: ". $this->statement->errno);
                    sleep(2); //This will make the timings not be valid. Need to store flag saying this state
                    $retries--; //has been slept.
                }
                else if($this->statement->errno == 1062){
                    throw new DBException("MySQL error 1062: Duplicate row detected:".var_export($this, true). " Query was [".$this->queryString."]");
                }
                else{
                    throw new DBException($errorString);
                }
            }
            else{
                $finished = true; //no error so stop executing.
            }
        }

        return $this->statement;
    }
}