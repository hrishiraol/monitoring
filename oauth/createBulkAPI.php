<html>
<title>Create Bulk Report API</title>
<body>
<h1>Create Bulk Report</h1>
    
    <form action="" method="post">
        <input type="submit" name="CreateBulkAPI" value="Create" /><br>
        <input type="submit" name="CheckBulkAPI" value="Check" /><br>
        <input type="submit" name="DownloadBulkAPI" value="Download" /><br>
        <input type="submit" name="Unzip" value="Unzip" /><br>
    </form>
    
<?php     
        if(isset ($_POST['CreateBulkAPI'])){  
                     
            list ($readStatus, $readID)= createBulkReport();
                    echo "STATUS: ".$readStatus . "<br>";
                    echo  "ID: ". $readID . "<br>";
                echo "Created"; 
        }   

        if(isset ($_POST['CheckBulkAPI'])){  
                       
            //$checkState="Not Initiated";
            echo "<br>Check Status: ".$checkState ."<br>";
            list($checkID, $checkState, $checkUrl) = checkBulkReport();
            echo "Check ID: ".$checkID."<br>";
            echo "Check State: ".$checkState."<br>";
            echo "Download URL: ".$checkUrl."<br>";
        }

        if(isset ($_POST['DownloadBulkAPI'])){  
                       
            downloadBulkReport();
        }

        if(isset ($_POST['Unzip'])){                        
            
            extractZipArchive("2648626000021737005.zip", "csvfiles/");
        }

        function downloadBulkReport(){

            $readaccessToken = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readaccessToken);
            $access_Token = $array->access_token;
            //echo "<br>AccessToken: ".$access_Token."<br>";

            $readJobID = file_get_contents("checkbulkreport.json");
            $array1 = json_decode($readJobID);
            $readID = $array1->data[0]->id;            
            //echo "<br>ReadID: ".$readID."<br>";

            $ch = curl_init();
            
            $curl_options = array();
            $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read/". $readID."/result";
            $curl_options[CURLOPT_RETURNTRANSFER] = true; 
            $curl_options[CURLOPT_HEADER] = 0; //Get Header details 0=false
            $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
 
            $headersArray = array();
            $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . $access_Token;
            $headersArray[] = "Content-Type".":"."application/zip";
          
            $curl_options[CURLOPT_HTTPHEADER]=$headersArray;        
            curl_setopt_array($ch, $curl_options);

            $result = curl_exec($ch);  
            $responseInfo = curl_getinfo($ch);
            curl_close($ch);      
            
            $directory = "zipfiles/"; 
            $filename= $directory.$readID.".zip";   
               
            file_put_contents($filename, $result);
            extractZipArchive($filename, "csvfiles/");
        } 

        function checkBulkReport(){

            $readaccessToken = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readaccessToken);
            $access_Token = $array->access_token;

            //echo "<br>AccessToken: ".$access_Token."<br>";

            $readJobID = file_get_contents("createbulkreport.json");
            $array1 = json_decode($readJobID);
            $readID = $array1->data[0]->details->id;
            
            //echo "<br>ReadID: ".$readID."<br>";

            $ch = curl_init();
            
            $curl_options = array();
            $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read/". $readID;
            
            $curl_options[CURLOPT_RETURNTRANSFER] = true; 
            $curl_options[CURLOPT_HEADER] = 0; //Get Header details 0=false
            $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";

            $headersArray = array();
            $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . $access_Token;
            $headersArray[] = "Content-Type".":"."application/json";
            
            $curl_options[CURLOPT_HTTPHEADER]=$headersArray;        
            curl_setopt_array($ch, $curl_options);

            $result = curl_exec($ch);    
            curl_close($ch);  
            file_put_contents("checkbulkreport.json", $result); 
            //var_dump($result);

            $jsonResponse = json_decode($result);           
            $id = $jsonResponse->data[0]->id;
            $state =  $jsonResponse->data[0]->state;
            $d_url = $jsonResponse->data[0]->result->download_url;
                                
            return array($id, $state, $d_url);

            /* echo "<br>Read ID: ". $id ."<br>";
            echo "<br>Read State: ". $state ."<br>";
            echo "<br>Download URL: ". $d_url ."<br>"; */

        }
        
        function createBulkReport(){

            $readJson = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readJson);
            $access_Token = $array->access_token;
            
            $queryaccntapi = file_get_contents("queryaccntapi.json");
            $queryequipapi = file_get_contents("queryequipapi.json");

            $query = array();
            $query[]= $queryaccntapi;
            $query[]= $queryequipapi;

            



            $ch = curl_init();            
            $curl_options = array();
            $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read";
            $curl_options[CURLOPT_RETURNTRANSFER] = true; 
            $curl_options[CURLOPT_HEADER] = 0; //Get Header details 0=false
            $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
            $curl_options[CURLOPT_POSTFIELDS]= $query;                
            $headersArray = array();
            $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . $access_Token;
            $headersArray[] = "Content-Type".":"."application/json";
            $curl_options[CURLOPT_HTTPHEADER]=$headersArray;
            curl_setopt_array($ch, $curl_options);
            $result = curl_exec($ch);    
            curl_close($ch);  
            
            file_put_contents("createbulkreport.json", $result);          
            //var_dump($result);  
            
            $jsonResponse = json_decode($result);           
            $status =  $jsonResponse->data[0]->status;
            $id = $jsonResponse->data[0]->details->id;
            //echo "<br>Create Status: ". $status."<br>";
            //echo "<br>Create ID: ". $id ."<br>";

            return array($status, $id);            
        }

        function extractZipArchive($archive, $destination){
            
            // Check if webserver supports unzipping.
            /*  if(!class_exists('ZipArchive')){
                echo 'Your PHP version does not support unzip functionality.';              
            } 
            if(is_readable($archive)) {
            echo ("$archive is readable")."<br>";
            } else {
            echo ("$archive is not readable")."<br>";
            } 
            */

            echo $archive."<br>";
            echo $destination."<br>";

            $zip = new ZipArchive();
            // Check if archive is readable.
            if($zip->open($archive) === TRUE){
                // Check if destination is writable
                if(is_writeable($destination)){
                    $zip->extractTo($destination);
                    $zip->close();
                   echo 'Files unzipped successfully';                    
                }else{
                    echo 'Directory not writeable by webserver.';                    
                }
            }else{
                echo 'Cannot read '. $archive.'  archive.';                
            }
        }

        
            /* list ($headers, $content) = explode("\r\n\r\n", $result, 2);
                if(strpos($headers," 100 Continue")!==false){
                    list( $headers, $content) = explode( "\r\n\r\n", $content , 2);
                }

                $headerArray = (explode("\r\n", $headers, 50));
                $headerMap = array();
                
                foreach ($headerArray as $key) {
                    if (strpos($key, ":") != false) {
                        $firstHalf = substr($key, 0, strpos($key, ":"));
                        $secondHalf = substr($key, strpos($key, ":") + 1);
                        $headerMap[$firstHalf] = trim($secondHalf);
                    }
                }
                $jsonResponse = json_decode($content, true);


                if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
                    list ($headers, $content) = explode("\r\n\r\n", $content, 2);
                    $jsonResponse = json_decode($content, true);
                } 
                
                var_dump($headerMap);
                var_dump($jsonResponse);
                var_dump($responseInfo['http_code']);
                 
                //(new CreateBulkReadjob())->execute();

                  /* list ($readStatus, $readID)= createBulkReport();
                    echo "STATUS: ".$readStatus . "<br>";
                    echo  "ID: ". $readID . "<br>";
                    
                    $r = createBulkReport();
                    echo $r[0];

                    if ($readStatus =="success"){

                        do{
                            list($checkID, $checkState, $check_url) = checkBulkReport($readID);
                            echo "Check ID: ".$checkID."<br>";
                            echo "Check State: ".$checkState."<br>";
                            echo "Download URL: ".$check_url."<br>";
                        }
                        while($checkState=="COMPLETED");
                        echo "<br>Status: ".$checkState;
                    }
                    else{
                        echo "not success";
                    }    */ 
?>
</body>
</html>

