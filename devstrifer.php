<?php
require_once 'header.php';
require_once 'monstermaker.php';

if (empty($_SESSION['username'])) {
  echo "Log in to do stuff.";
} elseif ($userrow['session_name'] != "Developers") {
  echo "What are you doing here?";
} else {

  if (!empty($_POST['newenemy'])) {
    if (empty($_POST['newgrist'])) $_POST['newgrist'] = "None";
    $slot = generateEnemy($userrow,$_POST['newland'],$_POST['newgrist'],$_POST['newenemy'],false);
    if ($slot == -1) {
      echo "There was an error generating the enemy.<br />";
    } else {
      echo "Enemy generated successfully.<br />";
      mysql_query("UPDATE `Players` SET `down` = 0, `Health_Vial` = $userrow[Gel_Viscosity] WHERE `Players`.`username` = '$username'");
    }
    echo "<br />";
  }
  
  echo "Strife Generator<br />";
  echo "<form action='devstrifer.php' method='post'>Land type of enemy's origin: <input type='text' name='newland' /><br />Grist: <input type='text' name='newgrist' /><br />Enemy's basename: <input type='text' name='newenemy' /><br /><input type='submit' value='Generate' /></form>";

}

require_once 'footer.php';
?>