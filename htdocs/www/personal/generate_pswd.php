<?php
function generate_password($length)
{
	$numbers = range(1,35);
	$letters = range('a','z');
	$mix = array_merge($numbers, $letters);
	$shuffle = shuffle($mix);
	$pswd = array_slice($shuffle,$length);
	return $pswd;
}
?>