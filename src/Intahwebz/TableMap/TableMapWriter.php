<?php


namespace Intahwebz\TableMap;

/**
 * Class TableMapWriter
 * @TODO This doesn't need to be a class but PHP sucks at function loading.
 * 
 * @package Intahwebz\TableMap
 */
class TableMapWriter {

    /**
     * @param TableMap $tableMap
     * @param $directory
     * @param $namespace
     */
    function generateObjectFile(TableMap $tableMap, $directory, $namespace) {

        $output = "<?php\n\n";
        $output .= "namespace $namespace;\n\n";
        $output .= $tableMap->getClassString();
        $output .= "\n";

        $filename = $directory.'/'.$tableMap->getDTOClassName().'.php';

        ensureDirectoryExists($filename);

        $fileHandle = fopen($filename, "w");
        fwrite($fileHandle, $output);
        fclose($fileHandle);
    }


    /**
     * @TODO - this really ought to be elsewhere, as it is a 'renderer' for this class rather
     * than an intrinisic part of this class.
     * @param TableMap $tableMap
     * @return string
     */
    function getClassString(TableMap $tableMap) {

        $st = "    ";

        $output = "class ".$this->tableMap->getDTOClassname()." {\n";
        foreach($this->tableMap->columns as $column){
            $output .= $st."public \$".$column[0].";\n";
        }

        $output .= "\n";
        $output .= $st."public function __construct(";
        $separator = '';

        $primaryColumnName = null;

        foreach($this->tableMap->columns as $column){
            $output .= $separator.'$'.$column[0].' = null';
            $separator = ', ';

            if (array_key_exists('primary', $column) == true) {
                if ($column['primary']) {
                    $primaryColumnName = $column[0];
                }
            }
        }

        $output .= ") {\n";

        foreach($this->tableMap->columns as $column){
            $output .= $st.$st."\$this->".$column[0]." = \$".$column[0].";\n";
        }
        $output .= $st."} \n";

        foreach($this->tableMap->columns as $column){
            $fieldName = $column[0];
            $output .= $st."function set".mb_ucfirst($fieldName).'($'.$fieldName.") { \n";
            $output .= $st.$st."\$this->".$fieldName.' = $'.$fieldName.";\n";
            $output .= $st."}\n\n";
        }

        $lcTableName = mb_lcfirst($this->tableMap->getDTOClassName());

        $queryType = '\UnknownQueryType';

        if ($this instanceof \Intahwebz\TableMap\SQLTableMap) {
            $queryType  = '\\Intahwebz\TableMap\SQLQuery';
        }
        else if ($this instanceof \Intahwebz\TableMap\YAMLTableMap) {
            $queryType  = '\\Intahwebz\TableMap\YAMLQuery';
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
            $output .= $st."\$this->$primaryColumnName = \$insertID;\n";
        }

        $output .= "
        return \$insertID;
    }
";

        $output .= "}\n\n";
        return $output;
    }


}

 