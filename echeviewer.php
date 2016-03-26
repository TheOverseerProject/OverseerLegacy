<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to view your Echeladder.</br>";
} else {
  
  $echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
  $echerow = mysql_fetch_array($echeresult);
  //Begin rung naming code here.
if (!empty($_POST['echename'])) {
    $newrung = mysql_real_escape_string($_POST['echename']);
    if (!empty($_POST['echenum'])) {
    	$echenum = intval($_POST['echenum']);
    	if ($echenum >= 1 && $echenum <= 612) {
    		$rungstr = "rung" . strval($_POST['echenum']);
    	} else {
    		echo "ERROR: Invalid echeladder rung number.</br>";
    	}
    }
    else $rungstr = $_POST['echestr'];
    //mysql_query("UPDATE `Players` SET `Echeladder_Rung` = '" . $newrung . "' WHERE `Players`.`username` = '$username' LIMIT 1 ;"); //Used to be for updating Echeladder rung. Now outdated.
    mysql_query("UPDATE `Echeladders` SET `" . $rungstr . "` = '" . $newrung . "' WHERE `Echeladders`.`username` = '$username' LIMIT 1 ;");
    $echerow[$rungstr] = $newrung; //Update array.;
  }
  //End rung naming code here.
  echo "$username's Echeladder</br>";
  echo '<form action="echeviewer.php" method="post">Quick rung [re]namer: Rung <input type="text" name="echenum">: <input id="echename" name="echename" type="text" /><input type="submit" value="Name it!" /></form></br>';
   //Magic number: Number of Echeladder rungs. I'M SO GOOD AT THIS WOW >_>
  echo ' <link rel="stylesheet" type="text/css" href="includes/echeladder.css" media="screen" /> <div class = echewrapper />' ;
  $i = 612;
  while ($i > 0) {
    $echestr = "rung" . strval($i);
    $numstr = strval($i);
    if (!empty($userrow[$echestr])) $userrow[$echestr] = str_replace("'", "&#39;", $userrow[$echestr]);
    if ($i == $userrow['Echeladder'] && $echerow[$echestr] != "") { //Current rung
      echo '<div style="text-align: center">>>>' . $echerow[$echestr] . "<<<" . '</div><div style="text-align: center">--------------------------------------------------</div>';
    } elseif ($echerow[$echestr] == "") {
      echo '<form action="echeviewer.php" method="post">Rung ' . $numstr . ':<input id="echename" name="echename" type="text" /><input type="hidden" value="' . $echestr . '" name="echestr" /><input type="submit" value="Name it!" /> </form>';
    }	else {
      echo '<div style="text-align: center">' . $echerow[$echestr] . '</div><div style="text-align: center">--------------------------------------------------</div>';
    }
    $i--;
  }
  echo "</p></div>";
}
require_once("footer.php");
?>