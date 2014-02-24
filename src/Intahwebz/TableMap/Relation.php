<?php


namespace Intahwebz\TableMap;


abstract class Relation {
    
    //http://docs.doctrine-project.org/en/2.0.x/reference/association-mapping.html
    
    //http://blog.anthonychaves.net/2009/12/16/bidirectional-relationships-owning-and-inverse-sides/
    //The owning-side of a bidirectional relationship is the one 
    //with the foreign key column on the table 

    //const ONE_TO_ONE_UNIDIRECTIONAL = 'ONE_TO_ONE_UNIDIRECTIONAL'; //This is key in subordinate table
    const ONE_TO_ONE_BIDIRECTIONAL = 'ONE_TO_ONE_DIRECTIONAL'; //This has join
    //const ONE_TO_ONE_SELF_REFERENCING = 'ONE_TO_ONE_SELF_REFERENCING'; //This is key same table?

    const ONE_TO_MANY_UNIDIRECTIONAL = 'ONE_TO_MANY_UNIDIRECTIONAL';
//    const ONE_TO_MANY_BIDIRECTIONAL = 'ONE_TO_MANY_BIDIRECTIONAL';
//    const ONE_TO_MANY_SELF_REFERENCING = 'ONE_TO_MANY_SELF_REFERENCING';

    //const MANY_TO_ONE_UNIDIRECTION = 'MANY_TO_ONE_UNIDIRECTION';

//    const MANY_TO_MANY_UNIDIRECTIONAL = 'MANY_TO_MANY_UNIDIRECTIONAL';
//    const MANY_TO_MANY_BIDIRECTIONAL = 'MANY_TO_MANY_BIDIRECTIONAL';
//    const MANY_TO_MANY_SELF_REFERENCING = 'MANY_TO_MANY_SELF_REFERENCING';
    
    const SELF_CLOSURE = 'SELF_CLOSURE';
    

    /** @var  string */
    private $type;

    /**
     * @var string
     */
    private $owning;

    /**
     * @var string
     */
    private $inverse;

    /**
     * @var string
     */
    private $tableName;

    abstract function getDefinition();
    
    function __construct() {
        $definition = $this->getDefinition();
        $this->type = $definition['type'];
        $this->owning = $definition['owning'];
        $this->inverse = $definition['inverse'];
        $this->tableName = $definition['tableName'];
    }

    function matches(TableMap $joinTableMap) {

        if ($joinTableMap instanceof $this->inverse) {
            return true;
        }
        
        return false;
    }

    /**
     * @return string
     */
    public function getInverse() {
        return $this->inverse;
    }

    public function getInverseTable() {
        $tableName = $this->inverse;
        return new $tableName();
    }
    
    
    
    /**
     * @return string
     */
    public function getOwning() {
        return $this->owning;
    }

    public function getOwningTable() {
        $tableName = $this->owning;
        return new $tableName();
    }

    /**
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * If a table is the owning side of a relationship, and it has a 
     * join table, return that table.
     * @param TableMap $tableMap
     * @return null|TableMap
     */
    function getOwningJoinTable(TableMap $tableMap) {
        if ($this->tableName) {
            $owningSide = $this->owning;
            if ($tableMap instanceof $owningSide) { 
                return new $this->tableName();
            }
        }

        return null;
    }
    
}




 