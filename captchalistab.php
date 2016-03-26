<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access the captchalogue list.</br>";
} else {

  if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
    $result = mysql_query("SELECT * FROM Captchalogue ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      $realname = str_replace("\\", "", $row['name']);
      echo "$realname</br>";
    }
  } else {
    $result = mysql_query("SELECT * FROM Captchalogue ORDER BY name");
    while ($row = mysql_fetch_array($result)) {
      $realname = str_replace("\\", "", $row['name']);
      echo $realname . "=" . $row['captchalogue_code'] . "</br>";
    }
    $sresult = mysql_query("SELECT * FROM System");
    $srow = mysql_fetch_array($sresult);
    $newaddlog = $srow['debuglog'] . "<br />Dev Captchalist accessed by " . $username;
    $newaddlog = mysql_real_escape_string($newaddlog);
    mysql_query("UPDATE `System` SET `debuglog` = '$newaddlog' WHERE 1");
  }
}
?>