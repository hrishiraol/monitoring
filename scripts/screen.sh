#!/bin/bash
curl -o /var/www/html/monitoring/screenconnect.csv -u administrator:RaduAdmin2020! "http://screens.ur-channel.com/Report.csv?ReportType=Session&GroupFields=Name&GroupFields=GuestInfoUpdateTime&GroupFields=GuestScreenshotContent&GroupFields=IsEnded&SelectFields=Count&Filter=NAME%20LIKE%20'%25'%0A%0A&AggregateFilter=&ItemLimit=10000"


