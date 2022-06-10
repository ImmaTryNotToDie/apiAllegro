<?php

define('NUMBER_OF_ITMES_IN_BLOCK', 2000);

class exportToCsv{

    private $csv;
    private $path;
    private $mysqlConnection;

    function __construct($host, $port, $username, $password, $database){
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

    function createContent($attributes){
        $query = "SELECT " . implode(', ', $attributes) . " FROM T_TD_SUPPLIERS_ALL WHERE (LENGTH(description0) > '20' AND stock > '2' AND restrictions = 0 AND wasImported = 0 AND wasModified = 0) OR (exception = 1)";
        $objectsTable = @mysqli_query($this->mysqlConnection, $query);
        if (mysqli_num_rows($objectsTable) !== 0){
            while ($row = mysqli_fetch_array($objectsTable)){
                foreach ($attributes as $attribute){
                    $dbTableProducts[$row['product_id']][$attribute] = $row[$attribute];
                }
            }
            $count = 0;
            $query = 'UPDATE T_TD_SUPPLIERS_ALL SET wasImported = 1 WHERE product_id IN (';
            foreach ($dbTableProducts as $product){
                $count ++;
                if ($count == NUMBER_OF_ITMES_IN_BLOCK) break;
                $query .= $product['product_id'] . ', ';
                fwrite($this -> csv, "\"" . implode('", "', $product) . "\"\n");
            }
            $query = substr($query, 0, strlen($query) - 2) . ')';
            @mysqli_query($this->mysqlConnection, $query);
        }else{
            fwrite($this -> csv, 'Brak produktów do pobrania!');
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
