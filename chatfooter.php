</div>
<span style="font-size: 10px;">Copyright &copy; 2013 <a style="color: #555555;" href="http://sho.insomnia247.nl/">ARG Loop</a> & <a style="color: #555555;" href="http://theoverseerproject.com/">The Overseer</a>. All rights reserved.</span>
<br />
<span style="font-size: 10px;"><a style="color: #555555;" href="http://www.mspaintadventures.com/?s=6&p=001901">Homestuck</a> is copyright &copy; Andrew Hussie.</span>
<?php
if ($userrow['dreamingstatus'] == "Prospit") {
  echo '<br />
  <span style="font-size: 10px;"><a style="color: #555555;" href="http://juls-art.tumblr.com/post/27674167117/15-derse-or-prospit-prospit-i-wasnt-sure-if-it">Prospit background image</a> by 
  <a style="color: #555555;" href="http://splitsoulsister.tumblr.com/">splitsoulsister</a></span>';
}
?>
</div>

<script type="text/javascript">
 $(document).ready(function() {

   $("#chatbutton").click(function() {
     $("#chat-wrap").toggle();
   });

 });
 </script>
<div id="page-wrap">
<span id="chatbutton">General Chat</span>
  <div id="chat-wrap">
    <iframe id="chatapp" src="http://partyonthedevserver.scorpiaproductions.co.uk/chat/overseer?user=<?php echo $_SESSION['username']; ?>" />
  </div>
</div>


</body>
</html>