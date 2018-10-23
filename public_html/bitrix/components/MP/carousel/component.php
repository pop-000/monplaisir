<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
$arSort = array(
		"SORT" => "ASC"
	);
$arFilter = Array("ACTIVE"=>"Y",
					"IBLOCK_CODE"=>"carousel",
					"<=DATE_ACTIVE_FROM" => array(false, ConvertTimeStamp(false, "FULL")),
					">=DATE_ACTIVE_TO"   => array(false, ConvertTimeStamp(false, "FULL")));
$arSelectFields = Array(
	"ID",
	"IBLOCK_ID",
	"NAME", 
	"DETAIL_PICTURE",
	"PREVIEW_TEXT"
	);
$res = CIBlockElement::GetList(
	$arSort,
	$arFilter,
	false,
	false,
	$arSelectFields
	);

while($ob = $res->GetNextElement())
{
	$arItem = $ob->GetFields();
	$arImg = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
	$arItem["IMG"] = $arImg;
	$arItem["IMG_HTML"] = 'src="' . $arImg["SRC"] . '" width="' . $arImg["WIDTH"] . 'px height="' . $arImg["HEIGHT"] . 'px"';
	
	$dbItemProps = CIBlockElement::GetProperty(
			$arItem["IBLOCK_ID"], 
			$arItem['ID'], 
			array(), 
			Array()
		);
		
	while($arProps = $dbItemProps->Fetch())
	{
		if( $arProps["MULTIPLE"] == "Y" )
		{
				
			$dbItemMultiProps = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem['ID'], array(), Array( "CODE" => $arProps["CODE"] ));
			while($arMultiProps = $dbItemMultiProps->Fetch())
			{
				if( $arMultiProps["VALUE"] ){
					$arItem["PROPERTIES"][ $arProps["CODE"] ][ "VALUES" ][] = $arMultiProps["VALUE"];
				}
			}
		}
		else
		{
			if( $arProps["VALUE"] ){
				$arItem["PROPERTIES"][ $arProps["CODE"] ] = $arProps["VALUE"];
			}
		}
	}
	if( $arItem["PROPERTIES"][ "LINK" ] )
	{
		$arItem[ "URL" ] = $arItem["PROPERTIES"][ "LINK" ];
	}
	elseif( $arItem["PROPERTIES"][ "SECTION_ID" ] )
	{
		$arRes = array();
		$obRes = CIBlockSection::GetByID( $arItem["PROPERTIES"][ "SECTION_ID" ] );
		if($arRes = $obRes->GetNext())
		  $arItem[ "URL" ] = $arRes[ "SECTION_PAGE_URL" ];
	}
	elseif( $arItem["PROPERTIES"][ "ELEMENT_ID" ] )
	{
		$arRes = array();
		$obRes = CIBlockElement::GetByID( $arItem["PROPERTIES"][ "ELEMENT_ID" ] );
		if($arRes = $obRes->GetNext())
		  $arItem[ "URL" ] = $arRes[ "DETAIL_PAGE_URL" ];
	}		
	
	if( $arItem["PROPERTIES"][ "BACKGROUND" ] )
		$arItem["BACKGROUND" ] = $arItem["PROPERTIES"][ "BACKGROUND" ];
	
	unset( $arItem["PROPERTIES"] );
		
	$arResult[] = $arItem;
}
$this->IncludeComponentTemplate();
?>