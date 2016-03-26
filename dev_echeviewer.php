<?php
require_once("header.php");
?>

<link rel="stylesheet" type="text/css" href="CSS_includes/echeladder.css" media="screen" /> 
<input id = "button" onclick = 'echeladder.scroll(0, 550)' type = button />
<div class = echewrapper id = echeladder onload = 'echeladder.scroll(0, 150)' >
<?php
  $echeresult = mysql_query("SELECT * FROM Echeladders WHERE `Echeladders`.`username` = '" . $username . "'");
  $echerow = mysql_fetch_array($echeresult);
  $i = 612;
  while ($i > 0) {
	echo "<div class = 'echerung' >";
    $echestr = "rung" . strval($i);
    $numstr = strval($i);
    if (!empty($userrow[$echestr])) $userrow[$echestr] = str_replace("'", "&#39;", $userrow[$echestr]);
    if ($i == $userrow['Echeladder'] && $echerow[$echestr] != "") { //Current rung
      echo $echerow[$echestr];
    } elseif ($echerow[$echestr] == "") {
      echo '<form action="echeviewer.php" method="post">Rung ' . $numstr . ':<input id="echename" name="echename" type="text" /><input type="hidden" value="' . $echestr . '" name="echestr" /><input type="submit" value="Name it!" /> </form>';
    } else {
      echo $echerow[$echestr];
    }
	echo "</div>";
    $i--;
  }
?>

</div>