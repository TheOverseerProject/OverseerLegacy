<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to do stuff.</br>";
} else {
	  if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
  	if (!empty($_POST['changecode']) && !empty($_POST['changename'])) {
  		$itemresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['changecode'] . "'");
  		$itemrow = mysql_fetch_array($itemresult);
  		if ($itemrow['captchalogue_code'] == $_POST['changecode']) {
  			$oldname = $itemrow['name'];
  			echo "Old item name: $oldname</br>";
  			$newname = str_replace("\\", "", $_POST['changename']); //go ahead and remove backslashes because they can also cause problems
  			echo "New item name: $newname</br>";
  			$newname = str_replace("'", "''", $newname); //so that the new name is escaped properly
  			$invslot = 1;
  			while ($invslot <= 50) {
  				$invstring = 'inv' . strval($invslot);
  				mysql_query("UPDATE Players SET `$invstring` = '$newname' WHERE `Players`.`$invstring` = '$oldname'");
  				$invslot++;
  			}
  			$newname = str_replace("''", "\\\\''", $newname);
  			mysql_query("UPDATE Captchalogue SET `name` = '$newname' WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['changecode'] . "'");
  			echo "Done. The item name has been changed, and this should be reflected in all players' inventories.</br>";
  		} else echo "That code doesn't match any existing item!</br>";
  	}
  	echo "Item Safety Auto-Name-Changer Version 1.0. Enter the captcha code of the item and what its new name should be.</br>";
  	echo '<form action="itemnamechange.php" method="post">Captcha code: <input type="text" name="changecode"></br>New item name: <input type="text" name="changename"></br>(Note: Backslashes will be automatically inserted where necessary.)</br><input type="submit" value="Change it!"></form>';
	}
}
require_once("footer.php");
?>