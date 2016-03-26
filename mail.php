<?php
$to = "mostis@msn.com";
$subject = "Test mail";
$message = "Hello! This is a simple email message.";
$from = "info@overseerdev.ctri.co.uk";
$headers = "From:" . $from;
mail($to,$subject,$message,$headers);
echo "Mail Sent.";
?>