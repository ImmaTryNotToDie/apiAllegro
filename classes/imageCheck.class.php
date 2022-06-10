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
                $query .= "image" . $i . ", ";
            }
            $query .= "product_id FROM T_TD_SUPPLIERS_ALL WHERE (LENGTH(description0) > '20' AND stock > '2' AND restrictions = 0 AND wasImported = 0) OR (exception = 1)";
            $objectsTable = mysqli_query($this->mysqlConnection, $query);

            ini_set('default_socket_timeout', 5);
            while ($row = mysqli_fetch_array($objectsTable)){
                $query = "UPDATE T_TD_SUPPLIERS_ALL
                SET ";
                for ($i = 0; $i < 16; $i++){
                    if ($row["image$i"] !== ''){
                        $headers = get_headers($row["image$i"]);
                        if ($headers && strpos($headers[0], '200')){
                            $query .= "image$i = '" . $row["image$i"] . "', ";
                        }else{
                            $query .= "image$i = '', ";
                        }
                    }else{
                        $query .= "image$i = '', ";
                    }
                }
                $query = substr($query, 0, strlen($query) - 2) . " WHERE product_id = " . $row['product_id'];
                ini_set('default_socket_timeout', 60);
                mysqli_query($this -> mysqlConnection, $query);
                $i++;
            }
        }

    }



?>