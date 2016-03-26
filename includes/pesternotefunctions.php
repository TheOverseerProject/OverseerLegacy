<?php
function sendPost($username,$password, $message) {
	$url = 'http://pesternote.com/1/statuses/update.xml';
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, "$url");
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$message&link=http://www.theoverseerproject.com");
	curl_setopt($curl_handle, CURLOPT_USERPWD, "$username:$password");
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);
	if (empty($buffer)) {
		return false;
	} else {
		return true;
	}
}
//Usage: sendPost("PesternoteUsername","PesternotePassword","XXXXXXXXX");
?>