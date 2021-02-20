<?php

function convertToJson($fname){            
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
            
            $jfp = fopen("open.json","w");
            fwrite($jfp, json_encode($json));
            fclose($jfp);
               // encode array to json
               // return json_encode($json); 
        }


        If(convertToJson("open.csv")){
            echo "converted";
        }
        ?>