 <?php
 require_once("header.php");
 
if (empty($_SESSION['username'])) {
  echo "Log in to upload art.</br>";
  echo '</br><a href="/">Home</a> <a href="controlpanel.php">Control Panel</a></br>';
} else {
  require_once("includes/SQLconnect.php");
  if ($userrow['session_name'] != "Developers") {
    echo "And just what do you think YOU'RE doing?";
  } else {
    if (!empty($_POST['artcode'])) {
      if ($_FILES["file"]["error"] > 0) {
	echo "ERROR! Return Code: " . $_FILES["file"]["error"] . "<br>";
      } else {
	echo "Upload: " . $_FILES["file"]["name"] . "<br>";
	echo "Type: " . $_FILES["file"]["type"] . "<br>";
	echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
	echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";
	if (file_exists("/www/overseer/Images/Items/" . $_FILES["file"]["name"])) {
	  echo $_FILES["file"]["name"] . " already exists. ";
	} else {
	  move_uploaded_file($_FILES["file"]["tmp_name"], "/www/overseer/Images/Items/" . $_FILES["file"]["name"]);
	  echo "Art file stored in: " . "/www/overseer/Images/Items/" . $_FILES["file"]["name"];
	  mysql_query("UPDATE `Captchalogue` SET `art` = '" . $_FILES['file']['name'] . "' WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['artcode'] . "' LIMIT 1 ;");
	  mysql_query("UPDATE `Captchalogue` SET `credit` = '" . $_POST['credit'] . "' WHERE `Captchalogue`.`captchalogue_code` = '" . $_POST['artcode'] . "' LIMIT 1 ;");
	}
      }
    }
    echo '<html>
         <body>

         <form action="uploadart.php" method="post" enctype="multipart/form-data">
         <label for="file">Filename:</label>
         <input type="file" name="file" id="file"></br>
         Captchalogue code for item whose art this is (because fuck apostrophes)<input type="text" name="artcode" id="artcode"></br>
         Player to receive credit:<input type="text" name="credit" id="credit"></br>
         <input type="submit" name="submit" value="Submit">
         </form>

         </body>
         </html> ';
  }
}
require_once("footer.php");
?> 