#!/bin/bash

INPUT=/var/www/html/monitoring/screenconnect.csv
sed -e 's/"//g' $INPUT > screenconnect.csv
OLDIFS=$IFS
IFS=','
[! -f $INPUT] && { echo "$INPUT file not found"; exit 99; }
while read Name GuestInfoUpdateTime GuestScreenshotContent
do 
    
    convert = $GuestScreenshotContent | base64 -d 
    echo convert > /var/www/html/monitoring/Thumbs/$Name.jpg
    
done < $INPUT
IFS=OLDIFS