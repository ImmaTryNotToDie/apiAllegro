<?php

define('NUMBER_OF_ITMES_IN_BLOCK', 2000);

class exportToCsv{

    private $csv;
    private $path;
    private $mysqlConnection;

    function __construct($host, $port, $username, $password, $database){
        $files = glob('data/*');
        foreach($files as $file){
            $exportAge = time() - intval(substr(basename($file), 6)) ;
            if(is_file($file) && $exportAge > 60) {
                unlink($file);
            }
        }
        $this -> path = 'data/export' . time() . '.csv';
        $this -> csv = fopen($this -> path, 'w');
        $this->mysqlConnection = @mysqli_connect($host, $username, $password, $database, $port);
    }

    function createHeader($attributesNames){
        foreach ($attributesNames as $name){
            fwrite($this->csv, "\"" . $name . "\",");
        }
        fwrite($this->csv, "\n");
    }

    function createContent($attributes, $blockOfContent){
        $query = "SELECT " . implode(', ', $attributes) . " FROM T_TD_SUPPLIERS_ALL WHERE LENGTH(description0) > '20' AND stock > '2' AND restrictions = 0";
        $objectsTable = @mysqli_query($this->mysqlConnection, $query);
        while ($row = mysqli_fetch_array($objectsTable)){
            foreach ($attributes as $attribute){
                $dbTableProducts[$row['product_id']][$attribute] = $row[$attribute];
            }
        }
        $count = -1;
        foreach ($dbTableProducts as $product){
            $count ++;
            if ($count < $blockOfContent * NUMBER_OF_ITMES_IN_BLOCK) continue;
            if ($count == ($blockOfContent + 1) * NUMBER_OF_ITMES_IN_BLOCK) break;
            fwrite($this -> csv, "\"" . implode('", "', $product) . "\"\n");
        }
    }

    function downloadPath(){
        return $this -> path;
    }

    function __destruct(){
        fclose ($this-> csv);
        @mysqli_close($this -> mysqlConnection);
    }

}
