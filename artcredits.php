<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to access the list of art credits.</br>";
} else {
 
    $result = mysql_query("SELECT * FROM Captchalogue where `art` <> '' ORDER BY name ;") or die(mysql_error());
    while ($row = mysql_fetch_array($result)) {
      
      echo '<a href="Images/Items/' . $row['art'] . '">' . stripslashes($row['name']) . '</a> - ' . stripslashes($row['credit']) . '</br>';
      }
    }
  require_once("footer.php");
?>