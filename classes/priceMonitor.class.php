<?php
define('CLIENT_ID', '13810547f0e341e79638101f891c06ff'); // wprowadź Client_ID aplikacji
define('CLIENT_SECRET', 'uaH2ezgXb6R7R33xQHAmro651lmDdp74KzWrANYBKE2IpLhQOCghqtgQPhGdXyBf'); // wprowadź Client_Secret aplikacji
define('CODE_URL', 'https://allegro.pl/auth/oauth/device');
define('TOKEN_URL', 'https://allegro.pl/auth/oauth/token');

class priceMonitor{


    private $ch;

function getCurl($url, $headers, $content = null) {
    $this -> ch = curl_init();
    curl_setopt_array($this -> ch, array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true
    ));
    if ($content !== null) {
        curl_setopt($this -> ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this -> ch, CURLOPT_POST, true);
        curl_setopt($this -> ch, CURLOPT_POSTFIELDS, $content);
    }
    return $this -> ch;
}


    function getCode(){
        $authorization = base64_encode(CLIENT_ID.':'.CLIENT_SECRET);
        $headers = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "client_id=" .CLIENT_ID;
        $this -> ch =$this -> getCurl(CODE_URL, $headers, $content);
        $result = curl_exec($this -> ch);
        $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
        curl_close($this -> ch);
        if ($result === false || $resultCode !== 200) {
            exit ("Something went wrong:  $resultCode $result");
        }
        return json_decode($result);
    }


    function getAccessToken($device_code) {
        $authorization = base64_encode(CLIENT_ID.':'.CLIENT_SECRET);
        $headers = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Adevice_code&device_code=${device_code}";
        $this -> ch =$this -> getCurl(TOKEN_URL, $headers, $content);
        $tokenResult = curl_exec($this -> ch);
        curl_close($this -> ch);
        return json_decode($tokenResult);
    }

    function getOffers($token){
        $headers = array("Authorization: Bearer {$token}", "Accept: application/vnd.allegro.public.v1+json");
        $url = "https://api.allegro.pl/sale/offers";
        $this -> ch = $this -> getCurl($url, $headers);
        $offersResult = curl_exec($this -> ch);
        $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
        echo $resultCode.  '<br>';
        var_dump($offersResult);
        curl_close($this -> ch);
        if ($offersResult === false || $resultCode !== 200) {
            exit ("Something went wrong");
        }
        $offersList = json_decode($offersResult);
        return $offersList;
    }

    
    
    function getProducts($token){
        $headers = array("Authorization: Bearer {$token}", "Accept: application/vnd.allegro.public.v1+json");
        $url = "https://api.allegro.pl/offers/listing?phrase='auctionTest'";
        $this -> ch =$this -> getCurl($url, $headers);
        $offersResult = curl_exec($this -> ch);
        $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
        echo $resultCode.  '<br>';
        var_dump($offersResult);
        curl_close($this -> ch);
        if ($offersResult === false || $resultCode !== 200) {
            exit ("Something went wrong");
        }
        $offersList = json_decode($offersResult, true);
        return $offersList;
    }
}

// function getProducts($token, $productId){
//     $headers = array("Authorization: Bearer {$token}", "Accept: application/vnd.allegro.public.v1+json");
//     $url = "https://api.allegro.pl.allegrosandbox.pl/sale/products/$productId";
//     $this -> ch =$this -> getCurl($url, $headers);
//     $offersResult = curl_exec($this -> ch);
//     $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
//     echo $resultCode.  '<br>';
//     var_dump($offersResult);
//     curl_close($this -> ch);
//     if ($offersResult === false || $resultCode !== 200) {
//         exit ("Something went wrong");
//     }
//     $offersList = json_decode($offersResult);
//     return $offersList;
// }

// function getOfferDetails($token){
    //     $headers = array("Authorization: Bearer {$token}", "Accept: application/vnd.allegro.public.v1+json");
    //     $url = "https://api.allegro.pl.allegrosandbox.pl/sale/offers/12147185997";
    //     // $query = ['offerId' => '12147185997'];
    //     // $getChildrenUrl = $url . '?' . http_build_query($query);
    //     $this -> ch =$this -> getCurl($url, $headers);
    //     $offersResult = curl_exec($this -> ch);
    //     $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
    //     echo $resultCode.  '<br>';
    //     var_dump($offersResult);
    //     curl_close($this -> ch);
    //     if ($offersResult === false || $resultCode !== 200) {
    //         exit ("Something went wrong");
    //     }
    //     $offersList = json_decode($offersResult);
    //     return $offersList;
    // }


    // function getAccessToken() {
    //     $authorization = base64_encode(CLIENT_ID.':'.CLIENT_SECRET);
    //     $headers = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
    //     $content = "grant_type=client_credentials";
    //     $url = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token";
    //     $this -> ch = $this -> getCurl($headers, $url, $content);
    //     $tokenResult = curl_exec($this -> ch);
    //     $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
    //     curl_close($this -> ch);
    //     if ($tokenResult === false || $resultCode !== 200) {
    //         exit ("Something went wrong");
    //     }
    //     return json_decode($tokenResult)->access_token;
    // }

    // function getMainCategories($token) {
    //     $headers = array("Authorization: Bearer {$token}", "Accept: application/vnd.allegro.public.v1+json");
    //     $url = "https://api.allegro.pl.allegrosandbox.pl/sale/categories";
    //     $this -> ch = $this -> getCurl($headers, $url);
    //     $mainCategoriesResult = curl_exec($this -> ch);
    //     $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
    //     curl_close($this -> ch);
    //     if ($mainCategoriesResult === false || $resultCode !== 200) {
    //         exit ("Something went wrong");
    //     }
    //     $categoriesList = json_decode($mainCategoriesResult);
    //     return $categoriesList;
    // }



?>