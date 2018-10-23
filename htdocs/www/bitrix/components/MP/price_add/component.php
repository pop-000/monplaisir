<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $DB;
global $APPLICATION;
CModule::IncludeModule("iblock");

$arResult["IBLOCK_ID"] = $arParams["IBLOCK_ID"];

if( !$arResult["IBLOCK_ID"] ) return;

// --------- производители		
$rsMnfct = CIBlockElement::GetList($arSort, array("IBLOCK_ID" => 4), false, false, array("ID", "NAME"));
while( $arMnfct = $rsMnfct->GetNext() )
{
	$arResult["MANUFACTURERS"][ $arMnfct["ID"] ] = $arMnfct["NAME"];
}

//-----------	 элементы	
$arFilter = array(
	"IBLOCK_ID" => $arResult["IBLOCK_ID"],
);
$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"NAME"
);
$rsElements = CIBlockElement::GetList(false, $arFilter, false, false, $arSelect);
while($arElement = $rsElements->GetNext())
{
	$dbItemProps = CIBlockElement::GetProperty($arResult["IBLOCK_ID"], $arElement['ID'], array(), Array());
	while($arProps = $dbItemProps->GetNext())
	{
		#echo "<pre>"; print_r($arProps); echo "<pre>";
		if( $arProps["CODE"] == "add_price" && $arProps["VALUE"] ) {
			$arElement["RUB"] = $arProps["VALUE"];
		}
		
		if( $arProps["CODE"] == "add_price_percent" && $arProps["VALUE"] ) {
			$arElement["PERCENT"] = $arProps["VALUE"];
		}
		
		if( $arProps["CODE"] == "goods" && $arProps["VALUE"] ) {
			$arElement["goods"][] = $arProps["VALUE"];
			$used["goods"][] = $arProps["VALUE"];
		}
			
		if( $arProps["CODE"] == "matherials" && $arProps["VALUE"] ) {
			$arElement["matherials"][] = $arProps["VALUE"];
			$used["matherials"][] = $arProps["VALUE"];
		}
	
		if( $arProps["CODE"] == "colors" && $arProps["VALUE"] ) {
			$arElement["colors"][] = $arProps["VALUE"];
			$used["colors"][] = $arProps["VALUE"];
		}
		
		if( $arProps["CODE"] == "mnfct" && $arProps["VALUE"] ) {
			$arElement["mnfct"] = $arProps["VALUE"];
		}
	}
	$arResult["ITEMS"][] = $arElement;
}
// ----------- материалы
$matherials = array_unique( $used["matherials"] );

$arFilter = array(
	"IBLOCK_ID" => 9,
	"ID" => $matherials
);
$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"NAME"
);
$rsSections = CIBlockSection::GetList(false, $arFilter, false, $arSelect, false);
while($arSect = $rsSections->GetNext())
{
	$arResult["MATHERIALS"][ $arSect["ID"] ] = $arSect;
}

// ----------- цвета
$colors = array_unique( $used["colors"] );

$arFilter = array(
	"IBLOCK_ID" => 8,
	"ID" => $colors
);
$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"NAME"
);
$rsSections = CIBlockSection::GetList(false, $arFilter, false, $arSelect, false);
while($arSect = $rsSections->GetNext())
{
	$arResult["COLORS"][ $arSect["ID"] ] = $arSect;
}

// ----------- товары
$goods = array_unique( $used["goods"] );

$arFilter = array(
	"IBLOCK_ID" => 2,
	"ID" => $goods
);
$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"NAME"
);
$rsSections = CIBlockSection::GetList(false, $arFilter, false, $arSelect, false);
while($arSect = $rsSections->GetNext())
{
	$arResult["GOODS"][ $arSect["ID"] ] = $arSect;
}

$this->IncludeComponentTemplate();
?>