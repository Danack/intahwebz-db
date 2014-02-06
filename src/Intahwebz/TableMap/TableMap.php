<?php

namespace Intahwebz\TableMap;

use Intahwebz\SafeAccess;


abstract class TableMap {

    use SafeAccess;

    var $schema;
    var $tableName;
    var $columns;

    var $indexColumns = array();

    /**
     * @var RelatedTable[]
     */
    protected $relatedTables = [];

    //TODO - make a getter
    public $objectName = null;

    function getObjectName() {
        return $this->objectName;
    }

    function getClassName() {
        $className = get_class($this);

        $slashPosition = strrpos($className, '\\');

        if ($slashPosition !== false) {
            return substr($className, $slashPosition + 1);
        }

        return $className;
    }

    function getDTONamespace() {
        return __NAMESPACE__."DTO";
    }

    function getDTOClassname() {
        return ucfirst($this->getTableName())."DTO";
    }

    function __clone() {
    }

    function isTreeLike() {
        return false;
    }

    function getTableName() {
        return $this->tableName;
    }

    /**
     * @return \Intahwebz\TableMap\RelatedTable[]
     */
    function getRelatedTables() {
        return $this->relatedTables;
    }

    private function __construct($tableDefinition){
        //Must be implemented in class
    }

    function initTableDefinition($tableDefinition) {

        $this->tableName = $tableDefinition['tableName'];

        $this->columns = $tableDefinition['columns'];


        if (array_key_exists('schema', $tableDefinition) == false) {
            throw new \UnexpectedValueException("Cannot initialise table without schema defined");
        }

        $this->schema = $tableDefinition['schema'];
        

        if (array_key_exists('indexColumns', $tableDefinition) == true) {
            $this->indexColumns = $tableDefinition['indexColumns'];
        }

        //TODO - Delete this. The schema should always be defined.
        if (array_key_exists('schema', $tableDefinition) == true) {
            $this->schema = $tableDefinition['schema'];
        }

        if (array_key_exists('relatedTables', $tableDefinition) == true) {
            $this->initRelatedTables($tableDefinition['relatedTables']);
        }
    }

    function initRelatedTables($relatedTables) {

        foreach ($relatedTables as $relatedTableInfo) {
            $type = $relatedTableInfo[0];
            $relatedTableName = $relatedTableInfo[1];

            $relatedTable = new $relatedTableName();

            $this->relatedTables[] = new RelatedTable($relatedTable, $type);
        }
    }

    function addRelatedTable(TableMap $relatedTable) {
        $this->relatedTables[] = $relatedTable;
    }

    /**
     * @param $columnNameToFind
     * @param $arrayOrValue
     * @throws \Exception
     * @internal param $aliased
     * @internal param $columnName
     * @return bool|string
     */
    function getDataTypeForColumn($columnNameToFind, $arrayOrValue) {
        foreach($this->columns as $column){
            $columnNameToTest = $column[0];

            if(strcmp($columnNameToTest, $columnNameToFind) == 0){

                if (isset($column['primary']) && $column['primary']) {
                    //All primary keys are currently i.
                    return 'i';
                }
                //Found the column
                if(isset($column['type']) == true && $column['type'] == 'i'){
                    return 'i';
                }
                if(isset($column['type']) == true && $column['type'] == 'hash'){
                    return 'hash';
                }
                if(isset($column['type']) == true && $column['type'] == 'text'){
                    return 'text';
                }
                else if(isset($column['type']) == true &&
                    $column['type'] == 'd'){

                    if (is_scalar($arrayOrValue) == true){
                        return 's';
                    }
                    else if (isset($arrayOrValue[$column[0]]) == false){
                    //date types when not set default to NOW(), which doesn't add a parameter
                        return false;
                    }
                }
                else{
                    //Strings, hashes
                    return 's';
                }
            }
        }

        $columns = '['.var_export($this->columns).']';

        throw new \Exception("Failed to find columnName [$columnNameToFind] in tableMap: ".$this->schema.".".$this->tableName." Columns are: ".$columns);
    }


    function getPrimaryColumn() {
        foreach($this->columns as $tableColumn){
            if(array_key_exists('primary', $tableColumn) == true){
                if($tableColumn['primary'] == true){
                    return $tableColumn[0];
                }
            }
        }

        return false;
    }


    
    //TODO - this really ought to be elsewhere.
    function getClassString() {

        $output = "class ".$this->getDTOClassname()." {\n";
        foreach($this->columns as $column){
            $output .= "\tpublic \$".$column[0].";\n";
        }

        $output .= "\n";
        $output .= "\tpublic function __construct(";
        $separator = '';

        $primaryColumnName = null;

        foreach($this->columns as $column){
            $output .= $separator.'$'.$column[0].' = null';
            $separator = ', ';

            if (array_key_exists('primary', $column) == true) {
                if ($column['primary']) {
                    $primaryColumnName = $column[0];
                }
            }
        }

        $output .= ") {\n";

        foreach($this->columns as $column){
            $output .= "\t\t\$this->".$column[0]." = \$".$column[0].";\n";
        }
        $output .= "\t} \n";

        foreach($this->columns as $column){
            $fieldName = $column[0];
            $output .= "\tfunction ".$fieldName.'($'.$fieldName.") { \n";
            $output .= "\t\t\$this->".$fieldName.' = $'.$fieldName.";\n";
            $output .= "\t}\n\n";
        }


        //$lcTableName = mb_lcfirst($this->getClassName());
        $lcTableName = mb_lcfirst($this->getDTOClassName());

        $queryType = '\UnknownQueryType';

        if ($this instanceof \Intahwebz\TableMap\SQLTableMap) {
            $queryType  = '\\'.\Intahwebz\TableMap\SQLQuery::class;
        }
        else if ($this instanceof \Intahwebz\TableMap\YAMLTableMap) {
            $queryType  = '\\'.\Intahwebz\TableMap\YAMLQuery::class;
        }

        $fullClassName = '\\'.get_class($this);

        $output .= "

    /**
     * @param \$query $queryType
     * @param \$$lcTableName $fullClassName
     * @return int
     */
    function insertInto($queryType \$query, $fullClassName \$".$lcTableName."){\n
        \$data = convertObjectToArray(\$this);
        \$insertID = \$query->insertIntoMappedTable(\$".$lcTableName.", \$data);\n";

        if ($primaryColumnName) {
            $output .= "\t\$this->$primaryColumnName = \$insertID;\n";
        }

        $output .= "
        return \$insertID;
    }
";

        $output .= "}\n\n";
        return $output;
    }


    function generateObjectFile($directory, $namespace) {

        $output = "<?php\n\n";
        $output .= "namespace $namespace;\n\n";
        $output .= $this->getClassString();
        $output .= "\n";

        $filename = $directory.$this->getDTOClassName().'.php';

        ensureDirectoryExists($filename);

        $fileHandle = fopen($filename, "w");
        fwrite($fileHandle, $output);
        fclose($fileHandle);

    }


}