<?php
if(!function_exists('make_message'))
{
	function make_message($id_order, $id_user, $arBasketItems, $sum, $comment=false)
	{
		$rsUser = CUser::GetByID($id_user);
		$arUser = $rsUser->Fetch();
		
		$result["email"] = $arUser["EMAIL"];
		$result["phone"] = $arUser["PERSONAL_PHONE"];

		$result["fio"] = implode(" ", array($arUser["NAME"], $arUser["SECOND_NAME"], $arUser["LAST_NAME"]));
		
		return $result;
	}
}	
?>
<html>
	<head></head>
		<body>
			<h2>Новый заказ</h2>
				<table cellpadding="8" cellspacing="0" border="1">
					<tr><td>ID пользователя</td><td><?=$id_user?></td></tr>
					<tr><td>ID заказа</td><td><?=$id_order?></td></tr>
					<tr><td>Как обращаться</td><td><?=$fio?></td></tr>
					<tr><td>Телефон</td><td><?=$phone?></td></tr>
					<tr><td>E-mail</td><td><?=$email?></td></tr>
					<tr><td>Комментарий</td><td><?=$comment?></td></tr>
					
					<?php echo $arBasketItems.php?>
					
				</table>

	<p>Письмо сформировано автоматически. Пожалуйста не отвечайте на него!</p>';
	</hr>
	
	<?include CONTACTS?>	
	
	<hr/>
	</body>
</html>	
