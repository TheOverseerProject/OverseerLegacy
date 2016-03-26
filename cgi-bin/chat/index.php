<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>The Overseer Chat</title>
<link href="core.css?<?php echo date('l_jS_\of_F_Y_h:i:s_A'); ?>" rel="stylesheet"/>
<script src="chat.js?<?php echo date('l_jS_\of_F_Y_h:i:s_A'); ?>" type="text/javascript"></script>
<meta name="viewport" content="width=device-width">
<script src="encode.js" type="text/javascript"></script>

<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
</head>
<body link="#2cff4b" aLink="#2cff4b" vLink="#2cff4b">

    <script type="text/javascript">
     $(document).ready(function() {

       late();
       setInterval('late()', 2000);
       $("#chatbutton").click(function() {
         $("#chat-wrap").toggle();
         $("#chat-area").animate({ scrollTop: $("#chat-area").height() + 9000000 });
       });

     });

    function late () {
      var divo = document.getElementById("chat-area").innerHTML;
        $.get("chat.txt", function(karma){
            lama = karma.replace(new RegExp("&lt;br/&gt;", "g"), '<br/>');
            lama = lama.replace(new RegExp("&amp;", "g"), '&');
            $("#chat-area").html(lama);
        });

      if($("#chat-area").scrollTop() + $("#chat-area").innerHeight() >= $("#chat-area")[0].scrollHeight)
      {
        $("#chat-area").animate({ scrollTop: $("#chat-area").height() + 9000000 });
      }

    }

    </script>

    <script type="text/javascript">

        // ask user for name with popup prompt
        var name = "<?php echo $_SESSION['username']; ?>";

    	var randum = Math.ceil(Math.random() * 10000)

        // default name is 'Guest'
    	if (!name || name === ' ') {
    	   name = "Guest" + randum;
    	}

    	// strip tags
    	name = name.replace(/(<([^>]+)>)/ig,"");

    	// kick off chat
        var chat =  new Chat();
    	$(document).ready(function() {

    		 chat.getState();
    		 
    		 // watch textarea for key presses
             $("#sendie").keydown(function(event) {  
             
                 var key = event.which;
           
                 //all keys including return.  
                 if (key >= 33) {

                     var maxLength = $(this).attr("maxlength");
                     var length = this.value.length;  

                     // don't allow new content if length is maxed out
                     if (length >= maxLength) {
                         event.preventDefault();
                     }  
                  }  
    		 																																																});
    		 // watch textarea for release of key press
    		 $('#sendie').keyup(function(e) {

    			  if (e.keyCode == 13) { 

                    var text = $(this).val();
                    text = htmlEncode(text);
                    text = text.replace(new RegExp("&#10;", "g"), '\n');
    		    var maxLength = $(this).attr("maxlength");
                    var length = text.length;

                    // send
                    if (length <= maxLength + 1) {

    			        chat.send(text, name);	
    			        $(this).val("");

                    } else {

    					$(this).val(text.substring(0, maxLength));

    				}


    			  }
             });

    	});
    </script>

        <div id="chat-wrap">

          <div id="chat-area">asdfasdf</div>

          <form id="send-message-area">
              <textarea id="sendie" maxlength = '100' ></textarea>
          </form>

        </div>




</body>
</html>