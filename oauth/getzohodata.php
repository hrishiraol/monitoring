<html>
<title>Bulk API - Oauth</title>
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
        }
        
        if(isset ($_POST['checkBulkReport'])){  
            checkBulkReport();   
        } 

        if(isset ($_POST['downloadBulkReport'])){
            downloadBulkReport();           
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

            if(!checkForAccess()){
                //header("Location: RefreshToken.php");                 
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
                    
                    foreach ($query1 as $queryjob1){                                          
                        $jsonResponse = json_decode($queryjob1);                          
                        $status =  $jsonResponse->data[0]->status;
                        $id = $jsonResponse->data[0]->details->id;
                        echo "Status: ". $status."<br>";
                        echo "JOB ID: ". $id."<br>";
                    }
                }
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

        function checkForAccess(){
            $checktoken = file_get_contents("jobid0.json");
            $array = json_decode($checktoken);
            //var_dump($array);
            $code =  $array->code;
            
            if ($code=="INVALID_TOKEN")
                echo "Token is invalid"."<br>"; 
                return false;            
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