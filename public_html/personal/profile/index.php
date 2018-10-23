<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require("../../need_auth.php");
$APPLICATION->SetTitle("Настройки пользователя");
$APPLICATION->IncludeComponent(
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
$APPLICATION->IncludeComponent("bitrix:main.profile", "", Array(
	"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
	),
	false
);
need_auth($email);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>