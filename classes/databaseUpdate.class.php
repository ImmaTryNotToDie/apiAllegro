<?php

set_time_limit(3600);

class databaseUpdate{
    private $mysqlConnection;

    //główne zapytanie do bazy
    private $query = "
    SELECT * FROM (
        SELECT * FROM (
            SELECT * FROM (
                SELECT * FROM(
                    SELECT * FROM (
                        SELECT * FROM (
                            SELECT product_id, product_name, ean, our_price_brutto, tax, stock, product_code FROM T_TD_SUPPLIERS_PRODUCTS WHERE product_id IS NOT NULL) t_prod
                            LEFT JOIN (SELECT product_id, description FROM T_TD_SUPPLIERS_DESCRIPTION) t_desc 
                            USING (product_id)
                            ORDER BY t_prod.product_id ASC, LENGTH(t_desc.description) DESC
                        )t_joined
                        GROUP BY t_joined.product_id
                    )t_grouped
                    LEFT JOIN (SELECT product_id, GROUP_CONCAT(value SEPARATOR'>') AS category FROM T_TD_SUPPLIERS_CATEGORIES GROUP BY product_id) t_cate 
                    USING (product_id)
                )t_grouped2
                LEFT JOIN (SELECT product_id, GROUP_CONCAT(img_url SEPARATOR '|') AS images FROM T_TD_SUPPLIERS_IMAGES GROUP BY product_id) t_done
                    USING (product_id)
            )t_grouped3
            LEFT JOIN (SELECT product_id, value AS producer FROM T_TD_SUPPLIERS_ATTRIBUTES WHERE atr_name = 'producer')t_producers
            USING (product_id)
        )t_grouped4
        LEFT JOIN (SELECT product_id, value AS color FROM T_TD_SUPPLIERS_ATTRIBUTES WHERE atr_name = 'color')t_colors
        USING (product_id)
";

        // zapytanie o index kategorii
        private $queryCategories = "
        SELECT t_cat.category, category_tree  FROM T_TD_SHOP_CATEGORIES_TREE
        LEFT JOIN (SELECT product_id, GROUP_CONCAT(value SEPARATOR '>') AS category FROM T_TD_SUPPLIERS_CATEGORIES GROUP BY product_id) t_cat
        USING (product_id)
        GROUP BY t_cat.category
        ";


    function __construct($host, $port, $username, $password, $database){
        $this->mysqlConnection = @mysqli_connect($host, $username, $password, $database, $port);
        $querySession = "SET SESSION sql_mode = 'TRADITIONAL'";
        mysqli_query($this->mysqlConnection, $querySession);
    }


    function __destruct(){
        @mysqli_close($this->mysqlConnection);
    }


