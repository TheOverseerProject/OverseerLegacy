<?php
//This is a filler line.
if ($_POST['mako'] == "kawaii") {
require_once("includes/SQLconnect.php");
$result = mysql_query("SELECT * FROM Players WHERE `Players`.`username` = '" . mysql_real_escape_string($_POST['username']) . "'");
$loggedin = False;

while ($userrow = mysql_fetch_array($result)) {
  if ($userrow['username'] == mysql_real_escape_string($_POST['username']) && $userrow['password'] == crypt(mysql_real_escape_string($_POST['password']), $userrow['password'])) {
    session_start(); //Begin initializing the session here. This involves initializing anything we don't want to call from the database all the time.
    $username = mysql_real_escape_string($_POST['username']);
    $_SESSION['username'] = $username;
    $titleresult = mysql_query("SELECT * FROM `Titles` WHERE `Titles`.`Class` = 'Adjective'");
    $titlerow = mysql_fetch_array($titleresult);
	//Grab aspect and class modifiers.
    if (!empty($titlerow[$userrow['Aspect']])) {
		$_SESSION['adjective'] = $titlerow[$userrow['Aspect']];
		$classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$userrow[Class]';");
		$_SESSION['classrow'] = mysql_fetch_array($classresult);
		$aspectresult = mysql_query("SELECT * FROM `Aspect_modifiers` WHERE `Aspect_modifiers`.`Aspect` = '$userrow[Aspect]';");
		$_SESSION['aspectrow'] = mysql_fetch_array($aspectresult);
	}
	//Grab grist types.
	$gristresult = mysql_query("SELECT * FROM `Grist_Types`");
	while ($row = mysql_fetch_array($gristresult)) {
		$_SESSION[$row['name']] = $row;
	}
    mysql_query("UPDATE `Players` SET `active` = 1 WHERE `Players`.`username` = '$username' LIMIT 1 ;");
    if ($userrow['equipped'] != "") {
      $equipname = str_replace("'", "\\\\''", $userrow[$userrow['equipped']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $equipname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['equipped']]) {
			$_SESSION['mainrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
		}
      }
    }
    if ($userrow['offhand'] != "" && $userrow['offhand'] != "2HAND") {
      $offname = str_replace("'", "\\\\''", $userrow[$userrow['offhand']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $offname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['offhand']]) {
		$_SESSION['offrow'] = $row;
		}
      }
    }
    if ($userrow['headgear'] != "") {
      $headname = str_replace("'", "\\\\''", $userrow[$userrow['headgear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $headname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['headgear']]) {
			$_SESSION['headrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
		}
      }
    }
    if ($userrow['facegear'] != "" && $userrow['facegear'] != "2HAND" && $userrow['dreamingstatus'] == "Awake") {
      $facename = str_replace("'", "\\\\''", $userrow[$userrow['facegear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $facename . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['facegear']]) {		
			$_SESSION['facerow'] = $row; //We save this to check weapon-specific bonuses to various commands.
		}
      }
    }
    if ($userrow['bodygear'] != "") {
      $bodyname = str_replace("'", "\\\\''", $userrow[$userrow['bodygear']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $bodyname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['bodygear']]) {
		$_SESSION['bodyrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
		}
      }
    }
    if ($userrow['accessory'] != "" && $userrow['dreamingstatus'] == "Awake") {
      $accname = str_replace("'", "\\\\''", $userrow[$userrow['accessory']]); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
      $itemresult = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $accname . "'");
      while ($row = mysql_fetch_array($itemresult)) {
		$itemname = $row['name'];
		$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
		if ($itemname == $userrow[$userrow['accessory']]) {
			$_SESSION['accrow'] = $row; //We save this to check weapon-specific bonuses to various commands.
		}
      }
    }
    $loggedin = True;
    echo "true";
  }
}
if ($loggedin == False) {
  if (mysql_real_escape_string($_POST['username']) == "The Overseer") {
    echo "DEBUG (password): $_POST[password]</br>";
    echo "DEBUG (encrypted password): " . crypt(mysql_real_escape_string($_POST['password']), $row['password']) . "</br>";
  }
  echo"false";
}
mysql_close($con);
}
else {
echo '<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<script>
$(document).ready(function () {
    window.location = "loginer.php";
});
</script>';
}
?>