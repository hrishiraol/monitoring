<?php



    function getScreenCSV(){
        $url="http://hrishi:HRa095!@screens.ur-channel.com/Report.csv?ReportType=Session&GroupFields=Name&GroupFields=GuestInfoUpdateTime&GroupFields=GuestScreenshotContent&GroupFields=IsEnded&SelectFields=Count&Filter=NAME%20LIKE%20'%25'%0A%0A&AggregateFilter=&ItemLimit=10000";
        $source = file_get_contents($url);
        file_put_contents('screenconnect.csv', $source);
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

    function printArray(){
        //getScreenCSV();
        $printArray = readScreenCSV();    
        foreach($printArray as $a){
            foreach($a as $v){
                echo $a['Machine'] ;
                echo $a['LastUpdate'];
                echo "<img src=".$a['Image'].">";
                echo $a['Status'];
                break;
            }
            echo "<br>\n";
        }
    }

   

?>
<html>
<head>
<title>ScreenConnect</title>
</head>
<body>
<h1>Screen Connect</h1>
<?php
        printArray();
?>
</body>
</html>
<?php

 /* foreach ($printArray as $a=>$b){
            foreach ($b as $c=>$d){
                echo "<td>$d</td>";
            }
            echo "</tr>";
        }
        echo "</table>"; */
?>
