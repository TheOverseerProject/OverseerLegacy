<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to populate your atheneum.</br>";
} else {
  require_once("includes/SQLconnect.php");
  $invslots = 50;
  echo "Atheneum populator</br>This should only need to be done once across your entire session.</br>Any item you or any of your session-mates have acquired or previewed since the update will be in your Atheneum already.</br>";
  $athresult = mysql_query("SELECT `atheneum` FROM Sessions WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
  $athrow = mysql_fetch_array($athresult);
  $athstring = $athrow['atheneum'];
  //$athstring = ""; //blanks the atheneum first for testing
  $teamresult = mysql_query("SELECT * FROM Players WHERE `Players`.`session_name` = '" . $userrow['session_name'] . "'");
  while ($prow = mysql_fetch_array($teamresult)) {
 		$invcount = 1;
 		echo "Adding items from " . $prow['username'] . "'s inventory:</br>";
 		while ($invcount <= $invslots) {
 			$invstr = 'inv' . strval($invcount);
 			if ($prow[$invstr] != "") {
 				//echo $prow[$invstr] . " found</br>";
 				$item = str_replace("'", "\\\\''", $prow[$invstr]);
 				$item = str_replace(" (ghost image)", "", $item);
 				$itemresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`name` = '$item' LIMIT 1;");
 				$itemrow = mysql_fetch_array($itemresult);
 				if (!strrpos($athstring, $itemrow['captchalogue_code'])) {
 					$athstring = $athstring . $itemrow['captchalogue_code'] . "|";
 					echo $prow[$invstr] . " added</br>";
 				}
 			}
 			$invcount++;
 		}
  }
  echo "That's everyone!</br>";
  mysql_query("UPDATE `Sessions` SET `atheneum` = '$athstring' WHERE `Sessions`.`name` = '" . $userrow['session_name'] . "'");
}
require_once("footer.php");
?>