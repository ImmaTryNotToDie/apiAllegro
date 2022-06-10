<?php
ini_set('default_socket_timeout', 60);
set_time_limit(7200);
require_once('./classes/databaseUpdate.class.php');
require_once('./classes/exportToCsv.php');
require_once('./classes/baselinkerUpdate.class.php');
require_once('./classes/imageCheck.class.php');
require_once('./classes/priceMonitor.class.php');

$host = 'mysql.lobos.pl';
$port = 3306;
$username = 'mzacharzewski';
$password = 'RazDwaSiedem3@&';
$database = 'lobos_it';

$dbAttributes = ['product_id', 'product_name', 'stock', 'product_code', 'ean', 'sku', 'category', 'our_price_brutto', 'tax', 'weight', 'description0', 'description1', 'description2', 'description3', 'description4', 'image0', 'image1', 'image2', 'image3', 'image4', 'image5', 'image6', 'image7', 'image8', 'image9', 'image10', 'image11', 'image12', 'image13', 'image14', 'image15', 'producer', 'color', 'width', 'height', 'wasModified', 'wasImported', 'exception'];

$importAttributes = ['product_id', 'product_name', 'stock', 'product_code', 'ean', 'category', 'our_price_brutto', 'tax', 'description', 'images', 'producer', 'color'];

//główne zapytanie do bazy
$query = "
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
    USING (product_id)";

// zapytanie o index kategorii
$queryCategories = "
SELECT t_cat.category, category_tree  FROM T_TD_SHOP_CATEGORIES_TREE
LEFT JOIN (SELECT product_id, GROUP_CONCAT(value SEPARATOR '>') AS category FROM T_TD_SUPPLIERS_CATEGORIES GROUP BY product_id) t_cat
USING (product_id)
GROUP BY t_cat.category
";

$start = time();
$import = new databaseUpdate($host, $port, $username, $password, $database);
$import -> updateDatabase($importAttributes, $query, $queryCategories);

$update = new baselinkerUpdate($host, $port, $username, $password, $database, $dbAttributes);
$update -> stockUpdate();

$update = new baselinkerUpdate($host, $port, $username, $password, $database, $dbAttributes);
$update -> priceUpdate(12, 13, 'ALL');

// $imgCheck = new imageCheck($host, $port, $username, $password, $database);
// $imgCheck -> check();

// $csv0 = new exportToCsv($host, $port, $username, $password, $database);
// $csv0 -> createHeader($dbAttributes);
// $csv0 -> createContent($dbAttributes);
// $csv1 = new exportToCsv($host, $port, $username, $password, $database);
// $csv1 -> createHeader($dbAttributes);
// $csv1 -> createContent($dbAttributes);
// $csv2 = new exportToCsv($host, $port, $username, $password, $database);
// $csv2 -> createHeader($dbAttributes);
// $csv2 -> createContent($dbAttributes);
// $csv3 = new exportToCsv($host, $port, $username, $password, $database);
// $csv3 -> createHeader($dbAttributes);
// $csv3 -> createContent($dbAttributes);
// $csv3 = new exportToCsv($host, $port, $username, $password, $database);
// $csv3 -> createHeader($dbAttributes);
// $csv3 -> createContent($dbAttributes);


// $priceMonitor = new priceMonitor;
// $result = $priceMonitor -> getCode();
// echo "Użytkowniku, otwórz ten adres w przeglądarce: \n" . $result->verification_uri_complete ."\n";
// ob_flush();
// flush();
// $accessToken = false;
// $interval = (int)$result->interval;
//     do {
//         sleep($interval);
//         $device_code = $result->device_code;
//         $resultAccessToken = $priceMonitor -> getAccessToken($device_code);
//         if (isset($resultAccessToken->error)) {
//             if ($resultAccessToken->error == 'access_denied') {
//                 break; 
//             } elseif ($resultAccessToken->error == 'slow_down') {
//                 $interval++; 
//             }
//         } else {
//             $accessToken = $resultAccessToken->access_token;
//         }
//     } while ($accessToken == false);

// var_dump($priceMonitor -> getProducts($accessToken));
$memory = memory_get_peak_usage();
echo 'Najwyższe użycie pamięci RAM: ' . round($memory/1000000) . ' MB <br>';
$end = time();
$time = $end - $start;
echo 'Czas pracy: ' . $time . ' s';

?>