<html>
<title>Bulk API - Oauth</title>
<body>
<h1>Create Bulk Report</h1>
    
    <form action="" method="post">
        <input type="submit" name="refreshToken" value="Refresh" /><br>
        <input type="submit" name="createBulkReport" value="Create" /><br>
        <input type="submit" name="checkBulkReport" value="Check" /><br>
        <input type="submit" name="downloadBulkReport" value="Download" /><br>        
    </form>

    <?php     
            if(isset ($_POST['createBulkReport'])){  
                createBulkReport();    
            }

            if(isset ($_POST['checkBulkReport'])){  
                checkBulkReport();   
            } 

            if(isset ($_POST['downloadBulkReport'])){
                downloadBulkReport();           
            }
            if(isset ($_POST['refreshToken'])){
                RenewRefreshToken();           
            }

        /*
            when the page loads, initaite first function - createBulkReport()
                if access token is invalid
                    then RenewalRefreshToken() && createBulkReport()
            
                wait for 30 sec to checkBulkReport()
                    keep checking checkBulkReport every 30 sec till it returns status - completed
            
                
                then downloadBulkReport()                          

        */ 


            //createBulkReport();

            if (checkForAccess() =="INVALID_TOKEN"){
                echo "Code: ".checkForAccess();
                RenewRefreshToken();
                createBulkReport();
                checkBulkReport();
                sleep (60);
                downloadBulkReport();
            }   else {
                checkBulkReport();
                sleep (60);
                downloadBulkReport();
            }
            
            
            

        function checkForAccess(){
            $checktoken = file_get_contents("jobid0.json");
            $array = json_decode($checktoken);         
            $codeStatus = $array->code;
            
            return $codeStatus;
            
            //var_dump($array);            
            //echo "\nCode from job:  ".$codeStatus."<br>";
            //echo "CODE: ". $array['data'][0]['code']."<br>";
            
            /*
            $valid = "Valid code";
            $invalid = "Invalid code";
            
            if ($codeStatus="INVALID_TOKEN"){  
                return $invalid;
            } else {
                return $valid;
            } */
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
            $filename= 0;            
            
            foreach ($query as $queryjob){          

                $ch = curl_init();            
                $curl_options = array();
                $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read";
                $curl_options[CURLOPT_RETURNTRANSFER] = true; 
                $curl_options[CURLOPT_HEADER] = 0; //Get Header details 0=false
                $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
                $curl_options[CURLOPT_POSTFIELDS]= $queryjob;                
                $headersArray = array();
                $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " . $access_Token;
                $headersArray[] = "Content-Type".":"."application/json";
                $curl_options[CURLOPT_HTTPHEADER]=$headersArray;
                curl_setopt_array($ch, $curl_options);
                $result = curl_exec($ch);    
                curl_close($ch);   
                
                $fname= "jobid".$filename.".json";               
                file_put_contents($fname, $result);          
                $filename ++;
                $query1 = array();
                $query1[]= file_get_contents($fname);               
            }                          
        }         


        function RenewRefreshToken(){
            //echo "\nRenerefreshed called";

            $readJson = file_get_contents("zohoauth.json");
            $array = json_decode($readJson);
    
            $client_id = $array->client_id;
            $client_secret = $array->client_secret;
            $refresh_token = $array->refresh_token;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://accounts.zoho.com/oauth/v2/token" );
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'client_secret' => $client_secret,                
                'grant_type' => 'refresh_token'
                ));

            $response = curl_exec($ch);
            file_put_contents("zoho-authtoken.json", $response); 
            
            //echo "Token Refreshed: ".json_decode($response)->access_token;

            //var_dump($response); 
            //return json_decode($response)->access_token;  
            //header("Location: getzohodata.php");  
        
        }              

        function checkBulkReport(){

           //echo "\nCheckBulkReport";
            
            $readaccessToken = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readaccessToken);
            $access_Token = $array->access_token;      

            $readID = JobID();            
            $filename= 0;            

            foreach($readID as $jobID){

                $jobidcheck = $jobID['ID'];            
                $ch = curl_init();            
                $curl_options = array();
                $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read/". $jobidcheck;            
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
                
                $fname= "checkjobid".$filename.".json";   
                file_put_contents($fname, $result); 
                $filename ++;                
                $query = array();
                $query[]= file_get_contents($fname);

                foreach ($query as $queryjob){
                    echo "Details"."<br>";
                    $jsonResponse = json_decode($queryjob);           
                    $id = $jsonResponse->data[0]->id;
                    $state =  $jsonResponse->data[0]->state;
                    $d_url = $jsonResponse->data[0]->result->download_url;
                    echo "Status: ". $state."<br>";
                    echo "JOB ID: ". $id."<br>";
                    echo "D URL : ". $d_url."<br>";                    
                }  
            }         
        }

        function checkReportStatus(){
            $checkStatus = file_get_contents("checkjobid0.json");
            $array = json_decode($checkStatus);         
            $reportStatus = $array->data[0]->state;
            
            return $reportStatus;
        }


        function downloadBulkReport(){
            
            $readaccessToken = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readaccessToken);
            $access_Token = $array->access_token;   

            $readID = JobID();            
            $filename= 0;

            foreach($readID as $jobID){

                $jobidcheck = $jobID['ID'];                     
                $ch = curl_init();                
                $curl_options = array();
                $curl_options[CURLOPT_URL] = "https://www.zohoapis.com/crm/bulk/v2/read/". $jobidcheck."/result";
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
                $filepath= $directory."csv".$filename.".zip";                   
                file_put_contents($filepath, $result);
                extractZipArchive($filepath, "csvfiles/");     
                convertToJson("csvfiles/".$jobidcheck.".csv", $jobidcheck);

                $filename++;
            }            
            echo "Json created"."<br>";
            changeFile();
        }

        function convertToJson($fname, $jid){            
            if (!($fp = fopen($fname, 'r'))) {
                die("Can't open file...");
            }         
            //read csv headers
            $key = fgetcsv($fp,"1024",",");            
            // parse csv rows into array
            $json = array();
                while ($row = fgetcsv($fp,"1024",",")) {
                $json[] = array_combine($key, $row);
            }            
            // release file handle
            fclose($fp);
            
            $jfp = fopen("json/".$jid.".json","w");
            fwrite($jfp, json_encode($json));
            fclose($jfp);
               // encode array to json
               // return json_encode($json); 
        }

        function JobID(){
            $queryaccntapi = file_get_contents("jobid0.json");
            $queryequipapi = file_get_contents("jobid1.json");
            
            $query = array();
            $query[]= $queryaccntapi;
            $query[]= $queryequipapi;
            
            foreach ($query as $queryjob){    
                $jsonResponse = json_decode($queryjob);           
                $status =  $jsonResponse->data[0]->status;
                $id = $jsonResponse->data[0]->details->id;
                $jobarray[] = array(
                            'ID'=>$id,
                            'Status'=>$status
                );
            }            
            return $jobarray;            
        }

        function changeFile(){
            $accntID = file_get_contents("jobid0.json");
            $equipID = file_get_contents("jobid1.json");
            $jsonconvert1 = json_decode($accntID );           
            $jsonconvert2 = json_decode($equipID);
            $id1 = $jsonconvert1->data[0]->details->id;
            $id2 = $jsonconvert2->data[0]->details->id;
            
            $accntPath = "json/".$id1.".json";
            $equipPath = "json/".$id2.".json";

            if (file_exists($accntPath)){
                rename ($accntPath, "json/account.json");
                rename ($equipPath, "json/equiplist.json");
                echo "Files Renamed !!!"."<br>";
            } else{
                echo "Files does not Exists!!";
            }
        }


        function extractZipArchive($archive, $destination){
            $zip = new ZipArchive();            
            // Check if archive is readable.
            if($zip->open($archive) === TRUE){
                // Check if destination is writable
                if(is_writeable($destination)){
                    $zip->extractTo($destination);
                    $zip->close();
                   //echo 'Files unzipped successfully';                    
                }else{
                    //echo 'Directory not writeable by webserver.';                    
                }
            }else{
                //echo 'Cannot read '. $archive.'  archive.';                
            }
        }
        ?>
</body>
</html>