<?php

    class imageCheck{
        private $mysqlConnection;
    
        function __construct($host, $port, $username, $password, $database){
            $this->mysqlConnection = @mysqli_connect($host, $username, $password, $database, $port);
        }

        function __destruct(){
            @mysqli_close($this -> mysqlConnection);
        }

        function check(){
            $query = "SELECT ";
            for ($i = 0; $i <16; $i++){
                $query = $query . "image" . $i . ", ";
            }
            $query = $query . "product_id FROM T_TD_SUPPLIERS_ALL WHERE LENGTH(description0) > '20' AND stock > '2' AND restrictions = 0";
            $objectsTable = mysqli_query($this->mysqlConnection, $query);
            while ($row = mysqli_fetch_array($objectsTable)){
                $query = "UPDATE T_TD_SUPPLIERS_ALL
                SET ";
                for ($i = 0; $i < 16; $i++){
                    if ($row["image$i"] !== ''){
                        $headers = get_headers($row["image$i"]);
                        if ($headers && strpos($headers[0], '200')){
                            $query = $query . "image$i = '" . $row["image$i"] . "', ";
                        }else{
                            $query = $query . "image$i = '', ";
                        }
                    }else{
                        $query = $query . "image$i = '', ";
                    }
                }
                $query = substr($query, 0, strlen($query) - 2) . " WHERE product_id = " . $row['product_id'];
                mysqli_query($this -> mysqlConnection, $query);
            }
        }

    }



?>