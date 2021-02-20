<html>
<title>Create Bulk Report API</title>
<body>
<h1>Create Bulk Report</h1>
    
    <form action="" method="post">
        <input type="submit" name="createBulkReport" value="Create" /><br>
        <input type="submit" name="checkBulkReport" value="Check" /><br>
        <input type="submit" name="downloadBulkReport" value="Download" /><br>        
    </form>

    <?php     

        if(isset ($_POST['createBulkReport'])){  

            createBulkReport();            
            checkForAccess();
            $queryaccntapi = file_get_contents("jobid0.json");
            $queryequipapi = file_get_contents("jobid1.json");
            
            $query = array();
            $query[]= $queryaccntapi;
            $query[]= $queryequipapi;

            foreach ($query as $queryjob){                
                echo "Details"."<br>";
                $jsonResponse = json_decode($queryjob);           
                $status =  $jsonResponse->data[0]->status;
                $id = $jsonResponse->data[0]->details->id;
                echo "Status: ". $status."<br>";
                echo "JOB ID: ". $id."<br>";
            }    
        }   

        if(isset ($_POST['checkBulkReport'])){  
            checkBulkReport();            
            $queryaccntapi = file_get_contents("checkjobid0.json");
            $queryequipapi = file_get_contents("checkjobid1.json");
            
            $query = array();
            $query[]= $queryaccntapi;
            $query[]= $queryequipapi;

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

        if(isset ($_POST['downloadBulkReport'])){
            downloadBulkReport();           
        }       

        function createBulkReport(){

            $readJson = file_get_contents("zoho-authtoken.json");
            $array = json_decode($readJson);
            $access_Token = $array->access_token;
            echo "Code: ".$code."<br>";
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
                file_put_contents("jobid".$filename.".json", $result);          
                $filename ++;
            }
        }

        function checkBulkReport(){

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
                file_put_contents("checkjobid".$filename.".json", $result); 
                $filename ++;
            }             
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
                //echo $jobidcheck;
                convertToJson("csvfiles/".$jobidcheck.".csv", $jobidcheck);
                $filename++;
            }            
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
            
            $jfp = fopen("../json/".$jid.".json","w");
            fwrite($jfp, json_encode($json));
            fclose($jfp);
               // encode array to json
               //return json_encode($json);                    
        }

        function checkForAccess(){
            $checktoken = file_get_contents("jobid0.json");
            $array = json_decode($checktoken);
            //var_dump($array);
            $code =  $array->code;

            echo "Code: ".$code."<br>";
            if ($code=="INVALID_TOKEN"){
                echo "Token is invalid"."<br>"; 
                echo "Code: ".$code."<br><br>";
                echo "call refresh page"."<br>";
            }
            else{
                echo "all is fine.";
            }
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
