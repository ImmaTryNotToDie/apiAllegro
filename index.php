<?php
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

$dbAttributes = ['product_id', 'product_name', 'stock', 'product_code', 'ean', 'sku', 'category', 'our_price_brutto', 'tax', 'weight', 'description0', 'description1', 'description2', 'description3', 'description4', 'image0', 'image1', 'image2', 'image3', 'image4', 'image5', 'image6', 'image7', 'image8', 'image9', 'image10', 'image11', 'image12', 'image13', 'image14', 'image15', 'producer', 'color', 'width', 'height'];

$importAttributes = ['product_id', 'product_name', 'stock', 'product_code', 'ean', 'category', 'our_price_brutto', 'tax', 'description', 'images', 'producer', 'color'];

$start = time();
$import = new databaseUpdate($host, $port, $username, $password, $database);
$import -> updateDatabase($importAttributes);

$imgCheck = new imageCheck($host, $port, $username, $password, $database);
$imgCheck -> check();

$csv0 = new exportToCsv($host, $port, $username, $password, $database);
$csv0 -> createHeader($dbAttributes);
$csv0 -> createContent($dbAttributes, 0);
$csv1 = new exportToCsv($host, $port, $username, $password, $database);
$csv1 -> createHeader($dbAttributes);
$csv1 -> createContent($dbAttributes, 1);
$csv2 = new exportToCsv($host, $port, $username, $password, $database);
$csv2 -> createHeader($dbAttributes);
$csv2 -> createContent($dbAttributes, 2);
$csv3 = new exportToCsv($host, $port, $username, $password, $database);
$csv3 -> createHeader($dbAttributes);
$csv3 -> createContent($dbAttributes, 3);
$csv3 = new exportToCsv($host, $port, $username, $password, $database);
$csv3 -> createHeader($dbAttributes);
$csv3 -> createContent($dbAttributes, 4);

$update = new baselinkerUpdate($host, $port, $username, $password, $database, $dbAttributes);
$update -> stockUpdate();

$update = new baselinkerUpdate($host, $port, $username, $password, $database, $dbAttributes);
$update -> priceUpdate(10, 12, 'ALL');

// $priceMonitor = new priceMonitor;
// $priceMonitor -> main();

echo 'Czas pracy: ' . time() - $start . ' s';

?>