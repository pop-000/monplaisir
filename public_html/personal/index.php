<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Персональный раздел");
?> 
<?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"grey_tabs",
	Array(
		"ROOT_MENU_TYPE" => "section",
		"MAX_LEVEL" => "1",
		"CHILD_MENU_TYPE" => "section",
		"USE_EXT" => "Y",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N",
		"MENU_CACHE_TYPE" => "N",
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_CACHE_GET_VARS" => array()
	)
);
?>
 
<h1>Мой кабинет</h1>
 
<div class="bx_page"> 	 
  <p>В личном кабинете Вы можете проверить текущее состояние корзины, ход выполнения Ваших заказов, просмотреть или изменить личную информацию, а также подписаться на новости и другие информационные рассылки. </p>
 	 
  <div> 		 
    <h2>Личная информация</h2>
   		<a href="profile/" >Изменить регистрационные данные</a> 	</div>
 	 
  <div> 		 
    <h2>Заказы</h2>
   		<a href="order/" >Ознакомиться с состоянием заказов</a> 
    <br />
   		<a href="cart/" >Посмотреть содержимое корзины</a> 
    <br />
   		<a href="order/?filter_history=Y" >Посмотреть историю заказов</a> 
    <br />
   	</div>
 	 
  <div> 		 
    <h2>Подписка</h2>
   		<a href="subscribe/" >Изменить подписку</a> 	</div>
 </div>
 <?php # echo "User<br/><pre>"; print_r($USER); echo "</pre>"; ?>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>