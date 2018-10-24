<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Монплезир. Интернет-магазин мебели");

$obGoods = CIBlockElement::GetList(
		Array("SORT"=>"ASC"),
		Array(
			"IBLOCK_ID"=> 2,
			"ACTIVE" => "Y",
			array
				(
				"LOGIC" => "OR",
				"!PROPERTY_NEWPRODUCT_VALUE"=>false,
				"!PROPERTY_SALELEADER_VALUE"=>false
				)
			),
		false,
		false,
		Array(
			"ID", 
			"PROPERTY_NEWPRODUCT",
			"PROPERTY_SALELEADER"
			)
		);
if( isset($obGoods) )
{	
	while($row = $obGoods -> Fetch())
	{
		if($row["PROPERTY_NEWPRODUCT_VALUE"]) $arResult["SORTED"]["NEWPRODUCT"][] = $row["ID"];
		if($row["PROPERTY_SALELEADER_VALUE"]) $arResult["SORTED"]["SALELEADER"][] = $row["ID"];
	}
}

$APPLICATION->IncludeComponent(
	"MP:elements",
	"top",
	array(
		"SHOW_ONE_ELEMENT" => false,
		"SHOW_SECTION" => false,
		"ELEMENT_ID" => null,
		"SECTION_ID" => null,
		"GOODS" =>  $arResult["SORTED"]["NEWPRODUCT"],
		"PROP_SEL" => "NEWPRODUCT",
		"NAME" => "Новинки",
		"USE_FILTR" => false,
		"ADM_LIST" => false,
		"SET_TITLE" => false,
		"SET_META_KEYWORDS" => false,
		"SET_META_DESCRIPTION" => false,
		"COLORS_COUNT" => $arParams["COLORS_COUNT"],
		"COLORS_IMG_SIZE" => $arParams["COLORS_IMG_SIZE"],
	),
	$component
);

$APPLICATION->IncludeComponent(
	"MP:elements",
	"top",
	array(
		"SHOW_ONE_ELEMENT" => false,
		"SHOW_SECTION" => false,
		"ELEMENT_ID" => null,
		"SECTION_ID" => null,
		"GOODS" =>  $arResult["SORTED"]["SALELEADER"],
		"PROP_SEL" => "SALELEADER",
		"NAME" => "Лидеры продаж",
		"USE_FILTR" => false,
		"ADM_LIST" => false,
		"SET_TITLE" => false,
		"SET_META_KEYWORDS" => false,
		"SET_META_DESCRIPTION" => false,
		"COLORS_COUNT" => $arParams["COLORS_COUNT"],
		"COLORS_IMG_SIZE" => $arParams["COLORS_IMG_SIZE"],
	),
	$component
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>