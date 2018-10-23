<?
#define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
#require($_SERVER["DOCUMENT_ROOT"]."personal/auth/need_auth.php");
require('make_message.php');
require('send_mail.php');
require('get_basket_items.php');

$price_delivery = 700;
$site_url = 'http://' . SITE_SERVER_NAME;
#$editor_email = 'order@monplaisir-mebel.ru';
$editor_email = 'pop-000@yandex.ru';
$from = 'webservice@monplaisir-mebel.ru';

$APPLICATION->SetAdditionalCSS("/personal/order/make/style.css");
$APPLICATION->SetTitle("Оформление заказа");

CModule::IncludeModule('sale');
$FUSER_ID = CSaleBasket::GetBasketUserID();
	
if(empty($FUSER_ID))
{
	$err[] = "При оформлении заказа возникла ошибка [1].";
}
else
{
	$basket = get_basket_items($FUSER_ID);
	$arBasketItems = $basket["BASKET_ITEMS"];
	$sum = $basket["SUM"];
}

if(!$USER->IsAuthorized())
{
	require($_SERVER["DOCUMENT_ROOT"] . "/personal/auth/need_auth.php");
}
else
{
	$user_id = $USER->GetID();
}

if(empty($user_id))
{
	$err[] = "При оформлении заказа возникла ошибка [2].";
}
else
{
	$rsUser = $USER::GetByID($user_id);
	$arUser = $rsUser->Fetch();
}

if(empty($err) && isset($_POST["confirm"]))
{
	/* ========== update user info ============ */

	$user_fields=array(
		"PERSONAL_PHONE"=>$_POST["PERSONAL_PHONE"],
		"NAME"=>$_POST["NAME"], 
		"EMAIL"=>$_POST["EMAIL"]);
		$USER->Update($user_id, $user_fields);

	/* =========== make order ======== */
	
	$arFields= array(
		"LID" => 's1',
		"PERSON_TYPE_ID" => 1,
	   "PAYED" => "N",
	   "CANCELED" => "N",
	   "STATUS_ID" => "N",
	   "PRICE" => $sum,
	   "CURRENCY" => "RUB",
	   "USER_ID" => $user_id,
	   "PAY_SYSTEM_ID" => 1,
	   "PRICE_DELIVERY" => $price_delivery,
	   "DELIVERY_ID" => 2,
	   "DISCOUNT_VALUE" => 0,
	   "TAX_VALUE" => 0.0,
		"USER_DESCRIPTION" => "",
		"COMMENTS" => $_POST["comment"]
	);
			
	$ORDER_ID = CSaleOrder::Add($arFields);
		
	if(empty($ORDER_ID))
	{
		$err[] = "При оформлении заказа возникла ошибка [4].";
	}
	else
	{
		//удаляем , чтобы очистилась малая корзина
			
		CModule::IncludeModule("sale");
		
		$msg = 'Ваш заказ принят. Номер заказа ' . $ORDER_ID;

		$content = CSaleBasket::OrderBasket(
			$ORDER_ID, 
			$FUSER_ID, 
			SITE_ID,
			False
		);
				
		/* -------- mail ----------- */	
		
		$message_to_client = make_message(
			$ORDER_ID, 
			$user_id, 
			$arBasketItems, 
			true, 
			$site_url, 
			$sum
		);
		$message_to_editor = make_message(
			$ORDER_ID, 
			$user_id, 
			$arBasketItems, 
			false, 
			$site_url, 
			$sum
		);
			
		$subject = "Новый заказ";
		
		if(!empty($email))
		{			
			if(empty(send_mail($message_to_client, $subject, $from, $email)))
			{
				$err[] = "При оформлении заказа возникла ошибка [5].";
			}
			else
			{
				/*
				$msg .= '<br>На почту ' . $email . ' отправлено письмо.';
				
				if(isset($_POST))
				{
					foreach($_POST as $key=>$val)
					{
						if(strpos($key, "_del_"))
						{
							CSaleBasket::Delete($val);
						}
					}
					$basket = array();
					$arBasketItems = array();
					$sum = 0;
				}
				*/
				CSaleBasket::DeleteAll( $fUserID );
			}
		}
		
		if(empty(send_mail($message_to_editor, $subject, $from, $editor_email)))
		{
			$err[] = "При оформлении заказа возникла ошибка [6].";
		}
	}
}
	
?>
<h1>Оформление заказа</h1>

<? if(!empty($err)): ?>
<p><? ShowMessage(implode("<br>", $err));?></p>
<? endif; ?>

<? 
if(empty($ORDER_ID))
{
	include("order_details.php");

	?>
	<form method="post">
	
		<p><span style="color:red">*</span> Имя:<br/>
			<input type="text" name="name" <? if(!empty($arUser["NAME"])):?> value="<?=$arUser["NAME"];?>"<? endif; ?> required /></p>
			
		<p><span style="color:red">*</span> Телефон:<br/>
			<input type="text" name="phone" <? if(!empty($arUser["PERSONAL_PHONE"])):?> value="<?=$arUser["PERSONAL_PHONE"];?>"<? endif; ?> required /></p>
		
		<p>E-mail:<br/>
			<input type="text" name="email" <? if(!empty($arUser["EMAIL"])):?> value="<?=$arUser["EMAIL"];?>"<? endif; ?> /></p>
			
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
	if(!empty($msg)): ?>
	<p><? ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>$msg));?></p>	
	<? endif; ?>

	<p><a href="/sale/payment/?ORDER_ID=<?=$ORDER_ID;?>&SUM=<?=$sum;?>">Оплатить</a></p>
	<?
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>