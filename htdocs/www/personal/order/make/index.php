<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"]."personal/auth/need_auth.php");

require('../../mail/send_mail.php');
require('get_basket_items.php');
require('order_details.php');

$APPLICATION->SetAdditionalCSS("/personal/order/make/style.css");
$APPLICATION->SetTitle("Оформление заказа");

CModule::IncludeModule('sale');
$FUSER_ID = CSaleBasket::GetBasketUserID();

if(!$USER->IsAuthorized())
{
	require($_SERVER["DOCUMENT_ROOT"] . "/personal/auth/need_auth.php");
}
else
{
	$user_id = $USER->GetID();
}
	
if(empty($FUSER_ID))
{
	$err[] = "При оформлении заказа возникла ошибка [1].";
}
else
{
	$basket = get_basket_items($FUSER_ID);
	$arBasketItems = $basket["BASKET_ITEMS"];
	$sum = $basket["SUM"];
	require('order_content.php');
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
	   "PRICE_DELIVERY" => DELIVERY,
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
		CModule::IncludeModule("sale");
		
		$msg = 'Ваш заказ принят. Номер заказа ' . $ORDER_ID;

		$content = CSaleBasket::OrderBasket(
			$ORDER_ID, 
			$FUSER_ID, 
			$SITE_ID,
			False
			);
				
		/* -------- mail ----------- */	
		
		require('../../mail/make_message.php');
		
		$message = array(
			$ORDER_ID, 
			$user_id, 
			$arBasketItems, 
			$sum, 
			$arFields["COMMENTS"]=false
			);
			
		$subject = "Новый заказ";
		
		if(!empty($email))
		{			
			if(empty(send_mail($email, $subject, $message)))
			{
				$err[] = "При оформлении заказа возникла ошибка [5].";
			}
			else
			{
				//удаляем , чтобы очистилась малая корзина
				
				CSaleBasket::DeleteAll( $fUserID );
			}
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
	#include "make_message.php"; 
	#include("order_details.php");

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
	?>
	<table>
		<tr>
			<td><?=$ITEMS["PRODUCT_ID"]?></td>
			<td><a href="<?=$ITEMS['PRODUCT_URL']?></a>" target="_blank"><img src="<?=$ITEMS["PICT"]?>"/></a></td>
			<td><a href="<?=$ITEMS['PRODUCT_URL']?></a>" target="_blank"><?=$ITEMS['NAME']?></a></td>
			<td><a href="<=$ITEM["PRICE"]"?></td>
			
			<?if($ITEM["PROPS"]["MATHERIAL"]["ID"]):?>
			<td><a href="<?=$site_url;?>"><?=$ITEM["PROPS"]["MATHERIAL"]["PICTURE"]?>></a>
				<br><?=$ITEM["PROPS"]["MATHERIAL"]["NAME"] .'('. $ITEM["PROPS"]["MATHERIAL"]["ID"].')' ?></td>
			<?else:?>
			<td>&nbsp;</td>
			<?endif;?>
			
			<?if($ITEM["PROPS"]["COLORS"]["ID"]):?>
			<td><a href="<?=$site_url;?>"</a><?=$ITEM["PROPS"]["COLORS"]["PICTURE"]?></a>
				<br><?=$ITEM["PROPS"]["COLORS"]["NAME"] .'('. $ITEM["PROPS"]["COLORS"]["ID"]?>)</td>
			<?else:?>
			<td>&nbsp;</td>
			<?endif;?>
		</tr>	
	</table>
			
	<h3>Итого: <?=number_format($arRESULT["sum"]);?> руб.</h3>
	<?/*
	if(!empty($msg)): ?>
	<p><? ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>$msg));?></p>	
	<? endif; ?>
	*/?>
	
	<p><a href="/sale/payment/?ORDER_ID=<?=$ORDER_ID;?>&SUM=<?=$sum;?>">Оплатить</a></p>
	<?
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>