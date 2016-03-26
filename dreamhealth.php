<?php
require_once("includes/SQLconnect.php");
$players = mysql_query("SELECT * FROM Players");
while ($result = mysql_fetch_array($players)) {
  mysql_query("UPDATE `Players` SET `Dream_Health_Vial` = $result[Gel_Viscosity] WHERE `Players`.`username` = '" . $result['username'] . "' LIMIT 1 ;");
  echo $result['username'];
}
?>