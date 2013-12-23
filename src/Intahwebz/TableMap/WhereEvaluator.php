<?php

namespace Intahwebz\TableMap;

/**
 * @param $paramValue
 * @param $paramType
 * @return int|mixed|string
 * @throws \Exception
 */
function sanitizeParam($paramValue, $paramType) {

    switch($paramType){

        case('s'):{//String type
            return "'".addslashes($paramValue)."'";
        }

        case('i'):{
            return intval($paramValue);
        }

        case('d'):{//Date type
            return preg_replace("/[^\d\s:-]*/", '', $paramValue);
        }

        default:{
            throw new \Exception("Unknown param type [$paramType]");
        }
    }
}

/* Converts a SQL style string with % for matching anything to a preg equivalent.
 */
function convertLikeOperandToRegex($operand) {
    $replacedString = str_replace( "%", "(.*?)", $operand);
    $pregSearch = "'/^'.".$replacedString.".'$/is'";
    return $pregSearch;
}

/* Str_replace limited by count
*/
function str_replace_count($search, $replace, $subject, $count) {
    for($x=0; $x<$count ; $x++){
        $pos = mb_strpos($subject, $search);
        if ($pos !== FALSE) {
            $subject = mb_substr_replace($subject, $replace, $pos, mb_strlen($search));
        }
    }

    return $subject;
}


class WhereEvaluator{

    var $paramsUsed = 0;

    var $queryString;
    var $params = array();

    var $dataName;

    function __construct($queryString, $params, $dataName){
        $this->queryString = $queryString;
        $this->params = $params;
        //$this->dataName = $dataName;
        $this->dataName = 'values';
    }

    function evaluate(/** @noinspection PhpUnusedParameterInspection */$values) {

        //Get the eval with the param to set if it matches.
        $finalEval = $this->replace('whereMatched');


        $whereMatched = FALSE;
            //<evil>
        eval($finalEval);
            //</evil>

        return $whereMatched;
    }


    /**
     * Performs the conversion
     * @param $variableToSet string - What variable to set if the where condition passes.
     * @return string The code to eval to perform the where test.
     */
    function	replace($variableToSet) {

        $newString = $this->queryString;

        $holderSearch = "/([^\s\(]*)\s+(like|=)\s+([^\s\)]+)/i";

        $matches = array();

        preg_match_all($holderSearch, $this->queryString, $matches, PREG_SET_ORDER);

        foreach($matches as $match){
            $originalString = $match[0];

            $operand1 = $match[1];
            $matchOperator = mb_strtolower($match[2]);
            $operand2 = $match[3];

            $replaceString = FALSE;

            switch($matchOperator){
                case('='):{
                    //Generate code that just does a straight compare.
                    $replaceString = '';
                    $replaceString .= $this->getOperand($operand1);
                    $replaceString .= ' = ';
                    $replaceString .= $this->getOperand($operand2);
                    break;
                }

                case('like'):{
                    //Convert the right operand to a regex and then to a preg_match compare
                    $operand1 = $this->getOperand($operand1);
                    $operand2 = $this->getOperand($operand2, TRUE);

                    $pregSearch = convertLikeOperandToRegex($operand2);
                    $replaceString = "(preg_match(".$pregSearch.", ".$operand1.") != 0)";
                    break;
                }

            }

            $newString = str_replace_count($originalString, $replaceString, $newString, 1);
        }

        return $this->convertToEval($newString, $variableToSet);
    }

    // Get an operand to be used in the SQL operation.
    // Replaces column names with the test value
    // Replaces '?' with a bound parameter and sanitizes it.
    // Strings get passed almost straight back (quotes are trimmed if needed).
    function	getOperand($operand) {

        if($operand == '?') {
            if($this->paramsUsed >= count($this->params)){
                throw new \Exception("Not enough parameters");
            }

            $param = $this->params[$this->paramsUsed];

            $paramValue = $param[0];
            $paramType = $param[1];

            $returnValue = sanitizeParam($paramValue, $paramType);

            $this->paramsUsed++;
            return $returnValue;
        }

        if(mb_strpos($operand, "'") === 0 ||
            mb_strpos($operand, '"') === 0) {
            //It's a string
            return $operand;
        }

        //It's the name of a 'column'
        return '$'.$this->dataName."['".$operand."']";
    }

    //Final tidy up to convert SQL style items to PHP style items
    function convertToEval($newString, $variableToSet){

        $search = array('or', 'and', '=');
        $replace = array('||', '&&', '==');

        $newString = str_replace($search, $replace, $newString);

        $finalEval = "if(".$newString."){
            \$".$variableToSet." = TRUE;
        }
        ";

        return $finalEval;
    }
}


