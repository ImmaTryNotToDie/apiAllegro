<?php
define('CLIENT_ID', 'f839832e0cb64a5fb5eb18284ebbddc5'); // wprowadź Client_ID aplikacji
define('CLIENT_SECRET', 'Xi35di1noXf4mtB3dObh4EFVDW3rE4XY4oJJ0f2TaMniqcPFY06GFUm7o1iT5iRA'); // wprowadź Client_Secret aplikacji

class priceMonitor{
    private $ch;
    
    function getCurl($headers, $url, $content = null) {
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
    
    function getAccessToken() {
        $authorization = base64_encode(CLIENT_ID.':'.CLIENT_SECRET);
        $headers = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=client_credentials";
        $url = "https://allegro.pl/auth/oauth/token";
        $ch = $this -> getCurl($headers, $url, $content);
        $tokenResult = curl_exec($this -> ch);
        $resultCode = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
        curl_close($this -> ch);
        if ($tokenResult === false || $resultCode !== 200) {
            exit ("Something went wrong");
        }
        return json_decode($tokenResult)->access_token;
    }
    
     function main()
    {
        echo "access_token = ", $this -> getAccessToken() . '<br>';
    }
}


?>