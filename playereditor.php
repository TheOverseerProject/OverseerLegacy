<?php
require_once("header.php");
if (empty($_SESSION['username'])) {
  echo "Log in to view your player overview.</br>";
  echo '</br><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} elseif ($userrow['session_name'] != "Developers") {
  echo "This is a developer tool, I'm sorry to say.";
} else {
  require_once("includes/SQLconnect.php");
  if (!empty($_POST['username'])) {
    echo "Operating on user: $_POST[username]</br>";
    if (!empty($_POST['class']) && !empty($_POST['aspect'])) {
      $newclass = mysql_real_escape_string($_POST['class']);
      $newclass = str_replace("<", "&lt;", $newclass);
      $newaspect = mysql_real_escape_string($_POST['aspect']);
      $newaspect = str_replace("<", "&lt;", $newaspect);
      $titleresult = mysql_query("SELECT * FROM `Titles` WHERE `Titles`.`Class` = 'Adjective'");
      $titlerow = mysql_fetch_array($titleresult);
      $_SESSION['adjective'] = $titlerow[$row[$newaspect]];
      mysql_query("UPDATE `Players` SET `Class` = '$newclass' WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      mysql_query("UPDATE `Players` SET `Aspect` = '$newaspect' WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      echo "Classpect changed.</br>";
    }
    if (!empty($_POST['newrung'])) {
      mysql_query("UPDATE `Players` SET `Echeladder` = $_POST[newrung] WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      if (intval($_POST['newrung']) >= 5) {
	mysql_query("UPDATE `Players` SET `Gel_Viscosity` = " . strval(45 + (intval($_POST['newrung'] - 4) * 15)) . " WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      } elseif (intval($_POST['newrung']) > 1) {
	mysql_query("UPDATE `Players` SET `Gel_Viscosity` = " . strval(5 + (intval($_POST['newrung'] - 1) * 10)) . " WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      } else {
	mysql_query("UPDATE `Players` SET `Gel_Viscosity` = 10 WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
      }
      echo "Echeladder rung changed.</br>";
    }
    mysql_query("UPDATE `Players` SET `down` = 0 WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
    mysql_query("UPDATE `Players` SET `dreamdown` = 0 WHERE `Players`.`username` = '" . $_POST['username'] . "' LIMIT 1 ;");
  }

  //Begin input forms here.
  echo '<form action="playereditor.php" method="post">User to modify: <input id="username" name="username" type="text" /></br>';
  echo '<form action="playereditor.php" method="post">Select class:<select name="class"> '; //Select a class
  echo '<option value=""></option>';
  $classes = mysql_query("SELECT * FROM Titles");
  $reachclass = True;
  while ($row = mysql_fetch_array($classes)) {
    $classresult = mysql_query("SELECT * FROM `Class_modifiers` WHERE `Class_modifiers`.`Class` = '$row[Class]';");
    $classrow = mysql_fetch_array($classresult);
    if ($classrow['activefactor'] > 100) {
      $activepassivestr = "(Active, $classrow[activefactor]%)";
    } else {
      $activepassivestr = "(Passive, $classrow[passivefactor]%)";
    }
    if ($row['Class'] == "General") $reachclass = False;
    if ($reachclass == True) echo '<option value="' . $row['Class'] . '">' . $row['Class'] . ' ' . $activepassivestr . '</option>';
  }
  echo '</select></br>';
  echo 'Select aspect:<select name="aspect"> '; //Select an aspect
  echo '<option value=""></option>';
  $aspects = mysql_query("SELECT * FROM Titles");
  $reachaspect = False;
  while ($col = mysql_fetch_field($aspects)) {
    $aspect = $col->name;
    if ($aspect == "Breath") $reachaspect = True;
    if ($aspect == "General") $reachaspect = False;
    if ($reachaspect == True) echo '<option value="' . $aspect . '">' . $aspect . '</option>';
  }
  echo '</select></br><form action="playereditor.php" method="post">New echeladder rung: <input id="newrung" name="newrung" type="text" /></br>';
  echo '<input type="submit" value="Grant it." /> </form></br>';
}
require_once("footer.php");
?>