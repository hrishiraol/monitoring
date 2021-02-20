<html>
<title>Learning</title>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>

            function loadDoc() {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("demo").innerHTML =
                        this.responseText;
                    }
                };
                xhttp.open("GET", "../json/2648626000021818005.json", true);
                xhttp.send();
            }
        

                            
            /*
            $(document).ready(function(){
            
            });

            $("button").click(function(){
                $("p").hide("slow", function(){
                    alert("The paragraph is now hidden");
                });
            });

            //Hide - Show with speed: slow", "fast", or milliseconds.
            //$(selector).hide(speed,callback);
            //$(selector).show(speed,callback);

            $("#hide").click(function(){
                $("p").hide();
            });

            $("#show").click(function(){
                $("p").show();
            });   

            ("button").click(function(){
                $("p").hide(500);
            });
            $("#show").click(function(){
                $("p").show(500);
            });         
            
             //Doubleclick
            $("p").dblclick(function(){
                $(this).hide();
            });


            //Mouse - Enter, Leave, Click
            $("p").on({
                mouseenter: function(){
                    $(this).css("background-color", "lightgray");
                },
                mouseleave: function(){
                    $(this).css("background-color", "lightblue");
                },
                click: function(){
                    $(this).css("background-color", "yellow");
                }
            });
            //Mouse - Hover            
            $("#p1").hover(function(){
                alert("You entered p1!");
                },
                function(){
                alert("Bye! You now leave p1!");
            });
            $("#p1").mouseenter(function(){
                alert("You entered p1!");
            });
            $("#p1").mouseleave(function(){
                alert("Bye! You now leave p1!");
            }); */
        
    </script>
</head>
<body>
<h3>Learning</h3>
<body>

<div id="demo">
    <h2>The XMLHttpRequest Object</h2>
    <button type="button" onclick="loadDoc()">Change Content</button>
</div>

</body>
  
</body>
</html>