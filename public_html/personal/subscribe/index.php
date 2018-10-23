<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рассылки");
?>
<p>
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
</p>
<?$APPLICATION->IncludeComponent(
	"bitrix:subscribe.edit",
	"clear",
	Array(
		"AJAX_MODE" => "N",
		"SHOW_HIDDEN" => "N",
		"ALLOW_ANONYMOUS" => "Y",
		"SHOW_AUTH_LINKS" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"SET_TITLE" => "N",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N"
	),
false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>