<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetAdditionalCSS("style.css");
$APPLICATION->SetTitle("Оформление заказа");

CModule::IncludeModule('sale');
global $USER;

if(!$USER->IsAuthorized())
{
	if(empty($_POST["email"]))
	{
		<form method="post">
			<caption>E-mail<br/>
			<input type="email" name="email" maxlength="200" value="" placeholder="Ваш e-mail" required>
			</caption>
			<input type="submit" name="submit" value="Отправить">
		</form>
	}
	else
	{
		$arResult = $USER->SimpleRegister($_POST["email"]);
		$USER_ID = $USER->GetID();
		$rsUser = CUser::GetById($USER_ID);
		CUser::SendUserInfo($id, SITE_ID, "Приветствуем Вас как нового пользователя нашего сайта!");
	}
}
else
{
	$rsUsers = CUser::GetList($by="", $order="", array('=EMAIL' => $email));
}

$arUser = $rsUser->Fetch();
$USER_ID = $arUser["ID"];

if(empty($USER_ID))
{
	$err[] = "При оформлении заказа возникла ошибка [1].";
}

$FUSER_ID = CSaleBasket::GetBasketUserID();

if(empty($FUSER_ID))
{
	$err[] = "При оформлении заказа возникла ошибка [2].";
}

if(!empty($err))
{
	<p><? ShowMessage(implode("<br>", $err));?></p>
}

header("Location:make.php?uid=".$USER_ID&fuid=$FUSER_ID);

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>