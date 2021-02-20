<?php

    //include "getscreenconnect.php";
    
    

    function accountArray($jsonPath){
        $json = file_get_contents($jsonPath);
        $jsonArray  = json_decode($json, true);        
        return $jsonArray;     
    }    
    
    function equipArray($jsonPath){
        $json = file_get_contents($jsonPath);
        $jsonArray  = json_decode($json, true);        
        return $jsonArray;     
    }
    
    function displayRow($doc, $account_name, $parent_account, $shipping_city, $shipping_state, 
                    $shipping_country, $store_status, 
                    $location_email, $number_of_menu_panels) {
                    
        $displayRow = $doc->createElement("div");
        $displayRow->setAttribute("class","displayRow");

            $displayRowHeader = $doc->createElement("div");
            $displayRowHeader->setAttribute("class", "displayRowHeader");
            $displayRow->appendChild($displayRowHeader);

                $spanAccountName = $doc->createElement("span", $account_name);
                $spanAccountName->setAttribute("class", "accountName");
                $displayRowHeader->appendChild($spanAccountName);

                $spanDetails = $doc->createElement("span", $shipping_city. " | " 
                                                .$shipping_state. " | "
                                                .$shipping_country 
                                                );
                $spanDetails->setAttribute("class", "accountDetails");
                $displayRowHeader->appendChild($spanDetails);

                $spanStatus = $doc->createElement("span", "Store Status: ".$store_status);
                $spanStatus->setAttribute("class", "accountStatus");
                $displayRowHeader->appendChild($spanStatus);

                $spanParent = $doc->createElement("span", "Parent Account: ".$parent_account. "&nbsp;&nbsp;&nbsp;Screens: ".$number_of_menu_panels);
                $spanParent->setAttribute("class", "parentAccount");
                $displayRowHeader->appendChild($spanParent);

        return $displayRow;       
    }

    function displayRowCard($doc, $account_name, $configuration, $player_name, 
                                $item_name, $name, $last_update, $sc_status, $orientation){

        $displayRowCard = $doc->createElement("div");
        $displayRowCard->setAttribute("class", "displayRowCard");

            $displayCard=$doc->createElement("div");
            $displayCard->setAttribute("class","displayCard");
            $displayRowCard->appendChild($displayCard);

                $spanCardPname = $doc->createElement("span",$player_name );
                $spanCardPname->setAttribute("class", "cardHeader");
                $displayCard->appendChild($spanCardPname);

                $spanCardConfig = $doc->createElement("span",$configuation);
                $spanCardConfig->setAttribute("class", "cardConfig");
                $displayCard->appendChild($spanCardConfig);

                $displayCardBody = $doc->createElement("div");
                $displayCardBody->setAttribute("class", "displayCardBody");
                $displayCard->appendChild($displayCardBody);

                    $link = $doc->createElement("a");
                    $link->setAttribute("href","http://screens.ur-channel.com/Host#Access/All%20Machines/".$player_name);
                    $link->setAttribute("title",$player_name);
                    $link->setAttribute("target","_blank");
                    $displayCardBody->appendChild($link);

                        $himg = $doc->createElement("img");
                        $himg->setAttribute("src","Thumbs/".$player_name.".jpg"); 
                        $himg->setAttribute("class", checkOrient($orientation)); //add check orientation method
                        $himg->setAttribute("title", $player_name);
                        $link->appendChild($himg);

                /* $spanMacStatus = $doc->createElement("span", "Status: ".$status); //add check online status method
                $spanMacStatus->setAttribute("class", "machStatus");
                $displayCard->appendChild($spanMacStatus);
                
                $br=$doc->createElement("br");
                $displayCard->appendChild($br); */
                
                $spanNagiosConnect = $doc->createElement("span","Nagios Status: ");
                $spanNagiosConnect->setAttribute("class", "nagiosConnect");
                $displayCard->appendChild($spanNagiosConnect);

                $br=$doc->createElement("br");
                $displayCard->appendChild($br);
                
                $spanScreenConnect = $doc->createElement("span","SC Status: ".$last_update. " | ". $sc_status);
                $spanScreenConnect->setAttribute("class", "screenConnect");
                $displayCard->appendChild($spanScreenConnect);
                
                $br=$doc->createElement("br");
                $displayCard->appendChild($br);

                /* $spanMoreInfo = $doc->createElement("span","More Info");
                $spanMoreInfo->setAttribute("class", "moreInfo");
                $displayCard->appendChild($spanMoreInfo); */

        return $displayRowCard;
    }

    function checkOrient($orient) {
        if($orient=="Portrait") {
            return "imgPortrait";						
        } else {
            return "imgLandscape";
        }
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