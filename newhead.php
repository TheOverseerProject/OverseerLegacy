<?php
session_start();
$_SESSION['ver'] = '';
header('location: index.php');
?>