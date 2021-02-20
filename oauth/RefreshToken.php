<html>
<title>Refresh Token</title>
<body>
<h1>Refresh Token</h1>

<?php
        $readJson = file_get_contents("zohoauth.json");
        $array = json_decode($readJson);

        $client_id = $array->client_id;
        $client_secret = $array->client_secret;
        $refresh_token = $array->refresh_token;

        function RenewRefreshToken($rftkn,$clid,$clsec){
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://accounts.zoho.com/oauth/v2/token" );
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'refresh_token' => $rftkn,
                'client_id' => $clid,
                'client_secret' => $clsec,                
                'grant_type' => 'refresh_token'
                ));

            $response = curl_exec($ch);
            file_put_contents("zoho-authtoken.json", $response);

            //var_dump($response); 
            //return json_decode($response)->access_token;  
            header("Location: getzohodata.php");  
        }

        RenewRefreshToken($refresh_token,$client_id,$client_secret);
?>
</body>
</html>