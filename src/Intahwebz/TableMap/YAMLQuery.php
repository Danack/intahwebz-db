<?php


namespace Intahwebz\TableMap;

use Intahwebz\TableMap\Fragment\SQLTableFragment;
use Intahwebz\TableMap\Fragment\SQLValueFragment;
use Intahwebz\TableMap\Fragment\SQLWhereFragment;


class YAMLQuery extends AbstractQuery {
    
    /**
     * @param TableMap $tableMap
     * @internal param $tableName
     * @return QueriedYAMLTable
     */
    function aliasTableMap(TableMap $tableMap) {
        $tableAlias = $this->getAliasForTable($tableMap);
        /** @var  $tableMap YAMLTableMap */
        return new QueriedYAMLTable($tableMap, $tableAlias, $this);
    }

    function delete() {
        throw new \BadFunctionCallException("delete not implemented yet.");
    }

    function count() {
        throw new \BadFunctionCallException("count not implemented yet.");
    }

    function    applyWhere(SQLWhereFragment $sqlWhereFragment, /** @noinspection PhpUnusedParameterInspection */
                           $rowData) {
        $params = array(
            array(
                $sqlWhereFragment->value,
                $sqlWhereFragment->type,
            ),
        );

        $converter = new WhereEvaluator($sqlWhereFragment->whereCondition, $params, 'rowData');

        return $converter->evaluate($rowData);

    }

    function fetch() {

        $tableMap = $this->getOnlyTableMap();

        $yamlData = $tableMap->getYAML();

        $tableData = $yamlData['tableData'];

        $aliasedTableData = array();
        foreach($tableData as $rowData){
            $entry = array();
            foreach($rowData as $key => $value){
                $entry[$tableMap->getObjectName().'.'.$key] = $value;
            }
            $aliasedTableData[] = $entry;
        }

        $tableData = $aliasedTableData;

        $matchedTableData = array();

        foreach($tableData as &$rowData){
            $whereMatched = TRUE;

            foreach($this->sqlFragments as $sqlFragment){
                if($sqlFragment instanceof SQLWhereFragment){
                    $whereMatched = $whereMatched && $this->applyWhere($sqlFragment, $rowData);
                }
            }

            if($whereMatched == TRUE){
                $matchedTableData[] = $rowData;
            }
        }

        return $matchedTableData;
    }


    function update() {

        $tableMap = $this->getOnlyTableMap();

        $yamlData = $tableMap->getYAML();
        $tableData = $yamlData['tableData'];

        $aliasedTableData = array();
        foreach($tableData as &$rowData){
            $entry = array();
            foreach($rowData as $key => $value){
                $entry[$tableMap->getObjectName().'.'.$key] = &$rowData[$key];//$value;
            }
            $aliasedTableData[] = $entry;
        }

        foreach($aliasedTableData as &$rowData){
            $whereMatched = TRUE;

            foreach($this->sqlFragments as $sqlFragment){
                if($sqlFragment instanceof SQLWhereFragment){
                    $whereMatched = $whereMatched && $this->applyWhere($sqlFragment, $rowData);
                }
            }

            foreach($this->sqlFragments as $sqlFragment){
                if($sqlFragment instanceof SQLValueFragment){
                    if($whereMatched == TRUE){
                        $rowData[$tableMap->getObjectName().'.'.$sqlFragment->name] = $sqlFragment->value;
                    }
                }
            }
        }

        $yamlData['tableData'] = $tableData;
        $tableMap->writeYAML($yamlData);
    }

    /**
     * @return YamlTableMap
     * @throws \Exception
     */
    function getOnlyTableMap(){
        $tableMap = FALSE;

        foreach($this->sqlFragments as $sqlFragment){
            if($sqlFragment instanceof SQLTableFragment){
                if($tableMap === FALSE){
                    $tableMap = $sqlFragment->queriedTableMap;
                }
                else{
                    throw new \BadFunctionCallException("YAML Query only support one table for now.");
                }
            }
        }

        if($tableMap == FALSE){
            throw new \BadFunctionCallException("TableName not set, no table to get data from?");
        }

        return $tableMap;
    }

    function fetchObjects() {
        throw new \Exception("Not implemented yet.");
    }

}

