<?php
    
    include "generateaccount.php";
    //include "getscreenconnect.php";


$doc = new DOMDocument('1.0','utf-8');

//path for the zoho json files
$accountPath = "oauth/json/account.json";
$equipPath = "oauth/json/equiplist.json";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Monitoring Page</title>
    <link rel="stylesheet" href="css/monitorpage.css">
</head>

<body>
        <div class="hwrapper">            
            <div class="hbanner">
                <img src="images/ur-channel-header.jpg" style="float:left;width:160px;height:48px;">
                <p>Monitoring Page</p>                
            </div>
        </div>  
        <div class="topnav">    
        </div>	        
        <div class="bwrapper">        
            <?php
                
                $statusOpen="Open";
                $equipStatus="Installed";                
               
                // Filtering arrays and adding player status
                $accntDets = filterStoreStatus($accountPath,$statusOpen);
                $equipDets = filterInstalled($equipPath, $equipStatus);

                $displayContainer= $doc->createElement("div");
                $displayContainer->setAttribute("class", "displayContainer");
                 
                foreach ($accntDets as $key){                         
                        $displayRow=displayRow($doc, $key['Account_Name'], $key['Parent_Account.Account_Name'],$key['Shipping_City'],
                                    $key['Shipping_State'], $key['Shipping_Country'],
                                    $key['Store_Status'],$key['Location_Email'], $key['Number_of_Menu_Panels'] );                           
                    foreach($equipDets as $key1){               
                        if($key['Account_Name']==$key1['Site.Account_Name']){
                                $displayRowCard = displayRowCard($doc, $key1['Site.Account_Name'], $key1['Configuration'], 
                                            $key1['Player_Name'], $key1['Item_Name'], 
                                            $key1['Name'], $key1['Last_Update'], $key1['SC_Status'],$key1['Orientation']);
                                    $displayRow->appendChild($displayRowCard);                       
                        }                
                    }               
                    $displayContainer->appendChild($displayRow);
                }
                
                echo $doc->saveHTML($displayContainer);  

                                
                function readScreenCSV(){
                    $filename = "screenconnect.csv";
                    
                    $delimiter =',';
                    if(!file_exists($filename)||!is_readable($filename))
                        return FALSE;
                    $header = NULL;
                    $data = array();
                    
                    if (($handle = fopen($filename, 'r'))!==FALSE)
                    {            
                        while(($row=fgetcsv($handle, 30000, $delimiter))!==FALSE)
                        {                
                            $ctime = convertTime($row[1]);
                            $cstatus = checkStatus($ctime);
        
                            $new_row = array("Machine"=> $row[0],
                                            "LastUpdate"=> $ctime,
                                            "Image"=> base64ToImage($row[2], "Thumbs/".$row[0].'.jpg'),
                                            "Status"=>$cstatus                
                                            );       
                        
                            if(!$header){
                                $header = $new_row;
                            }
                            else {
                                $data[]=$new_row;
                            }
                        }            
                        fclose($handle);
                    }      
                    return $data;
                }  
        
                function convertTime($reportTime){
                    if($reportTime=="GuestInfoUpdateTime"){
                        return "Not Date";
                    }
                    else{
                        $datetime = new DateTime(str_replace("/","-",$reportTime));
                        $datetime-> sub(new DateInterval('PT4H'));
                        $printDate = $datetime-> format('Y-m-d G:i');
                        return $printDate;
                    }
                }
                // Online-Offline status 20 mins ago
                function checkStatus($lastUpdate){
                    $last = strtotime($lastUpdate);
        
                    $today = date ("Y-m-d G:i", strtotime('-20 minutes'));
                    $tod = strtotime($today);
                    if ($last<= $tod){
                        return "Offline";            
                    } else {
                        return "Online";
                    }
                }
                //converting base4 to jpg
                function base64ToImage($base64_string, $output_file){
                    $file = fopen ($output_file, "wb");
                    $data = explode(',',$base64_string);
                    fwrite($file, base64_decode($data[0]));
                    fclose($file);
                    return $output_file;
                }

                //FILTER ACCOUNT ARRAY WITH STORE STATUS = OPEN
                function filterStoreStatus($accountPath, $statusOpen){
                    $statusArray = array_filter(accountArray($accountPath),function($value) use($statusOpen){
                        return ($value['Store_Status']==$statusOpen);
                    });
                    array_multisort(array_column($statusArray, "Account_Name"), SORT_ASC, $statusArray );
                    return $statusArray;
                } 
                
                
                    //FILTER and MERGING EQUIPMENT ARRAY WITH SCREENCONNECT DATA, NAGIOS DATA
                function filterInstalled($equipPath, $equipStatus){

                    //FILTER EQUIPMENT ARRAY EQUIPMENT STATUS = INSTALLED
                    $installArray = array_filter(equipArray($equipPath), function($value1) use($equipStatus){
                        return ($value1['Status']==$equipStatus);
                    });
                    
                        //filter array with correct player name > 5 character lookup                            
                    $playArray= array_filter($installArray, function($value2) {
                        return (strlen($value2['Player_Name'])>5);
                    });   
                        //inserting last update,status from screenconnect array to zoho array
                    $mergedArray = addPlayerStatus($playArray);
                
                    return $mergedArray;
                }    

                               
                function addPlayerStatus ($zohoArray){
                    $screenArray = readScreenCSV();  
                    $tmp=array();                    
                    foreach($zohoArray as $key1=>$value1){
                        foreach ($screenArray as $key2=>$value2){
                            if ($value1['Player_Name']==$value2['Machine']){
                                $tmp [$key1]=$value1;
                                $tmp [$key1]['Last_Update'] = $value2['LastUpdate'];
                                $tmp [$key1]['SC_Status'] = $value2['Status'];                    
                            }
                        }
                    }                        
                    //array_merge($zohoArray,$tmp);
                    //var_dump($zohoArray);
                    //echo "<pre>";
                    //var_dump($tmp);
                    //echo "</pre>";                    
                    return $tmp;
                }      
                
                

                /* $myarr = equipArray($equipPath);
                foreach ($myarr as $key){           
                    foreach ($key as $v=>$r){                
                        echo $v. " : ".$r."<br>";
                    }   
                    echo "<br>";  
                }
                 Working :
                $accntDets = accountArray($accountPath);
                foreach ($accntDets as $key){               
                    $displayRow=displayRow($doc, $key['Account_Name'], $key['Parent_Account.Account_Name'],$key['Shipping_City'],
                                    $key['Shipping_State'], $key['Shipping_Country'],
                                $key['Store_Status'],$key['Location_Email'], $key['Number_of_Menu_Panels'] );
                    $displayContainer->appendChild($displayRow);
                }
                echo $doc->saveHTML($displayContainer); */

            ?>                
        </div>
</body>

</html>
