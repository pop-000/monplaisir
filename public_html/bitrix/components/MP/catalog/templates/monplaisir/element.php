<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?# echo "<pre>arParams<br/>"; print_r( $arResult ); echo "</pre>"; exit();?>
<?$ElementID = $APPLICATION->IncludeComponent(
	"MP:element",
	"single",
	array(
		"ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"COLORS_IMG_SIZE" => $arParams["COLORS_IMG_SIZE"],
	),
	$component
);?>
<?
if (0 < $ElementID)
{
	if($arParams["USE_ALSO_BUY"] == "Y" && \Bitrix\Main\ModuleManager::isModuleInstalled("sale"))
	{?>
<?$APPLICATION->IncludeComponent("bitrix:sale.recommended.products", ".default", array(
	"ID" => $ElementID,
	"MIN_BUYES" => $arParams["ALSO_BUY_MIN_BUYES"],
	"ELEMENT_COUNT" => $arParams["ALSO_BUY_ELEMENT_COUNT"],
	"LINE_ELEMENT_COUNT" => $arParams["ALSO_BUY_ELEMENT_COUNT"],
	"DETAIL_URL" => $arParams["DETAIL_URL"],
	"BASKET_URL" => $arParams["BASKET_URL"],
	"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
	"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
	"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"PRICE_CODE" => $arParams["PRICE_CODE"],
	"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
	"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
	"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
	'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
	'CURRENCY_ID' => $arParams['CURRENCY_ID'],
	'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
	),
	$component
);
?>
<?
	}
	?>
<?
}
?>