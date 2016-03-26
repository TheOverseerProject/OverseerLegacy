
<?php
require_once("header.php");

$_POST['session'] = str_replace(">", "", $_POST['session']); //this is why we can't have nice things
$_POST['session'] = str_replace("<", "", $_POST['session']);
$_POST['session'] = str_replace("'", "", $_POST['session']); //kill apostrophes while we're at it
$result = mysql_query("SELECT * FROM Sessions WHERE `Sessions`.`name` = '" . $_POST['session'] . "'");
$clash = False;

if ($_POST['session'] != "" && $_POST['sessionpw'] == $_POST['confirmpw']) {
  while ($row = mysql_fetch_array($result)) {
    if ($_POST['session'] == $row['name']) { //Name clash: Session name is already taken.
      echo "Session creation failed: Session name is in use.";
      $clash = True;
    }
  }
  if ($clash == False) {
    $name = mysql_real_escape_string($_POST['session']);
    $pw = mysql_real_escape_string($_POST['sessionpw']);
    if (!empty($_POST['randoms'])) {
    	$randoms = "1";
    } else {
    	$randoms = "0";
    }
    if (!empty($_POST['unique'])) {
    	$unique = "1";
    } else {
    	$unique = "0";
    }
    if (!empty($_POST['challenge'])) {
    	$chall = "1";
    } else {
    	$chall = "0";
    }
    if (!empty($_POST['canon'])) {
    	$canon = "1";
    } else {
    	$canon = "0";
    }
    if (!empty($_POST['admin'])) {
      mysql_query("INSERT INTO `Sessions` (`name` ,`password` ,`admin` ,`allowrandoms` ,`uniqueclasspects` ,`challenge`, `canon`)VALUES ('$name', '$pw', 'default', $randoms, $unique, $chall, $canon);"); //default is the flag for "first player to enter receives admin powers"
    } else {
      mysql_query("INSERT INTO `Sessions` (`name` ,`password` ,`allowrandoms` ,`uniqueclasspects` ,`challenge`, `canon`)VALUES ('$name', '$pw', $randoms, $unique, $chall, $canon);");
    }
    echo "Session $name creation successful.";
  }
} else {
  echo "Session creation failed: Session name empty or passwords do not match.";
}
mysql_close($con);
echo '</br><a href="/">Home</a>';
require_once("footer.php");
?> 