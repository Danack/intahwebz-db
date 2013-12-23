<?php

namespace Intahwebz\TableMap;

use Intahwebz\SafeAccess;
//use PasswordHash\PasswordHash;
use Intahwebz\Exception\UnsupportedOperationException;

abstract class QueriedTable {

    use SafeAccess;

    public $alias;

    /** @return  SQLTableMap */
    abstract function getTableMap();

    /**
     * @return AbstractQuery
     */
    abstract function getQuery();

    function getAlias() {
        return $this->alias;
    }

    function getSchema(){
        return $this->getTableMap()->schema;
    }

    function getTableName() {
        return $this->getTableMap()->tableName;
    }

    function getAliasedPrimaryColumn() {
        return $this->alias.".".$this->getTableMap()->getPrimaryColumn();
    }

    function getPrimaryColumn() {
        return $this->getTableMap()->getPrimaryColumn();
    }

    function getColumns() {
        return $this->getTableMap()->columns;
    }

    function getColumn($column) {
        return $this->alias.".".$column;
    }

    /**
     * @param $value
     * @return $this
     */
    function wherePrimary($value) {
        $columnName = $this->getAliasedPrimaryColumn();
        $this->getQuery()->where("$columnName = ?", $value, 'i');
        return $this;
    }

    /**
     * @param $column
     * @param $value
     * @return $this
     * @throws \Intahwebz\Exception\UnsupportedOperationException
     */
    function whereColumn($column, $value) {
        return $this->whereColumnInternal('', $column, $value);
    }


    function rand() {

        $aliasedTable = $this->getQuery()->aliasTableMap($this->getTableMap());

        $this->getQuery()->rand($this, $aliasedTable);

        // WHERE r1.id >= r2.id
        //$this->getQuery()->whe

        $this->getQuery()->order($this, $this->getTableMap()->getPrimaryColumn());
        $this->getQuery()->limit(1);

        return $aliasedTable;
    }


    protected function whereColumnInternal($functionName, $column, $value) {

        $columnName = $this->getColumn($column);

        $lb = '(';
        $rb = ')';

        if (strlen($functionName) == 0) {
            $lb = '';
            $rb = '';
        }

        //TODO make this better for date columns
        //TODO - not only is this shite, it may be slow and dangerous.
        $dataType = $this->getTableMap()->getDataTypeForColumn($column, $value);
        switch($dataType){
            case('i'):{
                $this->getQuery()->where($functionName.$lb.$columnName.$rb." = ?", $value, $dataType);
                break;
            }

            case('s'):{
                $this->getQuery()->where($functionName.$lb.$columnName .$rb." like ? ", $value, 's');
                break;
            }

            case('hash'):{
                //$passwordHasher = new PasswordHash(8, false);
                //echo "Hashing [$value]";
                //$hash = $passwordHasher->HashPassword($value);
                //echo " gives value $hash";

                $options = array('cost' => 11);
                $hash = password_hash($value, PASSWORD_BCRYPT, $options); 
                             

                $this->getQuery()->where($functionName.$lb.$columnName .$rb." = ? ", $hash, 's');
                break;
            }

            default:{
                throw new UnsupportedOperationException("Can't handle data type [$dataType] yet for column $column.");
            break;
            }
        }
        return $this;
    }


    function whereColumnIn($column, array $values) {
        $columnName = $this->getColumn($column);
        $dataType = $this->getTableMap()->getDataTypeForColumn($column, null);
        $dataTypeArray = '';
        $inString = " in ( ";
        $separator = '';
        //TODO replace with count?
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($values as $value) {
            $inString .= $separator.' ? ';
            $dataTypeArray .= $dataType;
            $separator = ', ';
        }

        $inString .= " ) ";
        $this->getQuery()->where($columnName.$inString, $values, $dataTypeArray);
        return $this;
    }


    function whereColumnFunction($functionName, $column,  $value) {
        //TODO whitelist funcitonNames
        return $this->whereColumnInternal($functionName, $column, $value);
    }



}
 