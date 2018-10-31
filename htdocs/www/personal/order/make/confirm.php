<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"]."/personal/auth/auth_or_reg.php");
require($_SERVER["DOCUMENT_ROOT"]."/personal/mail/sender_mail.php");
require($_SERVER["DOCUMENT_ROOT"]."/personal/order/make/order_details.php");

$APPLICATION->SetAdditionalCSS("/personal/order/make/style.css");
$APPLICATION->SetTitle("Подтверждение заказа");

$FUSER_ID = $_REQUEST["fuid"];
$USER_ID = $_REQUEST["uid"];

if(!isset($FUSER_ID, $USER_ID))
{
	?>
	<p><? ShowMessage('Ошибка при оформлении заказа [1]');?></p>
	<?
	exit();
}

$dbBasketItems = CSaleBasket::GetList(
		array(),
		array(
			"FUSER_ID" => $FUSER_ID,
			"LID" => SITE_ID,
			"ORDER_ID" => "NULL",
		),
		false,
		false,
		array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "NAME")
		);
while($arItems = $dbBasketItems->Fetch())
{
	$arItems = CSaleBasket::GetByID($arItems["ID"]);
	$dbres = CIBlockElement::GetByID($arItems["PRODUCT_ID"]);
	if($obres = $dbres->GetNextElement())
	{	
		$arFields = $obres->GetFields();
		$arImg = CFile::ResizeImageGet( 
				 $arFields["PREVIEW_PICTURE"], 
				array('width'=>75, 'height'=>75), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
		$arItems["IMG"] = $arImg["src"];
	}
	$db_res = CSaleBasket::GetPropsList(
				array("SORT" => "ASC","NAME" => "ASC"),
				array("BASKET_ID" => $arItems["ID"]));
	while ($prop = $db_res->Fetch())
	{
		$rsProp = CIBlockElement::GetByID( $prop["VALUE"] );
		if($arProp = $rsProp->GetNext())
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$arProp["PREVIEW_PICTURE"], 
				array('width'=>50, 'height'=>50), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
			
			$arItems["PROPS"][ $prop["CODE"] ] = array(
				"ID" => $arProp["ID"],
				"NAME" => $arProp["NAME"],
				"PICTURE" => $arImg["src"],
			);
		}
	}
	$arBasketItems[] = $arItems;
}	

$rsUser = CUser::GetByID($USER_ID);
$arUser = $rsUser->Fetch();

/*-------------------------------------------------*/

