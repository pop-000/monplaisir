<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформление заказа");
$APPLICATION->SetAdditionalCSS("style.css");

CModule::IncludeModule('sale');
global $USER;

if(!$USER->IsAuthorized())
{
	if(empty($_POST["email"]))
	{
		?>
		<form method="post">
			<p>Требуетcя авторизация</p>
			<input type="email" name="email" maxlength="200" value="" placeholder="Ваш e-mail" required>
			<input type="submit" name="submit" value="Отправить">
		</form>
		<?
	}
	else
	{
		$rsUsers = CUser::GetList($by="", $order="", array('=EMAIL' => $_POST["email"]));
		if($rsUsers->SelectedRowsCount > 0)
		{
			$arUser = $rsUser->Fetch();
			$USER_ID = $arUser["ID"];
			CUser::Authorize($USER_ID);
		}
		else
		{
			$arResult = $USER->SimpleRegister($_POST["email"]);
		}
	}
}

if($USER->IsAuthorized())
{	
	$USER_ID = $USER->GetID();
	
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
		ShowMessage(implode("<br>", $err));
	}
	if(isset($USER_ID, $FUSER_ID))
	{

		header("Location:confirm.php?uid=".$USER_ID."&fuid=".$FUSER_ID);
		exit();
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>