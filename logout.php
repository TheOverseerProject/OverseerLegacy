<?php
$_SESSION['username'] = "";
require_once("header.php");
echo "<script>
$(document).ready(function () {
    window.location = 'index.php';
});
</script>";
require_once("footer.php");
session_destroy();
?>