    function updateDatabase($attributes){

        $i = 0;
        $j = 0;
        //pobieranie indexu kategorii category_tree > category
        $objectsTable = @mysqli_query($this->mysqlConnection, $this -> queryCategories);
        while ($row = mysqli_fetch_array($objectsTable)){
            $dbTableCategories[$row['category']] = $row['category_tree'];
        }

        //pobieranie obecnie zapisanych produktów
        $query = 'SELECT product_id FROM T_TD_SUPPLIERS_ALL';
        $objectsTable = @mysqli_query($this->mysqlConnection, $query);
        while ($row = mysqli_fetch_array($objectsTable)){
            $dbTableProducts[$row['product_id']] = $row['product_id'];
        }

        //pobieranie danych o produktach
        $objectsTable = @mysqli_query($this->mysqlConnection, $this -> query);
        while ($row = mysqli_fetch_array($objectsTable)){
            foreach ($attributes as $attribute){
                $dbTable[$row['product_id']][$attribute] = $row[$attribute];
            }

            //ustawienie naszej kategorii
            if (isset($dbTableCategories[$row['category']])){
                $dbTable[$row['product_id']]['category'] = $dbTableCategories[$row['category']];
            }else{
                $dbTable[$row['product_id']]['category'] = 'brak';
            }
        }

        // KATEGORIE BEZ WYMAGAŃ
        $file = fopen('categories.txt', 'r');
        $line = explode(',' , fgets($file));
        foreach ($line as $line){
            $categoryList[$line] = '0';
        }
        fclose($file);


        //zapisywanie lub aktualizowanie tabeli głównej
        foreach ($dbTable as $product){
            $product_id = $product['product_id'];
            $product_name = $this -> skuCorrection($product['product_id']) . ' ' . $this -> nameCorrection($product['product_name']);
            $stock = $product['stock'];
            $product_code = $product['product_code'];
            $ean = $this -> eanCorrection($product['ean']);
            $sku = $this -> skuCorrection($product['product_id']);
            $category = $product['category'];
            $our_price_brutto = $product['our_price_brutto'];
            $tax = $product['tax'];
            $weight = 0;
            $description0 = $this -> descriptionCorrection($product['description'], 0);
            $description1 = $this -> descriptionCorrection($product['description'], 1);
            $description2 = $this -> descriptionCorrection($product['description'], 2);
            $description3 = $this -> descriptionCorrection($product['description'], 3);
            $description4 = $this -> descriptionCorrection($product['description'], 4);
            $image0 = $this -> imageCorrection($product['images'], 0);
            $image1 = $this -> imageCorrection($product['images'], 1);
            $image2 = $this -> imageCorrection($product['images'], 2);
            $image3 = $this -> imageCorrection($product['images'], 3);
            $image4 = $this -> imageCorrection($product['images'], 4);
            $image5 = $this -> imageCorrection($product['images'], 5);
            $image6 = $this -> imageCorrection($product['images'], 6);
            $image7 = $this -> imageCorrection($product['images'], 7);
            $image8 = $this -> imageCorrection($product['images'], 8);
            $image9 = $this -> imageCorrection($product['images'], 9);
            $image10 = $this -> imageCorrection($product['images'], 10);
            $image11 = $this -> imageCorrection($product['images'], 11);
            $image12 = $this -> imageCorrection($product['images'], 12);
            $image13 = $this -> imageCorrection($product['images'], 13);
            $image14 = $this -> imageCorrection($product['images'], 14);
            $image15 = $this -> imageCorrection($product['images'], 15);
            $producer = $this -> producerCorrection($product['producer']);
            if (isset($categoryList[$product['category']])){
                $restrictions = 0;
            }else{
                $restrictions = 1;
            }
            $color = $product['color'];
            $width = $this -> dimensionsSearch($this -> nameCorrection($product['product_name']), 'w');
            $height = $this -> dimensionsSearch($this -> nameCorrection($product['product_name']), 'h');
            if(isset($dbTableProducts[$product_id])){
                $query = "UPDATE T_TD_SUPPLIERS_ALL
                SET
                product_id = " . $product_id . ", 
                product_name = '" . $product_name . "', 
                stock = " . $stock . ", 
                product_code = '" . $product_code . "', 
                ean = '" . $ean . "', 
                sku = '" . $sku . "', 
                category = '" . $category . "', 
                our_price_brutto = " . $our_price_brutto . ", 
                tax = " . $tax . ", 
                weight = " . $weight . ", 
                description0 = '" . $description0 . "', 
                description1 = '" . $description1 . "', 
                description2 = '" . $description2 . "', 
                description3 = '" . $description3 . "', 
                description4 = '" . $description4 . "', 
                image0 = '" . $image0 . "', 
                image1 = '" . $image1 . "', 
                image2 = '" . $image2 . "', 
                image3 = '" . $image3 . "', 
                image4 = '" . $image4 . "', 
                image5 = '" . $image5 . "', 
                image6 = '" . $image6 . "', 
                image7 = '" . $image7 . "', 
                image8 = '" . $image8 . "', 
                image9 = '" . $image9 . "', 
                image10 = '" . $image10 . "', 
                image11 = '" . $image11 . "', 
                image12 = '" . $image12 . "', 
                image13 = '" . $image13 . "', 
                image14 = '" . $image14 . "', 
                image15 = '" . $image15 . "',
                producer = '" . $producer . "',
                restrictions = " . $restrictions . ",
                color = '" . $color . "',
                width = '" . $width . "',
                height = '" . $height . "'
                WHERE product_id = " . $product_id;
                $j++;
                mysqli_query($this -> mysqlConnection, $query);
            }else{
                $query = "INSERT INTO T_TD_SUPPLIERS_ALL (
                product_id, 
                product_name, 
                stock,
                product_code, 
                ean, 
                sku, 
                category, 
                our_price_brutto, 
                tax, 
                weight, 
                description0, 
                description1, 
                description2, 
                description3, 
                description4, 
                image0, 
                image1, 
                image2, 
                image3, 
                image4, 
                image5, 
                image6, 
                image7, 
                image8, 
                image9, 
                image10, 
                image11, 
                image12, 
                image13, 
                image14, 
                image15,
                producer,
                restrictions,
                color,
                width,
                height
                    ) VALUES (
                " . $product_id . ", 
                '" . $product_name . "', 
                " . $stock . ", 
                '" . $product_code . "', 
                '" . $ean . "', 
                '" . $sku . "', 
                '" . $category . "', 
                " . $our_price_brutto . ", 
                " . $tax . ", 
                " . $weight . ", 
                '" . $description0 . "', 
                '" . $description1 . "', 
                '" . $description2 . "', 
                '" . $description3 . "', 
                '" . $description4 . "', 
                '" . $image0 . "', 
                '" . $image1 . "', 
                '" . $image2 . "', 
                '" . $image3 . "', 
                '" . $image4 . "', 
                '" . $image5 . "', 
                '" . $image6 . "', 
                '" . $image7 . "', 
                '" . $image8 . "', 
                '" . $image9 . "', 
                '" . $image10 . "', 
                '" . $image11 . "', 
                '" . $image12 . "', 
                '" . $image13 . "', 
                '" . $image14 . "', 
                '" . $image15 . "',
                '" . $producer . "',
                " . $restrictions . ",
                '" . $color . "',
                '" . $width . "',
                '" . $height . "'
                )";
                mysqli_query($this -> mysqlConnection, $query);
                $i++;
            }
        }
        echo 'dodano: ' . $i . ' produktów. <br>';
        echo 'zaktualizowano: ' . $j . ' produktów. <br>';
        
    }
    


    // FUNKCJE POPRAWIAJĄCE
    // PARAMETRY PRODUKTÓW 
    //          |
    //          |
    //          |
    //          |
    //          V


    // edycja nazwy produktu
    function nameCorrection($name){
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^A-Z a-z 0-9 .ąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+/', ' ', $name);
        $name = str_replace('-', ' ', $name);
        $name = str_replace('   ', ' ', $name);
        $name = str_replace('  ', ' ', $name);
        $name = str_replace("\"", '\'', $name);
        $name = trim($name);
        if (strlen($name) > 42){
        $name = substr($name, 0, 42);
        }
        $name = substr($name, 0, strrpos($name, ' '));

        return $name;
    }


    // tworzenie sku na podstawie product_id
    function skuCorrection($id){
        return substr($id, 0,3) . 'L' . substr($id, 3,3);
    }


    // podział i edycja opisów
    function descriptionCorrection($description, $number){
        $suffixArray = ['.com', '.eu', '.pl', '.org', 'gov', '.edu'];

        $description = str_replace("\n", '<br>', $description);
        $description = str_replace("\r", '<br>', $description);
        $description = str_replace("\r\n", '<br>', $description);
        $description = str_replace(PHP_EOL, '<br>', $description);
        $description = str_replace("\"", '\'', $description);
        $description = str_replace('•', '-', $description);
        $description = str_replace('\'', '"', $description);
        $description = str_replace('\s', ' ', $description);
        $description = str_replace('"', '\'\'', $description);
        
        if (str_replace($suffixArray, '', $description) !== $description){
            foreach ($suffixArray as $suffix){
                if($startOfSuffix = strrpos($description, $suffix)){
                    $startOfLink = strrpos($description, ' ', $startOfSuffix - strlen($description));
                    $endOfLink = $startOfSuffix + strlen($suffix);
                    $description = str_replace(substr($description, $startOfLink, $endOfLink - $startOfLink), ' alibiuro', $description);
                }
            }
        }

        $breakLines = explode('<br>', $description);
        $description = '';
        foreach ($breakLines as $line){
            if (!(preg_match('~^\p{Lu}~u', $line))){
                $description = $description . '<br>' . strtoupper(substr($line, 0, 1)) . substr($line, 1);
            }else{
                $description = $description . '<br>' . $line;
            }
        }
        $description = substr($description, 4);

        if (!$number){
            $start = 0;
        }

        $pos = 0;

        $linesTable = explode('<br>', $description);

        foreach ($linesTable as $line){
            if ($pos + strlen($line) >= $number * 500){
                if (!isset($start)){
                    $start = $pos;
                }
            }

            if ($pos + strlen($line) >= ($number + 1) * 500){
                $end = $pos + strlen($line);
            }
            $pos = $pos + strlen($line) + 4;
        }
        if (isset($start) && isset($end)){
            return substr($description, $start, $end - $start);
        }
        if (isset($start)){
            return substr($description, $start);
        }
        return '';
    }

    // podział na obrazy
    function imageCorrection($images, $number){
        $imagesTable = explode('|', $images);
        if (isset($imagesTable[$number])){
            if (str_replace(['.jpg', '.jpeg', '.bmp', '.png', '.webp'], '', $imagesTable[$number]) !== $imagesTable[$number]){
                    return $imagesTable[$number];
            }else{
                return '';
            }
        }else{
            return '';
        }
    }

    // edycja apostrofów w nazwie producenta
    function producerCorrection($producer){
        return str_replace('\'', '\'\'', $producer);
    }


    //szukanie wymiarów
    function dimensionsSearch($name, $dimension){
        if(strpos($name, 'mm')){
            if(strpos($name, 'x')){
                $mm = strrpos($name, 'mm');
                $x = strrpos($name, 'x', $mm - strlen($name));
                $space = strrpos($name, ' ', $x - strlen($name));
                $height = substr($name, $space + 1, $x - $space - 1);
                $width = substr($name, $x + 1, $mm - $x - 1);

                if ($dimension == 'w' && is_numeric($width)){
                    return $width;
                }
                if ($dimension == 'h' && is_numeric($height)){
                    return $height;
                }
            }
        }else{
            return '';
        }
    }

    function eanCorrection($ean){
        if (strlen($ean) == 11){
            $ean = '0' . $ean;
            return $ean;
        }else{
            return $ean;
        }
    }
}


?>