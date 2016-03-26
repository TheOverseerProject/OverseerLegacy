<?php
require 'header.php';
function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristcost = $col->name;
    $gristtype = substr($gristcost, 0, -5);
    if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristcost == "End_Of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
  return $gristname;
}
if (empty($_SESSION['username'])) {
  echo "Log in to do stuff bro<br />";
} elseif ($userrow['session_name'] != "Developers") {
  echo "Dude go away this shit be private yo<br />";
} else {
  $gristname = initGrists();
  $keyresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `Captchalogue`.`abstratus` LIKE '%keykind%'");
  while ($krow = mysql_fetch_array($keyresult)) {
    echo "Key: " . $krow['name'] . "<br />";
    $alreadyfound = false;
    $doorresult = mysql_query("SELECT * FROM `Dungeon_Doors` WHERE `Dungeon_Doors`.`keys` LIKE '%" . mysql_real_escape_string($krow['name']) . "%'");
    while ($drow = mysql_fetch_array($doorresult)) {
      $alreadyfound = true;
    }
    if (strpos($krow['abstratus'], "bladekind") !== false || strpos($krow['abstratus'], "birdkind") !== false || strpos($krow['description'], "blade") !== false || strpos($krow['description'], "sword") !== false) {
      echo "nope screw it<br />";
      $alreadyfound = true;
    }
    if (!$alreadyfound) {
      $newpower = $krow['power'] * 2;
      $newdesc = str_replace("key", "door", $krow['description']);
      $newdesc = str_replace("Key", "Door", $newdesc);
      $newdesc = str_replace("\\", "", $newdesc); //no backslashes before the escaping
      $newdesc = mysql_real_escape_string($newdesc);
      $total = 0;
      $grist = 0;
      while (!empty($gristname[$grist])) {
        $gristcost = $gristname[$grist] . "_Cost";
        $total += $krow[$gristcost];
        $grist++;
      }
      if ($total > 1000000) $newgate = 6;
      elseif ($total > 100000) $newgate = 5;
      elseif ($total > 1000) $newgate = 3;
      else $newgate = 1;
      $newkeys = mysql_real_escape_string($krow['name']);
      $query = "INSERT INTO `Dungeon_Doors` (`gate`,`keys`,`description`,`strength`) VALUES ($newgate, '$newkeys', '$newdesc', $newpower);";
      echo $query . "<br />";
      //mysql_query($query);
    }
  }
  echo "Done!<br />";
}

require 'footer.php';
?>