<html>
<title>Merge Data</title>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<style>
        table 
        {
            width: 100%;
            font: 17px Calibri;
        }
        table, th, td 
        {
            border: solid 1px #DDD;
            border-collapse: collapse;
            padding: 2px 3px;
            text-align: center;
        }
    </style>
</head>
<body>
<h3>Data extracted from External JSON file.</h3>
    <div id='showTable'></div>
</body>
    <script>

    $(document).ready(function () {
        
        $.getJSON("../json/2648626000021818005.json", function (data) {

            var arrItems = [];      // THE ARRAY TO STORE JSON ITEMS.
            $.each(data, function (index, value) {
                arrItems.push(value);       // PUSH THE VALUES INSIDE THE ARRAY.
            });

            // EXTRACT VALUE FOR TABLE HEADER.
            var col = [];
            for (var i = 0; i < arrItems.length; i++) {
                for (var key in arrItems[i]) {
                    if (col.indexOf(key) === -1) {
                        col.push(key);
                    }
                }
            }

            // CREATE DYNAMIC TABLE.
            var table = document.createElement("table");

            // CREATE HTML TABLE HEADER ROW USING THE EXTRACTED HEADERS ABOVE.

            var tr = table.insertRow(-1);                   // TABLE ROW.

            for (var i = 0; i < col.length; i++) {
                var th = document.createElement("th");      // TABLE HEADER.
                th.innerHTML = col[i];
                tr.appendChild(th);
            }

            // ADD JSON DATA TO THE TABLE AS ROWS.
            for (var i = 0; i < arrItems.length; i++) {

                tr = table.insertRow(-1);

                for (var j = 0; j < col.length; j++) {
                    var tabCell = tr.insertCell(-1);
                    tabCell.innerHTML = arrItems[i][col[j]];
                }
            }

            // FINALLY ADD THE NEWLY CREATED TABLE WITH JSON DATA TO A CONTAINER.
            var divContainer = document.getElementById("showTable");
            divContainer.innerHTML = "";
            divContainer.appendChild(table);
        });
    });

        /*  Create XMLHttpRequest object.
        var oXHR = new XMLHttpRequest();
        // Initiate request.
        oXHR.onreadystatechange = reportStatus;
        oXHR.open("GET", "../json/2648626000021818005.json", true);  // get json file.
        oXHR.send();

        function reportStatus() {
            if (oXHR.readyState == 4) {		// Check if request is complete.
                //Write data to a DIV element.
                //document.getElementById('showTable').innerHTML = this.responseText;
                makeTableHTML(this.responseText);
            }
        }

        function makeTableHTML(myArray) {
            for (var i in myArray){
                console.log("row "+i);
                for (var j in myArray[i]){
                    console.log(" "+myArray[i][j]);
                }
            }
            
            //document.body.appendChild(table);
        }    */     
</script>  


</hmtl>