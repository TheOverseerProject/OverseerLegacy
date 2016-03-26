<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access the captchalogue list.</br>";
} else {
 
  echo '<a href="howmanyweapons.php">Check the number of weapons available for an abstratus</a></br>';
  echo '<a href="artcredits.php">View a list of items with existing art only (with credits)</a></br>';
  if ($userrow['modlevel'] < 4) {
    $result = mysql_query("SELECT * FROM Captchalogue ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      $realname = str_replace("\\", "", $row['name']);
      echo "$realname</br>";
    }
  } else {
    $result = mysql_query("SELECT * FROM Captchalogue ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      $realname = str_replace("\\", "", $row['name']);
      echo "$realname - $row[captchalogue_code] - $row[abstratus]</br>";
    }
    $sresult = mysql_query("SELECT * FROM System");
    $srow = mysql_fetch_array($sresult);
    $newaddlog = $srow['debuglog'] . "<br />Dev Captchalist accessed by " . $username;
    $newaddlog = mysql_real_escape_string($newaddlog);
    mysql_query("UPDATE `System` SET `debuglog` = '$newaddlog' WHERE 1");
  }
}
require_once("footer.php");
?>