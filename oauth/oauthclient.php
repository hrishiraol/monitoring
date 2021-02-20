<html>
<title>Oauth</title>
<body>
<h1>Oauth</h1>
       
        <form action="" method="POST">
            <label>Code</label>
                <input type="text" size="70" name="oauthcode"> <br>
            <label>Client ID</label>
                <input type="text" size="70" name="client_id"><br>
            <label>Client Secret</label>
                <input type="text" size="70" name="client_secret"><br>
            <input type="submit" value ="Submit" name="Submit">
        </form>
    <?php 

        $errors = array();

        if(isset($_POST['Submit'])){
                
            $oauthcode=isset($_POST['oauthcode']) ? $_POST['oauthcode']:null;
            $client_id = isset($_POST['client_id']) ? $_POST['client_id']:null;
            $client_secret = isset($_POST['client_secret']) ? $_POST['client_secret']:null;     

            if(strlen(trim($oauthcode))==0){
                $errors[]="Enter Oauthcode";
            }
            if(strlen(trim($client_id))==0){
                $errors[]="Enter Client ID";
            }
            if(strlen(trim($client_secret))==0){
                $errors[]="Enter Client Secret";
            }
            if(empty($errors)){
                
                $Refresh_Token = getRefreshToken($oauthcode,$client_id,$client_secret);                            
                $jsonArray= array(
                    "client_id"=> $client_id,
                    "client_secret"=>$client_secret,
                    "refresh_token"=>$Refresh_Token
                );
                $json = json_encode($jsonArray);
                file_put_contents("zohoauth.json", $json);

                header("Location:RefreshToken.php");                               
            }
        } 

        if(!empty($errors)){ 
            echo '<h1>Error(s)!</h1>';
            foreach($errors as $errorMessage){
                echo $errorMessage . '<br>';
            }
        }

        function getRefreshToken($ocode,$cl_id,$cl_secret){
            
            $code = $ocode;
            $client_id = $cl_id;
            $client_secret = $cl_secret;
            $redirect_uri = "http://54.80.18.15/callback";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://accounts.zoho.com/oauth/v2/token" );
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'code' => $code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code'
                ));

            $response = curl_exec($ch);
            //var_dump($response); 
            return json_decode($response)->refresh_token;
        }
    ?>

</body>
</html> 
