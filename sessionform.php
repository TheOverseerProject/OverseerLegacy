<?php
require_once("header.php");
echo '<a href="index.php">Home</a>';
echo '<p><span style="font-size: medium;">Create Session</span></p>
<p>Create a new session here (WARNING: DO NOT include an apostrophe in your session name, it messes everything up. You have been warned.):</p>
<form action="createsession.php" method="post"> Session name: <input id="session" name="session" type="text" /><br /> 
Session password: <input id="sessionpw" name="sessionpw" type="password" /><br /> 
Confirm session password: <input id="confirmpw" name="confirmpw" type="password" /><br /> 
<input type="checkbox" name="canon" value="canon">Use canon SBURB devices and alchemy methods (for that authentic feel at the cost of a bit of convenience)<br />
<input type="hidden" name="admin" value="admin"><input type="checkbox" name="challenge" value="challenge">Enable Challenge Mode (for veteran players who know too many codes and recipes for a normal game to be a challenge)<br />
<input type="checkbox" name="randoms" value="randoms">Allow players to randomly join this session</br><input type="checkbox" name="unique" value="unique">Enforce unique classpects (a new player cannot share a class or aspect with another player in the session unless there are over 12 players)<br />
<input type="submit" value="Register" /> </form></br>NOTE - Session administration works by making the first user to enter the session the session admin.';
require_once("footer.php");
?>