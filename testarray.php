<?php
include "generateaccount.php";




$doc = new DOMDocument('1.0','utf-8');

$accountPath = "oauth/json/account.json";
$equipPath = "oauth/json/equiplist.json";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Array Test</title>   
</head>

<body>
        
      <h1>Test Array</h1>      
        <div class="bwrapper">        
            <?php

                $displayContainer= $doc->createElement("div");
                $displayContainer->setAttribute("class", "displayContainer");
                
                $statusOpen="Open";
                $equipStatus="Installed";
                
                //FILTER ACCOUNT ARRAY WITH STORE STATUS = OPEN
                function filterStoreStatus($accountPath, $statusOpen){
                    $statusArray = array_filter(accountArray($accountPath),function($value) use($statusOpen){
                        return ($value['Store_Status']==$statusOpen);
                    });
                    return $statusArray;
                }  
                 
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

                ////SORT ACCOUNT ARRAY BY ACCOUNT_NAME
                //array_multisort( array_column($equipDets, "Site.Account_Name"), SORT_ASC, $equipDets);
                
                function listArray($equipDets){
                   //array_multisort( array_column($equipDets, "Site.Account_Name"), SORT_ASC, $equipDets);

                    $order = array("Menu Boards","Next Gen Coffee Bar","Slurpee TV","Promotional Display","Infotainment","Order Confirmation Boards","UR-Tunes","Video Wall");
                    $equipDets = sortByPriority($equipDets, $order);

                    foreach ($equipDets as $t){
                        foreach($t as $h){
                                           
                        }
                        echo $t['Site.Account_Name']."\t";
                        echo $t['Configuration']."\t";
                        echo $t['Status']."\t";
                        echo $t['Player_Name']."\t";
                        echo $t['Last_Update']."\t";
                        echo $t['SC_Status']."\n";
                        echo "<br>";
                    }                
                }

                listArray(filterInstalled($equipPath, $equipStatus));

                function sortByPriority($data , $keys){                   
                                    
                    $priority = array();
                    $i = count($keys);
                    //array_flip($keys);
                    foreach ($keys as $key => $value) {
                      $i--;
                      $priority[$value] = $i;
                    }
                    usort($data, function($a, $b) use($priority){
                      $a = isset($priority[$a->key]) ? $priority[$a->key] : -1;
                      $b = isset($priority[$b->key]) ? $priority[$b->key] : -1;
                      return $b - $a;
                    });
                
                    return $data;
                }

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
            
                function base64ToImage($base64_string, $output_file){
                    $file = fopen ($output_file, "wb");
                    $data = explode(',',$base64_string);
                    fwrite($file, base64_decode($data[0]));
                    fclose($file);
                    return $output_file;
                }

                /* Configuration :
                    1. Menu Boards
                    2. Next Gen Coffee Bar
                    3. Slurpee TV
                    4. Promotoinal Display
                    5. Infotainment
                    6. Order Confirmation Boards                    
                    7. UR-Tunes
                    8. Video Wall


                */
                function prnt($array){

                    echo "<pre>";
                    print_r($array);
                    echo "</pre>";

                }
                //prnt($equipDets);

                /* foreach ($accntDets as $key){                         
                        $displayRow=displayRow($doc, $key['Account_Name'], $key['Parent_Account.Account_Name'],$key['Shipping_City'],
                                    $key['Shipping_State'], $key['Shipping_Country'],
                                    $key['Store_Status'],$key['Location_Email'], $key['Number_of_Menu_Panels'] );                           
                    foreach($equipDets as $key1){               
                        if($key['Account_Name']==$key1['Site.Account_Name']){
                            
                          //  if (($key1['Status'] =="Installed")){
                                //$statusCheck = installedStatus($key1['Status']);                                
                                $displayRowCard = displayRowCard($doc, $key1['Site.Account_Name'], $key1['Configuration'], 
                                        $key1['Player_Name'], $key1['Item_Name'], 
                                        $key1['Name'], $key1['Status'], $key1['Orientation']);
                    
                                $displayRow->appendChild($displayRowCard);
                            //}
                        }                
                    }               
                    $displayContainer->appendChild($displayRow);
                }
                echo $doc->saveHTML($displayContainer);   */
            ?>                
        </div>
</body>

</html>
<?php
                /* $myarr = equipArray($equipPath);
                foreach ($myarr as $key){           
                    foreach ($key as $v=>$r){                
                        echo $v. " : ".$r."<br>";
                    }   
                    echo "<br>";  
                }  */
                 /* Working :
                $accntDets = accountArray($accountPath);
                foreach ($accntDets as $key){               
                    $displayRow=displayRow($doc, $key['Account_Name'], $key['Parent_Account.Account_Name'],$key['Shipping_City'],
                                    $key['Shipping_State'], $key['Shipping_Country'],
                                $key['Store_Status'],$key['Location_Email'], $key['Number_of_Menu_Panels'] );
                    $displayContainer->appendChild($displayRow);
                }
                echo $doc->saveHTML($displayContainer); 
                
                
                
                //FILTER EQUIPMENT ARRAY FOR PLAYERNAME > 5 CHARACTERS
                function filterPlayerName($farray){
                    $playArray= array_filter($farray, function($value2) {
                        return (strlen($value2['Player_Name'])>5);
                    });
                    return $playArray;
                }    
                */


                

?>
