<?php
require_once("header.php");

echo 'The following is a list of every "main" abstratus and the number of weapons present in each.</br>';
  $itemresult = mysql_query("SELECT * FROM Captchalogue ORDER BY abstratus");
  $currentabstratus = "";
  $k = 0;
  while ($itemrow = mysql_fetch_array($itemresult)) {
    $mainabstratus = "";
    $alreadydone = False;
    $foundcomma = False;
    $j = 0;
    if (strrchr($itemrow['abstratus'], ',') == False) {
      $mainabstratus = $itemrow['abstratus'];
    } else {
      while ($foundcomma != True) {
	$char = "";
	$char = substr($itemrow['abstratus'],$j,1);
	if ($char == ",") { //Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
	  $mainabstratus = substr($itemrow['abstratus'],0,$j);
	  $foundcomma = True;
	} else {
	  $j++;
	}
      }
    }
    if ($currentabstratus == $mainabstratus) {
      $alreadydone = True;
    } else {
      $currentabstratus = $mainabstratus;
    }
    if ($alreadydone == False && $mainabstratus != "notaweapon" && $mainabstratus != "headgear" && $mainabstratus != "bodygear" && $mainabstratus != "facegear" && $mainabstratus != "accessory" && $mainabstratus != "computer") { //I HAVE NEW WEAPON!
      $absresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE '" . $mainabstratus . "%' OR `abstratus` LIKE '%, " . $mainabstratus . "%'");
      //ensures that we don't catch dartkind with artkind, inflatablekind with tablekind, etc
      $total = 0;
      while ($itemrow = mysql_fetch_array($absresult)) {
        $total++;
      }
      $ordered[$mainabstratus] = $total;
      $abs[$k] = $mainabstratus;
      $k++;
      if ($_GET['sort'] != "yes") echo $mainabstratus . ": $total</br>";
    }
  }
  if ($_GET['sort'] == "yes") {
  $allabs = $k;
  $i = 1;
  $maxx = max($ordered);
  while ($i <= $maxx) {
    $k = 0;
    while ($k < $allabs) {
      if ($ordered[$abs[$k]] == $i) {
        echo $abs[$k] . ": " . strval($ordered[$abs[$k]]) . "<br />";
        $countresult = mysql_query("SELECT `ID` FROM `Feedback` WHERE `Feedback`.`type` = 'item' AND `Feedback`.`comments` LIKE '%" . $abs[$k] . "%' AND `Feedback`.`suspended` = 0");
        while ($row = mysql_fetch_array($countresult)) {
          echo ' - Submission <a href="submissions.php?view=' . strval($row['ID']) . '">' . strval($row['ID']) . '</a><br />';
        }
      }
      $k++;
    }
    $i++;
  }
  }
  require_once("footer.php");
?>