<?php
require_once("header.php");
$gristresult = mysql_query("SELECT * FROM Grist_Types");
echo '<a href="index.php">Home</a>';
echo '<p><span style="font-size: medium;">Enter Session</span></p>
<p>If you are having trouble getting started, check out <a href="http://the-overseer.wikia.com/wiki/Beginner%27s_Guide_to_the_Overseer_Project">this guide!</a></p>
<p>Register new player here:</p>
<form action="addplayer.php" method="post"> Username: <input id="username" name="username" type="text" /><br />
Password: <input id="password" name="password" type="password" /><br />
Confirm password: <input id="confirmpw" name="confirmpw" type="password" /><br />
Email (optional): <input id="email" name="email" type="text" /><br />
Confirm email: <input id="cemail" name="cemail" type="text" /><br />
Note: The Overseer Project uses these emails for the sole purpose of account recovery, should you forget your password. We will never give your email to any third parties, or send you anything without your permission.</br>
You can always change your email through the Player Settings page.</br>
Session name: <input id="session" name="session" type="text" /><br />
Session password: <input id="sessionpw" name="sessionpw" type="password" /><br />
<input type="checkbox" name="randomsession" value="randomsession"> Disregard the above, put me in a random session!</br>(note: only sessions that have opted in will be available)</br>
Prototyping strength: <input id="prototyping_strength" name="prototyping_strength" type="text" /> (For a first time player, I recommend between 0 and 10. 999 represents the power of a First Guardian.)<br />
Be aware that you can prototype post-entry as well, and the resulting power will not be applied to the enemies in your session.<br />
Sprite name: <input id="sprite_name" name="sprite_name" type="text" />sprite<br />
First prototyping item: <input id="protoitem1" name="protoitem1" type="text" /><br />
Second prototyping item: <input id="protoitem2" name="protoitem2" type="text" /><br />
Client player: <input id="client" name="client" type="text" /> (this can be left blank, you will have the opportunity to register a client afterwards)<br />
Land of <input id="land1" name="land1" type="text" / >and <input id="land2" name="land2" type="text" /><br />
Grist category: <select name="grist_type">';
while ($gristrow = mysql_fetch_array($gristresult)) {
  echo '<option value="' . $gristrow['name'] . '">' . $gristrow['name'] . ' - ';
  $i = 1;
  while ($i <= 9) { //Nine types of grist. Magic numbers >_>
    $griststr = "grist" . strval($i);
    echo $gristrow[$griststr];
    if ($i != 9) echo ", ";
    $i++;
  }
  echo '</option>';
}
echo '</select></br>';
echo 'Dreaming status: <select name="dreamer"><option value="Unawakened">Unawakened</option><option value="Prospit">Prospit</option><option value="Derse">Derse</option></select></br>';
echo '<input type="submit" value="Register" /> </form><br />
IMPORTANT: For your prototyping to succeed, you must enter a prototyping strength between -999 and 999, and your first prototyping item field must not be empty!<br />';
require_once("footer.php");
?>