<?php
function make_message( $id_order, $id_user, $arBasketItems, $to_user = false, $site_url, $sum, $comment=false)
{
	$rsUser = CUser::GetByID($id_user);
	$arUser = $rsUser->Fetch();
	
	$email = $arUser["EMAIL"];
	$phone = $arUser["PERSONAL_PHONE"];

	$fio = implode(" ", array($arUser["NAME"], $arUser["SECOND_NAME"], $arUser["LAST_NAME"]));
	
	$contacts = '<p>&laquo;Монплезир&raquo;. Интернет-магазин мебели<br/>
	Санкт-Петербург, МЦ «Гранд Каньон», ул. Шостаковича, д. 8, лит. А, 2 этаж, секция 203.<br/>
	Тел.: <a href="tel:78122071049">+7 (812) 207-10-49</a>.<br/>
	e-mail: <a href="mailto:order@monplaisir-mebel.ru">order@monplaisir-mebel.ru</a></p>
	<p>Работаем ежедневно с 11:00 до 21:00</p>';
	
	$message = '<html>';
	$message .= '<head></head>';
	$message .= '<body>';

	if( empty( $to_user ) ) 
	{
		/// письмо редактору
		$message .= '<h2>Новый заказ</h2>' . PHP_EOL;
		$message .= '<table cellpadding="8" cellspacing="0" border="1">' . PHP_EOL;
		$message .= '<tr><td>ID пользователя</td><td>' . $id_user . '</td></tr>' . PHP_EOL;
		$message .= '<tr><td>ID заказа</td><td>' . $id_order . '</td></tr>' . PHP_EOL;
		$message .= '<tr><td>Как обращаться</td><td>' . $fio . '</td></tr>' . PHP_EOL;
		$message .= '<tr><td>Телефон</td><td>' . $phone . '</td></tr>' . PHP_EOL;
		$message .= '<tr><td>E-mail</td><td>' . $email . '</td></tr>' . PHP_EOL;

		if($comment)
		{
			$message .= '<tr><td>Комментарий</td><td>' . $comment . '</td></tr>' . PHP_EOL;
		}
		$message .= '</table>' . PHP_EOL;
	}
	else
	{	
		/// письмо покупателю
		$message .= '<p>Здравствуйте, ' . $fio . '</p>';
		$message .= '<p>Вами был сделан заказ на сайте ' . $site_url . '.<br>';
		$message .= 'Номер Вашего заказа: ' . $id_order . '.<br>';
		if(!empty($phone))
		{
			$message .= 'Вы указали контактный телефон: ' . $phone . '</p>';
		}			
	}

	$message .= '<h3>Состав заказа:</h3>';
	$message .= '<table cellpadding="8" cellspacing="0" border="1">';
	$message .= '<thead>';
	$message .= '<tr>';
	$message .= '<th>ID</th>';
	$message .= '<th>Фото</th>';
	$message .= '<th>Название</th>';
	$message .= '<th>Цена</th>';
	$message .= '<th>Материал</th>';
	$message .= '<th>Цвет</th>';
	$message .= '</tr>';
	$message .= '</thead>';

	foreach( $arBasketItems as $item )
	{
		$message .= '<tr>';
		$message .= '<td>' . $item["PRODUCT_ID"] . '</td>';
		$message .= '<td><a href="' . $site_url . $item["DETAIL_PAGE_URL"] . '" target="_blank"><img src="' . $site_url . $item["PICT"] . '"/></a></td>';
		$message .= '<td><a href="' . $site_url . $item["DETAIL_PAGE_URL"] . '" target="_blank">' . $item["NAME"] . '</a></td>';
		$message .= '<td>' . $item["PRICE"] . '</td>';
		
		if( $item["PROPS"]["MATHERIAL"]["ID"] )
		{
		$message .= '<td><img src="' . $site_url . $item["PROPS"]["MATHERIAL"]["PICTURE"] . '"/><br>' . $item["PROPS"]["MATHERIAL"]["NAME"] . 
		'(' . $item["PROPS"]["MATHERIAL"]["ID"] . ')</td>';	
		}
		else
		{
		$message .= '<td>&nbsp;</td>';
		}
		
		$message .= '<td><img src="' . $site_url . $item["PROPS"]["COLORS"]["PICTURE"] . '"/><br>' . $item["PROPS"]["COLORS"]["NAME"] . 
		'(' . $item["PROPS"]["COLORS"]["ID"] . ')</td>';
	}

	$message .= '</table>' . PHP_EOL;

	$message .= '<h3>Итого: ' . number_format( $sum ) . ' руб.</h3>';
	
	if( !empty( $to_user ) ) /// письмо покупателю
	{
		$message .= '<p>Письмо сформировано автоматически. Пожалуйста не отвечайте на него!</p>';
		$message .= '<hr/>' . $contacts;
	}
	
	$message .= '</body>';
	$message .= '</html>';
	
	return $message;
}
?>