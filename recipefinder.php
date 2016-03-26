<?php
require_once("header.php");

function getCodename($tempcode) {
	if (empty($tempcodestorage[$tempcode])) {
		$nameresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`captchalogue_code` = '$tempcode' ");
		while ($namerow = mysql_fetch_array($nameresult)) {
			$tempcodestorage[$tempcode] = $namerow['name'];
			return $namerow['name'];
		}
	} else return $tempcodestorage[$tempcode];
}
if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	"I'm afraid it's not that easy, kiddo.";
} else {
if (!empty($_POST['compname'])) {
	$compuname = str_replace("'", "\\\\''", $_POST['compname']); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
  $compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
  $compuresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
  while ($compurow = mysql_fetch_array($compuresult)) $compsearchcode = $compurow['captchalogue_code'];
  $tempcodestorage[$compsearchcode] = $compurow['name']; //this should make it so that it doesn't search for the same item more than once
}
if (!empty($_POST['compcode'])) {
	$compsearchcode = $_POST['compcode'];	
}
if (!empty($compsearchcode)) {
	$reciperesult = mysql_query("SELECT * FROM Recipes WHERE `Recipes`.`ingredient1` = '$compsearchcode' OR `Recipes`.`ingredient2` = '$compsearchcode'");
}
if (!empty($_POST['resname'])) {
	$compuname = str_replace("'", "\\\\''", $_POST['resname']); //Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
  $compuname = str_replace("\\\\\\", "\\\\", $compuname); //really hope this works
  $compuresult = mysql_query("SELECT `captchalogue_code`,`name` FROM Captchalogue WHERE `Captchalogue`.`name` = '" . $compuname . "' LIMIT 1;");
  while ($compurow = mysql_fetch_array($compuresult)) $ressearchcode = $compurow['captchalogue_code'];
  $tempcodestorage[$compsearchcode] = $compurow['name'];
}
if (!empty($_POST['rescode'])) {
	$ressearchcode = $_POST['rescode'];	
}
if (!empty($ressearchcode)) {
	$reciperesult = mysql_query("SELECT * FROM Recipes WHERE `Recipes`.`result` = '$ressearchcode'");
}
if (!empty($reciperesult)) {
	$arrow = " ==&gt; ";
	$totalfound = 0;
	while ($rrow = mysql_fetch_array($reciperesult)) {
		$totalfound++;
		$name1 = getCodename($rrow['ingredient1']);
		$name2 = getCodename($rrow['ingredient2']);
		$namer = getCodename($rrow['result']);
		if ($rrow['operation'] == 1) $op = " &amp;&amp; ";
		else $op = " || ";
		echo $name1 . $op . $name2 . $arrow . $namer;
		echo "</br>";
	}
	if ($totalfound == 0) echo "No results were found.</br>";
	else echo "Total results: $totalfound </br>";
	echo "</br>";
}


echo 'Recipe finder v0.1124 Alpha. Enter search terms below.</br>
The page will return every recipe it can find in the database.</br></br>
<form action="recipefinder.php" method="post">Component search:<br />
Name: <input type="text" name="compname"> / Code: <input type="text" name="compcode"><br />
<input type="submit" value="Search"></form><br />
<br />
<form action="recipefinder.php" method="post">Result search:<br />
Name: <input type="text" name="resname"> / Code: <input type="text" name="rescode"><br />
<input type="submit" value="Search"></form>';
}
require_once("footer.php");
?>