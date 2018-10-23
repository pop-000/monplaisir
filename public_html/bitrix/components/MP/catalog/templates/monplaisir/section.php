<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	$ob = CIBlockSection::GetList(Array(), Array("CODE" => $arResult["VARIABLES"]["SECTION_CODE"]), false, false, Array("ID"));
	$arSECT = $ob -> GetNext();
	$arResult["SECTION_ID"] = $arSECT["ID"];
#	echo "<pre>"; print_r( $arResult ); echo "</pre><hr />";
?>
<?$APPLICATION->IncludeComponent(
	"MP:elements",
	"section",
	array(
		"SECTION_ID" => $arResult["SECTION_ID"],
		"SHOW_SECTION" => true,
		"COLORS_COUNT" => $arParams["COLORS_COUNT"],
		"COLORS_IMG_SIZE" => $arParams["COLORS_IMG_SIZE"],
	),
	$component
);
?>