<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$path = $APPLICATION->GetCurPage();
$parts = explode( "/", $path );
$parts = array_filter( $parts );
$last = end( $parts );
$bc = array();

if( $arParams["START"] ){
	$bc[] = array(
		"NAME" => $arParams["START"]["NAME"],
		"URL" => $arParams["START"]["URL"]
		);
}

foreach( $parts as $item )
{	
	$obSect = CIBlockSection::GetList( array(), array( "CODE" => $item ), false, false, array( "IBLOCK_SECTION_ID" ) );
	$arSect = $obSect->GetNext();
	$parent = $arSect["IBLOCK_SECTION_ID"];
	
	if( $parent )
	{
		$obParent = CIBlockSection::GetList( array(), array( "ID" => $parent ), false, false, array( "NAME", "CODE" ) );
		$arParent = $obParent->GetNext();
		$bc[] = array(
			"NAME" => $arParent["NAME"],
			"URL" => $arParent["CODE"],
		);
	}
	
	if( $arSect["NAME"] )
	{
		if( $parent )
		{
			$bc[] = array(
				"NAME" => $arSect["NAME"],
				"URL" => $arSect["CODE"]
			);
		}
		else
		{
			if( $item == $last )
			{
				$last_item = true; 
			}
			else
			{
				$last_item = false; 
			}
			
			$bc[] = array(
				"NAME" => $arSect["NAME"],
				"URL" => $arSect["CODE"],
				"LAST" => $last_item
			);
		}
	}
}
$arResult = "";
foreach( $bc as $item )
{
	if( $item["URL"] != $arParams["START"]["URL"] )
	{
		$url = $arParams["START"]["URL"] . "/" . $item["URL"];
	}
	else
	{
		$url = $item["URL"];
	}
	$arResult .= '<a href="/' . $url . '/">' . $item["NAME"] . '</a> / ';
}
if( count($bc) > 1 )
{
	$this->IncludeComponentTemplate();
}	
?>