<?php
function sender_mail($to, $email, $subject, $message){

	$message = wordwrap($message, 70, "\r\n");
	
	$headers = 'From: ' . MAIL_FROM . "\r\n" .
		'Reply-To: ' . MAIL_FROM . "\r\n" .
		'To: ' . $to . "<". $email . ">\r\n" .
		"Content-Type: text/html; charset=UTF-8\r\n" .
		'X-Mailer: PHP/' . phpversion();

	$send_res = mail($to, $subject, $message, $headers);
	return $send_res;
}
?>