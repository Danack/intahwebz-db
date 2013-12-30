<?php

namespace Intahwebz\DB;


interface Statement {


    /**
     * (PHP 5)<br/>
     * Fetch results from a prepared statement into the bound variables
     * @link http://php.net/manual/en/mysqli-stmt.fetch.php
     * @return bool
     */
    public function fetch();

    function setQueryString($queryString);

    /**
     * @param $parameterArray Array of ($type, $reference)
     */
    function bindParameterArray($parameterArray);

    function bindResult(/** @noinspection PhpUnusedParameterInspection */
        &$var0, &$var1, &$var2 = false, &$var3 = false, &$var4 = false, &$var5 = false,
                        &$var6 = false, &$var7 = false, &$var8 = false, &$var9 = false, &$var10 = false,
                        &$var11 = false, &$var12 = false, &$var13 = false, &$var14 = false, &$var15 = false,
                        &$var16 = false, &$var17 = false, &$var18 = false, &$var19 = false, &$var20 = false);


    function	bindParam($types, /** @noinspection PhpUnusedParameterInspection */
                          &$var1, /** @noinspection PhpUnusedParameterInspection */
                          &$var2 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var3 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var4 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var5 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var6 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var7 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var8 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var9 = false, /** @noinspection PhpUnusedParameterInspection */
                          &$var10 = false);


    function sendFile($parameterNumber, $filePath);

    function close();

    function getInsertID();

    //static function finalise();

    function sendBigString($paramID, $largeString);

    function execute();
}



