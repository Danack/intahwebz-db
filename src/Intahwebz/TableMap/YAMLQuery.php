<?php


namespace Intahwebz\TableMap;


class YAMLQuery extends AbstractQuery{

    /**
     * @param TableMap $tableMap
     * @internal param $tableName
     * @return QueriedYAMLTable
     */
    function aliasTableMap(TableMap $tableMap) {
        /** @var $tableAlias YAMLTableMap */
        $tableAlias = $this->getAliasForTable($tableMap);
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
                    $tableMap = $sqlFragment->tableMap;
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


//    /**
//     * @param TableMap $tableMap
//     * @return QueriedSQLTable
//     * @throws \Exception
//     */
//    function aliasTableMap(TableMap $tableMap) {
//
//        if(in_array($tableMap->tableName, $this->tableNamesUsed) == FALSE){
//            $this->tableNamesUsed[] = $tableMap->tableName;
//
//            return new QueriedSQLTable($tableMap, $tableMap->tableName, $this);
//        }
//
//        $tableAliases = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',  );
//
//        if($this->aliasCount >= 0 && $this->aliasCount < count($tableAliases)){
//            $this->aliasCount++;
//
//            return new QueriedYAMLTable($tableMap, $tableAliases[$this->aliasCount], $this);
//        }
//
//        throw new \Exception("Out of aliases");
//    }




}

