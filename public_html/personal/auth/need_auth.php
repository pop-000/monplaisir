<?php
if(isset($_POST["email"]))
{
	require('authorize_or_register.php');
	$user_id = authorize_or_register($_POST["email"]);
}
else
{
	?>
<h2>Необходима  авторизация</h2>
<form method="post">
	Ваш e-mail:
	<input type="email" name="email" required>
	<input name="send" type="submit">
</form>
	<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
exit();
}
?>