<?php
require_once("header.php");
if (empty($_SESSION['username'])) {	
  echo "Log in to administrate your session.</br>";
  include("loginer.php");
} else {
  
  $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
  $sessionrow = mysql_fetch_array($sessionresult);
  if ($sessionrow['admin'] == $username && $userrow['admin'] == 0) {
    $userrow['admin'] = 1;
    mysql_query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '$username' LIMIT 1;");
    echo "You were set as the session's head admin, but you were not marked as an admin yourself. We have just attempted to fix this, but if you have gotten this message more than once, Blahdev/Babby Overseer would appreciate it if you reported it to him.</br>";
    }
  if ($userrow['admin'] == 0) {
    echo "You're not a session administrator!";
  } else {
    //Begin administration processing here.
    if (!empty($_POST['newsespw'])) {
    	if ($sessionrow['admin'] == $username) {
    		if ($_POST['oldsespw'] == $sessionrow['password']) {
    			if ($_POST['newsespw'] == $_POST['consespw']) {
    				$newpw = mysql_real_escape_string($_POST['newsespw']);
    				mysql_query("UPDATE `Sessions` SET `password` = '$newpw' WHERE `Sessions`.`name` = '$userrow[session_name]'");
    				echo "Password changed successfully!<br />";
    			} else echo "Error changing password: Confirmation did not match the new password given.<br />";
    		} else echo "Error changing password: Password given for 'Current password' field did not match current password.<br />";
    	} else echo "Only the session's head admin can change the session password.<br />";
    }
    
    if (!empty($_POST['exile'])) { //Attempting to exile
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['exile'] . "'");
      $exilerow = mysql_fetch_array($playerresult);
      if ($exilerow['session_name'] != $userrow['session_name'] || $exilerow['username'] == $sessionrow['admin'] || $exilerow['username'] == $userrow['username']) { //Target is either the primary admin or not in the session
	echo "You cannot exile this player!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `former_session_name` = '$exilerow[session_name]' WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `session_name` = 'Exiles' WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `admin` = 0 WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `autoassist` = '' WHERE `Players`.`username` = '$exilerow[username]'"); //exiles could still assist if autoassist was on, hehehe
	if ($sessionrow['exchangeland'] == $exilerow['username']) {
		mysql_query("UPDATE `Sessions` SET `exchangeland` = '' WHERE `Sessions`.`name` = '$userrow[session_name]'");
	}
	$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
	while ($sessionrow = mysql_fetch_array($sessionmates)) {
	  if ($sessionrow['server_player'] == $exilerow['username']) mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	  if ($sessionrow['client_player'] == $exilerow['username']) mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	}
	echo "$exilerow[username] has been exiled from the session.</br>";
      }
    }
    if (!empty($_POST['unexile'])) { //Attempting to un-exile
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['unexile'] . "'");
      $unexilerow = mysql_fetch_array($playerresult);
      if ($unexilerow['former_session_name'] != $userrow['session_name']) { //Target not originally from this session
	echo "You cannot un-exile this player!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `former_session_name` = '' WHERE `Players`.`username` = '$unexilerow[username]'");
	mysql_query("UPDATE `Players` SET `session_name` = '$unexilerow[former_session_name]' WHERE `Players`.`username` = '$unexilerow[username]'");
	mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$unexilerow[username]'");
	mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$unexilerow[username]'");
	$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = 'Exiles'");
	while ($sessionrow = mysql_fetch_array($sessionmates)) {
	  if ($sessionrow['server_player'] == $unexilerow['username']) mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	  if ($sessionrow['client_player'] == $unexilerow['username']) mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	}
	echo "$unexilerow[username] has been un-exiled from the session.</br>";
      }
    }
    if (!empty($_POST['disconnect'])) { //Attempting to disconnect player from all connections.
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['disconnect'] . "'");
      $exilerow = mysql_fetch_array($playerresult);
      if ($exilerow['session_name'] != $userrow['session_name'] || ($exilerow['username'] == $sessionrow['admin'] && $sessionrow['admin'] != $username)) { //Target is either the primary admin or not in the session (the head admin can still target themselves)
	echo "You cannot disconnect this player!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$exilerow[username]'");
	mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$exilerow[username]'");
	$sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
	while ($sessionrow = mysql_fetch_array($sessionmates)) {
	  if ($sessionrow['server_player'] == $exilerow['username']) mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	  if ($sessionrow['client_player'] == $exilerow['username']) mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	}
	echo "$exilerow[username] has had their connections severed.</br>";
      }
    }
    if (!empty($_POST['forceclient']) && !empty($_POST['forceserver'])) { //Attempting to force a server-client connection.
      $allow = true;
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['forceclient'] . "'");
      $clientrow = mysql_fetch_array($playerresult);
      if ($clientrow['session_name'] != $userrow['session_name'] || ($clientrow['username'] == $sessionrow['admin'] && $sessionrow['admin'] != $username)) { //Target is either the primary admin or not in the session (the head admin can still target themselves)
        echo "You cannot manipulate the connections of the player you specified as the client.<br />";
	$allow = false;
      } 
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['forceserver'] . "'");
      $serverrow = mysql_fetch_array($playerresult);
      if ($serverrow['session_name'] != $userrow['session_name'] || ($serverrow['username'] == $sessionrow['admin'] && $sessionrow['admin'] != $username)) { //Target is either the primary admin or not in the session (the head admin can still target themselves)
        echo "You cannot manipulate the connections of the player you specified as the server.<br />";
	$allow = false;
      } 
      if ($allow) { //Targets valid
        $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'"); //first blank the selected players' original connections
	while ($sessionrow = mysql_fetch_array($sessionmates)) {
	  if ($sessionrow['server_player'] == $serverrow['username']) mysql_query("UPDATE `Players` SET `server_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	  if ($sessionrow['client_player'] == $clientrow['username']) mysql_query("UPDATE `Players` SET `client_player` = '' WHERE `Players`.`username` = '$sessionrow[username]'");
	}
	mysql_query("UPDATE `Players` SET `server_player` = '$serverrow[username]' WHERE `Players`.`username` = '$clientrow[username]'");
	mysql_query("UPDATE `Players` SET `client_player` = '$clientrow[username]' WHERE `Players`.`username` = '$serverrow[username]'");
	echo "$serverrow[username] is now $clientrow[username]'s server player.</br>";
      }
    }
    if (!empty($_POST['protonull'])) { //Attempting to nullify pre-entry prototypings.
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['protonull'] . "'");
      $nullrow = mysql_fetch_array($playerresult);
      if ($nullrow['session_name'] != $userrow['session_name'] || $nullrow['username'] == $sessionrow['admin']) { //Target is either the primary admin or not in the session
	echo "You cannot nullify this player's sprite power!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `prototyping_strength` = 0 WHERE `Players`.`username` = '$nullrow[username]'");
	echo "$nullrow[username] has had their prototyping strength reduced to zero.</br>";
      }
    }
    if (!empty($_POST['dungeoneject'])) { //Attempting to eject player from dungeon.
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['dungeoneject'] . "'");
      $nullrow = mysql_fetch_array($playerresult);
      if ($nullrow['session_name'] != $userrow['session_name']) { //Target is either the primary admin or not in the session
	echo "You cannot eject this player from a dungeon!</br>";
      } elseif ($nullrow['cantabscond'] == 1) {
        echo "You cannot eject a player from a dungeon while they strifing the dungeon boss!<br />";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `indungeon` = 0 WHERE `Players`.`username` = '$nullrow[username]'");
	echo "$nullrow[username] has been ejected from their dungeon.</br>";
      }
    }
    if ($sessionrow['admin'] == $username) {
    if (!empty($_POST['admingrant'])) { //Attempting to grant administrative privileges.
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['admingrant'] . "'");
      $adminrow = mysql_fetch_array($playerresult);
      if ($adminrow['session_name'] != $userrow['session_name'] || $adminrow['username'] == $sessionrow['admin']) { //Target is either the primary admin or not in the session
	echo "You cannot grant admin to this player!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `admin` = 1 WHERE `Players`.`username` = '$adminrow[username]'");
	echo "$adminrow[username] has been granted administrative privileges.</br>";
      }
    }
    if (!empty($_POST['adminremove'])) { //Attempting to revoke administrative privileges.
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['adminremove'] . "'");
      $adminrow = mysql_fetch_array($playerresult);
      if ($adminrow['session_name'] != $userrow['session_name'] || $adminrow['username'] == $sessionrow['admin']) { //Target is either the primary admin or not in the session
	echo "You cannot remove admin from this player!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Players` SET `admin` = 0 WHERE `Players`.`username` = '$adminrow[username]'");
	echo "$adminrow[username] has had their administrative privileges revoked.</br>";
      }
    }
    if (!empty($_POST['adminhead'])) { //Attempting to change the head admin
      $playerresult = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . $_POST['adminhead'] . "'");
      $adminrow = mysql_fetch_array($playerresult);
      if ($adminrow['session_name'] != $userrow['session_name'] || $adminrow['username'] == $sessionrow['admin']) { //Target is either the primary admin or not in the session
	echo "You cannot make this player head admin!</br>";
      } else { //Target valid
	mysql_query("UPDATE `Sessions` SET `admin` = '$adminrow[username]' WHERE `Sessions`.`name` = '$sessionrow[name]'");
	echo "$adminrow[username] is now the head admin of your session. (You are now a normal admin.)</br>";
      }
    }
    if (!empty($_POST['settingschange'])) {
    	if (!empty($_POST['randoms'])) {
    		if ($_POST['randoms'] == "yes") $gorandom = "1"; else $gorandom = "0";
    	} else $gorandom = strval($sessionrow['allowrandoms']);
    	if (!empty($_POST['unique'])) {
    		if ($_POST['unique'] == "yes") $gounique = "1"; else $gounique = "0";
    	} else $gounique = strval($sessionrow['uniqueclasspects']);
    	mysql_query("UPDATE Sessions SET `allowrandoms` = $gorandom , `uniqueclasspects` = $gounique WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "' LIMIT 1;");
    	echo "Settings updated.</br>";
    }
    if (!empty($_POST['deleteconfirm'])) {
	$pass = $_POST['deleteconfirm'];
	if ($pass == $sessionrow['password']) { //unadmin all this session's players and exile them
		mysql_query("UPDATE `Players` SET `admin` = 0 WHERE `Players`.`session_name` = '$userrow[session_name]'");
		mysql_query("UPDATE `Players` SET `former_session_name` = '$userrow[session_name]' WHERE `Players`.`session_name` = '$userrow[session_name]'");
		mysql_query("UPDATE `Players` SET `session_name` = 'Exiles' WHERE `Players`.`session_name` = '$userrow[session_name]'");
		mysql_query("DELETE FROM `Sessions` WHERE `Sessions`.`name` = '$userrow[session_name]' LIMIT 1;");
		echo "Done! The session $userrow[session_name] has been completely removed from the database. Have a nice day!</br>";
	} else echo "Error: Password incorrect. Your account still exists, so... yay? Unless you REALLY wanted your account gone, in which case not yay?</br>";
}
}
    //End administration processing here.
    $sessionmates = mysql_query("SELECT `username`,`admin` FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]'");
    $totalmates = 0;
    while ($sessionrow = mysql_fetch_array($sessionmates)) {
      $sessionmate[$totalmates] = $sessionrow['username'];
      $sessionmatea[$totalmates] = $sessionrow['admin'];
      $totalmates++;
    }
    echo '<form action="admin.php" method="post">Exile player from session:<select name="exile">';
    $mates = 0;
    while ($mates < $totalmates) {
      echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
      $mates++;
    }
    echo '</select><input type="submit" value="Exile this player"></form></br>';
    echo '<form action="admin.php" method="post">Un-exile player from session:<select name="unexile">';
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`former_session_name` = '$userrow[session_name]'");
    while ($sessionrow = mysql_fetch_array($sessionmates)) {
      echo '<option value="' . $sessionrow['username'] . '">' . $sessionrow['username'] . '</option>';
    }
    echo '</select><input type="submit" value="Un-exile this player"></form></br>';
    echo '<form action="admin.php" method="post">Break server/client connections:<select name="disconnect">';
    $mates = 0;
    while ($mates < $totalmates) {
      echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
      $mates++;
    }
    echo '</select><input type="submit" value="Disconnect this player"></form></br>';
    echo '<form action="admin.php" method="post">Force connect one player as another\'s server:<br />Server:<select name="forceserver">';
    $mates = 0;
    while ($mates < $totalmates) {
      echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
      $mates++;
    }
    echo '</select><br />Client:<select name="forceclient">';
    $mates = 0;
    while ($mates < $totalmates) {
      echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
      $mates++;
    }
    echo '</select><br /><input type="submit" value="Connect these players"></form></br>';
    echo '<form action="admin.php" method="post">Nullify prototypings:<select name="protonull">';
    $mates = 0;
    while ($mates < $totalmates) {
      echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
      $mates++;
    }
    echo '</select><input type="submit" value="Nullify these prototypings"></form></br>';
    echo '<form action="admin.php" method="post">Eject player from a dungeon:<select name="dungeoneject">';
    $sessionmates = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '$userrow[session_name]' AND `Players`.`indungeon` = 1");
    while ($sessionrow = mysql_fetch_array($sessionmates)) {
      echo '<option value="' . $sessionrow['username'] . '">' . $sessionrow['username'] . '</option>';
    }
    echo '</select><input type="submit" value="Eject this player"></form></br>';
    $sessionresult = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
    $sessionrow = mysql_fetch_array($sessionresult);
    if ($sessionrow['admin'] == $username) { //Special privileges for superadmin
      echo '<form action="admin.php" method="post">Appoint administrator:<select name="admingrant">';
      $mates = 0;
    while ($mates < $totalmates) {
	if ($sessionmatea[$mates] == 0) echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
	$mates++;
      }
      echo '</select><input type="submit" value="Appoint this player"></form></br>';
      echo '<form action="admin.php" method="post">De-appoint administrator:<select name="adminremove">';
      $mates = 0;
    while ($mates < $totalmates) {
	if ($sessionmatea[$mates] == 1) echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
	$mates++;
      }
      echo '</select><input type="submit" value="De-appoint this player"></form></br>';
      echo '<form action="admin.php" method="post">Transfer head admin status (recommended if you wish to self-delete or exile your account):<select name="adminhead">';
      $mates = 0;
    while ($mates < $totalmates) {
	if ($sessionmatea[$mates] == 1) echo '<option value="' . $sessionmate[$mates] . '">' . $sessionmate[$mates] . '</option>';
	$mates++;
      }
      echo '</select><input type="submit" value="Make this player head admin"></form></br>';
      echo '<form action="admin.php" method="post">Change session password:<br />';
      echo 'Current password: <input type="text" name="oldsespw" /><br />New password: <input type="text" name="newsespw" /><br />Confirm new password: <input type="text" name="consespw" /><br /><input type="submit" value="Change it!" /></form><br />';
      echo '<form action="admin.php" method="post">Session-wide settings:</br></br><input type="hidden" name="settingschange" value="settingschange">';
      echo '<input type="radio" name="randoms" value="yes">Allow players to randomly join this session</br><input type="radio" name="randoms" value="no">Block players from randomly joining this session</br></br>';
      echo '<input type="radio" name="unique" value="yes">Players cannot share class/aspect with another</br><input type="radio" name="unique" value="no">Players can have any classpect they want</br>';
      echo '<input type="submit" value="Update settings"></form></br>';
      echo 'Delete your session</br>';
			echo 'If none of your session\'s players plan on participating anymore, we politely ask that you delete the session. All players still in the session (including you) will be exiled. If another session is made of the same name, accounts from this session will be able to move into it if the new head admin unexiles them.</br>';
			echo 'Remember: <b>deleting your session is permanent.</b> Only do this if you are ABSOLUTELY SURE neither you nor any of your other players will be using this session.</br>';
			echo 'If you wish to proceed, type your session password in the box below for confirmation.</br>';
			echo '<form action="admin.php" method="post"><input type="password" name="deleteconfirm"></br><input type="submit" value="Yes, my session is dead and is better off nonexistent!"></form>';
    }
  }
}
require_once("footer.php");
?>