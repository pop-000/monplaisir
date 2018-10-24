<?php 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"]."/personal/generate_pswd.php");
$APPLICATION->SetTitle("Регистрация");

global $USER;

if(isset($_POST["submit"]))
{
	
	
	CUser::Register(
		 time(),
		 $_POST["name"],
		 "",
		 $pswd,
		 $pswd,
		 $_POST["email"],
		 false,
		 "",
		 0
		);
	$id = $USER->GetId();
	
	CUser::Update("PERSONAL_PHONE" => $_POST["phone"]);
}

if (isset($_REQUEST["backurl"]) && strlen($_REQUEST["backurl"])>0) 
	LocalRedirect($backurl);
?>

<form method="post">
	
	<caption>Имя<br/>
	<input type="text" name="name" maxlength="75" value="Ваше имя" required>
	</caption>

	<caption>E-mail<br/>
	<input type="email" name="email" maxlength="200" value="Ваш e-mail" required>
	</caption>
	
	<caption>Телефон<br/>
	<input type="phone" name="phone" maxlength="200" value="Ваш телефон" required>
	</caption>
	
	<input type="submit" name="submit" value="Отправить">
	
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>