if(isset($_POST["confirm"]))
{
	/* ========== update user info ============ */

	$user_fields=array(
		"PERSONAL_PHONE"=>$_POST["phone"],
		"NAME"=>$_POST["name"], 
		"EMAIL"=>$_POST["email"]);
		
		$USER->Update($USER_ID, $user_fields);

	/* =========== make order ======== */
	
	$sum = $_POST["sum"];
	
	$arFields= array(
		"LID" => 's1',
		"PERSON_TYPE_ID" => 1,
	   "PAYED" => "N",
	   "CANCELED" => "N",
	   "STATUS_ID" => "N",
	   "PRICE" => $sum,
	   "CURRENCY" => "RUB",
	   "USER_ID" => $USER_ID,
	   "PAY_SYSTEM_ID" => 1,
	   "PRICE_DELIVERY" => PRICE_DELIVERY,
	   "DELIVERY_ID" => 2,
	   "DISCOUNT_VALUE" => 0,
	   "TAX_VALUE" => 0.0,
		"USER_DESCRIPTION" => "",
		"COMMENTS" => $_POST["comment"]
	);
			
	$ORDER_ID = CSaleOrder::Add($arFields);
		
	if(empty($ORDER_ID))
	{
		$err[] = "При оформлении заказа возникла ошибка [3].";
	}
	else
	{
		$msg = 'Ваш заказ принят. Номер заказа ' . $ORDER_ID;
/*
		CSaleBasket::OrderBasket(
			$ORDER_ID, 
			$FUSER_ID, 
			$SITE_ID,
			False
			);
*/
		CSaleBasket::OrderBasket($ORDER_ID);
		
		//чистим корзину
				
		#CSaleBasket::DeleteAll( $fUserID );	
				
		/* -------- mail ----------- */	

		$message = array(
			$ORDER_ID, 
			$USER_ID, 
			$arBasketItems, 
			$sum, 
			$arFields["COMMENTS"]
			);
	
		$subject = "Новый заказ";
		/*------------ to editor ---------- */		
		
		$fio = implode(" ", array($arUser["NAME"], $arUser["SECOND_NAME"], $arUser["LAST_NAME"]));
		$message_editor =
		'<html>
			<head></head>
				<body>
					<h2>Новый заказ</h2>
						<table cellpadding="8" cellspacing="0" border="1">
							<tr><td>ID пользователя</td><td>'.$USER_ID.'</td></tr>
							<tr><td>ID заказа</td><td>'.$ORDER_ID.'</td></tr>
							<tr><td>Как обращаться</td><td>'.$fio.'</td></tr>
							<tr><td>Телефон</td><td>'.$arUser["PERSONAL_PHONE"].'</td></tr>
							<tr><td>E-mail</td><td>'.$arUser["EMAIL"].'</td></tr>
							<tr><td>Комментарий</td><td>.'.$_POST["comment"].'</td></tr>
						</table>
						<hr/>';	
		$order_details = order_details($arBasketItems);
		$message_editor .= $order_details;
		$message_editor .= '<hr/>'.
					date('H:i:s d.m.Y', time()) . '
				</body>		
		<html>';
		
		sender_mail('Редактору', EDITOR_EMAIL, $subject, $message_editor);
		
		/*------------ to client ---------- */
		
		$message_client =
		'<html>
			<head></head>
				<body>
					<h2>Новый заказ</h2>
					<p>'. $fio .', Вы сделали заказ на сайте ' . SITE_URL . '.</p>';
					$order_details = order_details($arBasketItems);
		$message_client .=	$order_details;		
		$message_client .= '</hr>'.		
					include MAIL_CONTACTS.'	
					<p>Письмо сформировано автоматически. Пожалуйста не отвечайте на него!</p>
				</body>
			</html>';

		// отправка e-mail
			
		if(!sender_mail($fio, $_POST["email"], $subject, $message_client))
		{
			$err[] = "При оформлении заказа возникла ошибка [4].";
		}
	}
}
?>

<? if(!empty($err)): ?>
<p><? ShowMessage(implode("<br>", $err));?></p>
<? endif; ?>

<? 
if(empty($ORDER_ID))
{
	?>
	<h1>Подтверждение заказа</h1>
	<?
	include "order_details.php";
?>
	
	<form method="post">
		<input type="hidden" name="USER_ID" value="<?=$USER_ID;?>">
		<input type="hidden" name="FUSER_ID" value="<?=$FUSER_ID;?>">
		<input type="hidden" name="sum" value="<?=$sum_total;?>">
		
		<p><span style="color:red">*</span> Имя:<br/>
			<input type="text" name="name" maxlength=50 <? if(!empty($arUser["NAME"])):?> value="<?=$arUser["NAME"];?>"<? endif; ?> required /></p>
			
		<p><span style="color:red">*</span> Телефон:<br/>
			<input type="text" name="phone" maxlength=50 <? if(!empty($arUser["PERSONAL_PHONE"])):?> value="<?=$arUser["PERSONAL_PHONE"];?>"<? endif; ?> required /></p>
		
		<p><span style="color:red">*</span> E-mail:<br/>
			<input type="email" name="email" <? if(!empty($arUser["EMAIL"])):?> value="<?=$arUser["EMAIL"];?>"<? endif; ?> required /></p>
			
		<p>Комментарий к заказу:<br/>
		<textarea name="comment" rows="5" cols="50"></textarea></p>
			
		<hr/>
		
		<p><input type="submit" name="confirm" value="Подтвердить заказ" class="btn" />&emsp;
			<a href="/personal/cart/" class="btn">Отмена</a>
		</p>
		<p>Нажимая кнопку &laquo;Подтвердить заказ&raquo;, вы подтверждаете, что ознакомились с <a href="/personal/conditions.php" target="_blank">Политикой обработки персональных данных</a> и принимаете её. 
		
	</form>			
	<?
}
else
{
	if(!empty($msg)):?>
	<p style="font-size:2em"><?echo $msg;?></p>
	<? endif; ?>
	
	<p><a href="/sale/payment/?ORDER_ID=<?=$ORDER_ID;?>&SUM=<?=$sum;?>">Оплатить</a></p>
	<?
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
?>