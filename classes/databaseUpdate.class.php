<?php


class databaseUpdate{
    private $mysqlConnection;

    function __construct($host, $port, $username, $password, $database){
        $this->mysqlConnection = @mysqli_connect($host, $username, $password, $database, $port);
        $querySession = "SET SESSION sql_mode = 'TRADITIONAL'";
        mysqli_query($this->mysqlConnection, $querySession);
    }


    function __destruct(){
        @mysqli_close($this->mysqlConnection);
    }


    function updateDatabase($importAttributes, $query, $queryCategories){

        //pobieranie indexu kategorii category_tree > category
        $objectsTable = @mysqli_query($this->mysqlConnection, $queryCategories);
        while ($row = mysqli_fetch_array($objectsTable)){
            $dbTableCategories[$row['category']] = $row['category_tree'];
        }

        //pobieranie danych o produktach
        $objectsTable = @mysqli_query($this->mysqlConnection, $query);
        while ($row = mysqli_fetch_array($objectsTable)){
            foreach ($importAttributes as $attribute){
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

        //WYJĄTKI
        $file = fopen('exceptions.txt', 'r');
        $line = explode(',' , fgets($file));
        foreach ($line as $line){
            $exceptionsList[$line] = '1';
        }
        fclose($file);

        //zapisywanie lub aktualizowanie tabeli głównej

        //poczatek zapytania
        $query = "REPLACE INTO T_TD_SUPPLIERS_ALL (
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
                height,
                wasModified,
                wasImported,
                exception
                ) VALUES ";
        foreach ($dbTable as $product){
            //ustalanie wartości dla każdego produktu
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
            $wasModified = 0;
            $wasImported = 0;
            if (isset($exceptionsList[$product['product_id']])){
                $exception = 1;
            }else{
                $exception = 0;
            }
            // dopisywania zapytania dla każdego produktu
            $query .= "(
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
            '" . $height . "',
            " . $wasModified . ",
            " . $wasImported . ",
            " . $exception . "
            ), ";
        }
        $query = substr($query, 0, strlen($query) - 2);
        mysqli_query($this -> mysqlConnection, $query);
        echo 'Aktualizacja zakończona pomyślnie!' . '<br>';
        
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
        
        $descriptions[0] = '';
        $descriptions[1] = '';
        $descriptions[2] = '';
        $descriptions[3] = '';
        $descriptions[4] = '';

        $linesTable = explode('<br>', $description);

        foreach ($linesTable as $line){
            if (strlen($descriptions[0]) < 500){
                $descriptions[0] = $descriptions[0] . '<br>' . $line;
                continue;
            }
            if (strlen($descriptions[1]) < 500){
                $descriptions[1] = $descriptions[1] . '<br>' . $line;
                continue;
            }
            if (strlen($descriptions[2]) < 500){
                $descriptions[2] = $descriptions[2] . '<br>' . $line;
                continue;
            }
            if (strlen($descriptions[3]) < 500){
                $descriptions[3] = $descriptions[3] . '<br>' . $line;
                continue;
            }
            if (strlen($descriptions[4]) < 500){
                $descriptions[4] = $descriptions[4] . '<br>' . $line;
                continue;
            }
        }

        return substr($descriptions[$number], 4);
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