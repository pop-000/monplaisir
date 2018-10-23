<?php
function send_mail($message, $subject, $to, $from){

	$message = wordwrap($message, 70, "\r\n");
	
	$headers = 'From: ' . $from . "\r\n" .
		'Reply-To: ' . $from . "\r\n" .
		"Content-Type: text/html; charset=UTF-8\r\n" .
		'X-Mailer: PHP/' . phpversion();

	$send_res = mail($to, $subject, $message, $headers);
	return $send_res;
}
?>