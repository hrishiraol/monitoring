<?php
    function readZohoCSV(){
        $filename = "fromzoho1.csv";
        
        $delimiter =',';
        if(!file_exists($filename)||!is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        
        if (($handle = fopen($filename, 'r'))!==FALSE)
        {            
            while(($row=fgetcsv($handle, 30000, $delimiter))!==FALSE)
            {                   
                $new_row = array("PlayerName"=>$row[0],
                            "AccountName"=> $row[1],
                            "SerialNumber"=> $row[5]                                                  
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

    function readScreenCSV(){
        $filename = "fromscreenconnect.csv";
        
        $delimiter =',';
        if(!file_exists($filename)||!is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        
        if (($handle = fopen($filename, 'r'))!==FALSE)
        {            
            while(($row=fgetcsv($handle, 75000, $delimiter))!==FALSE)
            {                
                $new_row = array("MachineName"=> $row[0],
                            "SerialNumber"=> $row[5]                                                                              
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

    

    function getSCSerial ($zohoSerial){
        $scArray = readScreenCSV();
        foreach ($scArray as $a){   
            foreach ($a as $v){  
                $scPlayerName= $a['MachineName'];
                $scSerial = $a['SerialNumber'];     
                $work=null;
                if(stristr($zohoSerial,$scSerial)){
                    //echo $scPlayerName."\t";
                    $work= stristr($zohoSerial,$scSerial);
                    break;
                }      
                return $work;                     
            }
        }
    }

    function getZohoSerial(){
        $zohoArray = readZohoCSV();
        foreach ($zohoArray as $b){
            foreach($b as $x){ 
                
            }
        }
    }



    function scSerialNo(){        
        $scArray = readScreenCSV();
        $zohoArray = readZohoCSV();

        foreach ($scArray as $a){   
            foreach ($a as $v){                   
                $scPlayerName= $a['MachineName'];
                $scSerial = $a['SerialNumber'];                   
                
                    foreach ($zohoArray as $b){
                        foreach($b as $x){                            
                            $zohoPlayerName = $b['PlayerName'];
                            $zohoSerial = $b['SerialNumber'];                
                            
                            if(stristr($zohoSerial,$scSerial)){
                                echo "Player Name: " .$scPlayerName."\t";
                                echo "Serial No: ". stristr($zohoSerial,$scSerial);                                
                            }                            
                            break;
                        }
                    }
                break;                
            }
            echo "<br>\n";    
        }
                
    }

    function printArray(){
        $printArray = readZohoCSV();    
        foreach($printArray as $a){
            foreach($a as $v){
                echo $a['PlayerName']."\t";
                echo $a['AccountName']."\t";
                echo $a['SerialNumber'];                               
                break;
            }
            echo "<br>\n";
        }
    }

    function printArray1(){
        $printArray = readScreenCSV();    
        foreach($printArray as $a){
            foreach($a as $v){
                echo $a['MachineName']."\t";
                echo $a['SerialNumber']."\t";                                
                break;
            }
            echo "<br>\n";
        }
    }

   



?>

<html>
<head>
<title>CSV Working</title>
</head>
<body>
<h1>CSV Data</h1>
<?php
        echo getSCSerial ("211SHWA78473");
        //scSerialNo();
        //printArray();
        //printArray1();
?>
</body>
</html>
