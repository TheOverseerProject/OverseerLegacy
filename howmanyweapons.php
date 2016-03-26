<?php
require_once("header.php");
if (!empty($_POST['abstratus'])) {
  $itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE '%" . $_POST['abstratus'] . "%'");
  $total = 0;
  while ($itemrow = mysql_fetch_array($itemresult)) {
    $total++;
  }
  echo "Weapons available for " . $_POST['abstratus'] . ": $total</br>";
}
echo '<form action="howmanyweapons.php" method="post">Check weapon tally for abstratus:<input id="abstratus" name="abstratus" type="text" /><br />';
echo '<input type="submit" value="Check it!" /></form>';
require_once("footer.php");
?>