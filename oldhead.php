<?php
session_start();
$_SESSION['ver'] = 'old';
header('location: index.php');
?>