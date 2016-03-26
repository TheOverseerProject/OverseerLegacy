    <script type="text/javascript" src="chat.js"></script>
    <script type="text/javascript">
     $(document).ready(function() {

       $("#chatbutton").click(function() {
         $("#chat-wrap").toggle();
         $("#chat-area").animate({ scrollTop: $("#chat-area").height() + 9000000 });
       });

     });
   function late () {
    var divo = document.getElementById("chat-area").innerHTML;
      $.get("chat.txt", function(karma){

          $("#chat-area").html(karma);

      });

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
    	$(function() {
    	
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


    <div id="page-wrap" onload="setInterval('chat.update()', 1000); late();">
        <span id="chatbutton">General Chat</span>
        <div id="chat-wrap">

          <div id="chat-area"></div>

          <form id="send-message-area">
              <span onclick="" >==></span>
              <textarea id="sendie" maxlength = '100' ></textarea>
          </form>
        </div>
    </div>