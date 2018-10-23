<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Настройки пользователя");
if(!empty($_GET))
{
	echo "<pre>"; print_r($_GET); echo "</pre>";
}
?>
<p>Вы успешно оплатили заказ.</p>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>