<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $DB;
global $APPLICATION;
global $CACHE_MANAGER;

$arResult["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
$arResult["SECTION_ID"] = $arParams["SECTION_ID"];

if( !$arResult["IBLOCK_ID"] ) return;
if( !$arResult["SECTION_ID"] ) return;

$arResult["SECTION_ID"] = $arParams["SECTION_ID"];

$arIblock = CIBlock::GetArrayByID( $arResult["IBLOCK_ID"] );
$arResult["IBLOCK_NAME"] = $arIblock["NAME"];
$arResult["IBLOCK_CODE"] = $arIblock["CODE"];

// --------- производители		
$rsMnfct = CIBlockElement::GetList($arSort, array("IBLOCK_ID" => 4), false, false, array("ID", "NAME"));
while( $arMnfct = $rsMnfct->GetNext() )
{
	$arResult["MANUFACTURERS"][ $arMnfct["ID"] ] = $arMnfct["NAME"];
}
$rsSection = CIBlockSection::GetByID( $arResult["SECTION_ID"] );
$arSection = $rsSection -> GetNext();
//-----------	 разделы	
$arSort = array(
	"left_margin" => "asc"
);

$arFilter = array(
	"IBLOCK_ID" => $arResult["IBLOCK_ID"],
	'>LEFT_MARGIN' => $arSection['LEFT_MARGIN'],
	'<RIGHT_MARGIN' => $arSection['RIGHT_MARGIN'],
	'>DEPTH_LEVEL' => $arSection['DEPTH_LEVEL']
);

$arSelect = array(
	"ID",
	"IBLOCK_ID",
	"IBLOCK_SECTION_ID",
	"SORT",
	"NAME",
	"DEPTH_LEVEL",
	"UF_MANUFACTURER"
);
$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, false );
while( $arSect = $rsSections->GetNext() )
{
	$arResult["SECTIONS"][ $arSect["ID"] ] = $arSect;
	
// ---- элементы 
	
	$arSort = array(
	"SORT" => "ASC"
	);
	$arFilter = array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"SECTION_ID" => $arSect["ID"],
	);
	$arSelect = array(
		"ID",
		"IBLOCK_SECTION_ID",
		"NAME",
		"PREVIEW_PICTURE",
	);

	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	while($arElement = $rsElements->GetNext())
	{
		if( $arElement["PREVIEW_PICTURE"] )
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$arElement["PREVIEW_PICTURE"], 
				array('width'=>50, 'height'=>50), 
				BX_RESIZE_IMAGE_EXACT, 
				true);
			$arElement["IMG_SRC"] = $arImg["src"];
		}
		$arResult["SECTIONS"][ $arSect["ID"] ]["ELEMENTS"][] = $arElement;
	}			
}
$this->IncludeComponentTemplate();
?>