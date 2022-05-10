<?php

class baselinkerUpdate{
    private $mysqlConnection;
    private $parameters;
    private $shopProducts;
    private $shopProductsData;
    private $products;
    private $token = '3006-19807-9S60XRKXQDVB12I1T6RF4AZ37GSXCA1FOWWFWMVSZJSSQZ89H11W35A1ZMLF7T3Y';
    private $categories;


    function __construct($host, $port, $username, $password, $database, $attributes){
        //otwieranie połączenia
        $this->mysqlConnection = @mysqli_connect($host, $username, $password, $database, $port);

        //pobieranie bazy danych
        $query = "SELECT " . implode(', ', $attributes) . " FROM T_TD_SUPPLIERS_ALL";
        $objectsTable = @mysqli_query($this->mysqlConnection, $query);
        while ($row = mysqli_fetch_array($objectsTable)){
            foreach ($attributes as $attribute){
                $this -> products[$row['product_id']][$attribute] = $row[$attribute];
            }
        }
        

        //pobieranie listy produktów z bl
        $page = 1;
        do{
            $this -> parameters = '{"inventory_id": 3575, "page": ' . $page . '}';
            $apiParams = [
            "method" => "getInventoryProductsList",
            "parameters" => $this -> parameters
            ];   
            $curl = curl_init("https://api.baselinker.com/connector.php");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-BLToken: " .  $this -> token]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($apiParams));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($curl), true);
            $this -> parameters = '{"inventory_id": 3575, "products": [';
            if ($response['status'] == 'SUCCESS'){
                foreach ($response['products'] as $product){
                    $this -> shopProducts[] = $product;
                    $this -> parameters = $this -> parameters . $product['id'] . ',';
                }
            }else{
                echo 'problem z pobraniem produktów!';
            }
            $this -> parameters = substr($this -> parameters, 0 , strlen($this->parameters) - 1) . ']}';
            $apiParams = [
            "method" => "getInventoryProductsData",
            "parameters" => $this -> parameters
            ];

            $curl = curl_init("https://api.baselinker.com/connector.php");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-BLToken: " .  $this -> token]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($apiParams));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = json_decode(curl_exec($curl), true);
            foreach ($response['products'] as $shopProduct){
                $this -> shopProductsData[] = $shopProduct;
            }
            $page ++;
        } while (count($response['products']) == 1000);

        //pobieranie listy kategorii z bl
        $methodParams = '{"inventory_id": "3575"}';
        $apiParams = [
            "method" => "getInventoryCategories",
            "parameters" => $methodParams
        ];

        $curl = curl_init("https://api.baselinker.com/connector.php");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-BLToken: " .  $this -> token]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($apiParams));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($curl), true);
        foreach ($response as $category){
            $this -> categories = $category;
        }
    }


    function stockUpdate(){
        $counter = 0;
        $loopCount = ceil(count($this -> shopProducts)/1000);
        for ($i = 0; $i < $loopCount; $i++){
            $counter = 0;
            $this -> parameters = '{
                "inventory_id": "3575",
                "products": {';
            foreach ($this -> shopProducts as $product){
                if ($counter < $i * 1000){
                    $counter++;
                    continue;
                }
                if ($counter == ($i + 1) * 1000)
                    break;
                    
                if (isset($this -> products[str_replace('L', '', $product['sku'])]['stock'])){
                    $this -> parameters = $this -> parameters . '"' . $product['id'] . '": {'
                    . '"bl_4604":' . $this -> products[str_replace('L', '', $product['sku'])]['stock'] . 
                    '},';
                }else{
                    $this -> parameters = $this -> parameters . '"' . $product['id']  . '": {'
                    . '"bl_4604":' . 0.00 . 
                    '},';
                    echo 'nie udalo się zaktualizować produktu ID: ' . $product['id']  . '<br>';
                }
                $counter++;
            }
            $this -> parameters = substr($this -> parameters, 0, strlen($this -> parameters) - 1) . '}}';

            $apiParams = [
            "method" => "updateInventoryProductsStock",
            "parameters" => $this -> parameters
            ];

            $curl = curl_init("https://api.baselinker.com/connector.php");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-BLToken: " .  $this -> token]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($apiParams));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
        }
    }



    function priceUpdate($marginCategory, $margin, $category){
        $counter = 0;
        $loopCount = ceil(count($this -> shopProducts)/1000);

        for ($i = 0; $i < $loopCount; $i++){
            $counter = 0;
            $this -> parameters = '{
                "inventory_id": "3575",
                "products": {';
            foreach ($this -> shopProducts as $product){
                if ($counter < $i * 1000){
                    $counter++;
                    continue;
                }
                if ($counter == ($i + 1) * 1000)
                    break;
                if ($category == 'ALL' || $this -> shopProductsData[$product['id']]['category_id'] == $category){
                    if (isset($this -> products[str_replace('L', '', $product['sku'])]['our_price_brutto'])){
                        $this -> parameters = $this -> parameters . '"' . $product['id'] . '": {'
                        . '"859":' . 
                        $this -> priceCalculation($this -> products[str_replace('L', '', $product['sku'])]['our_price_brutto'], $marginCategory, $margin) . 
                        '},';
                    }else{
                        $this -> parameters = $this -> parameters . '"' . $product['id'] . '": {'
                        . '"859":' . 0.00 . 
                        '},';
                        echo 'nie udalo się zaktualizować produktu ID: ' . $product['id'] . '<br>';
                    }
                }
            $counter++;
            }
            $this -> parameters = substr($this -> parameters, 0, strlen($this -> parameters) - 1) . '}}';
            $apiParams = [
            "method" => "updateInventoryProductsPrices",
            "parameters" => $this -> parameters
            ];

            $curl = curl_init("https://api.baselinker.com/connector.php");
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["X-BLToken:" .  $this -> token]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($apiParams));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);  
        }
    }


    function categoriesList(){
        $i = 0;
        foreach ($this -> categories as $category){
            $tableToReturn[$i]['name'] = $category['name'];
            $tableToReturn[$i]['category_id'] = $category['category_id'];
            $i++;
        }
        var_dump($tableToReturn);
        return $tableToReturn;
    }


    function priceCalculation($price, $marginCat, $margin){
        $marginCat = str_replace('%', '', $marginCat);
        $margin = str_replace('%', '', $margin);
        $price = ceil($price + $price*$marginCat/100 + $price*$margin/100)- 0.01;
        if ($price < 1){
            return 1;
        }else{
            return $price;
        }
    }
}

?>