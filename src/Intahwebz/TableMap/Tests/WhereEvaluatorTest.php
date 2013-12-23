<?php
//
//namespace Intahwebz\TableMap;
//
//
//class WhereEvaluatorTest extends \PHPUnit_Framework_TestCase {
//
//    function test1() {
//
//        //Where condition to test
//        $example1 = "(a.test like ?
//            and a.type like 'S3%')
//            or (a.value = ? and
//            a.value = 'hello')";
//
//        //Parameters to be 'bound' into the query. Each is value + datatype
//        // s = string
//        // i = integer
//        // d = date
//        $params = array(
//            array('Hac"king"', 's'),
//            array('""Ha""c"king"', 's')
//        );
//
//        //Setup the converter
//        $converter = new WhereEvaluator($example1, $params, 'values');
//
//        //Values to use in place of column values
//        $values = array();
//
//        $values['a.test'] = 'test';
//        $values['a.type'] = 'File_STORAGE';
//        $values['a.value'] = 'Danack';
//
//        $whereMatched = $converter->evaluate($values);
//
//        $this->assertFalse($whereMatched, "Where condition failed.");
//    }
//
//
//    function test2() {
//
//        //Where condition to test
//        $example1 = "(a.test like ?
//            and a.type like 'S3%')
//            or (a.value = ? and
//            a.value = 'hello')";
//
//        //Parameters to be 'bound' into the query. Each is value + datatype
//        // s = string
//        // i = integer
//        // d = date
//        $params = array(
//            array('TesT', 's'),
//            array('Danack', 's')
//        );
//
//        //Setup the converter
//        $converter = new WhereEvaluator($example1, $params, 'values');
//
//        //Values to use in place of column values
//        $values = array();
//
//        $values['a.test'] = 'test';
//        $values['a.type'] = 'S3_STORAGE';
//        $values['a.value'] = 'Danack';
//
//
//
//
//        $whereMatched = $converter->evaluate($values);
//
//        $this->assertTrue($whereMatched, "Where condition failed.");
//    }
//}
//
