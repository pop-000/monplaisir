<?
function MP_get_matherials( $iblock_id )
{
	$arResult["IBLOCK_ID"] = intval( $iblock_id );
	if( !$arResult["IBLOCK_ID"] )
	{
		return false;
	}
	/*
	global $DB;
	global $APPLICATION;
	global $CACHE_MANAGER;
	CModule::IncludeModule("catalog");
*/
	/* ------- SECTIONS ----------- */
	$arResult["SECTIONS"] = array();
	$arMnfct = array();
	
	$arSort = array(
		"left_margin" => "asc"
	);
	$arFilter = array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"GLOBAL_ACTIVE" => "Y"
	);
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"SORT",
		"NAME",
		"DEPTH_LEVEL",
		"UF_MANUFACTURER",
		"UF_PRICE_ADD"
	);
	$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, false );
	while( $arSect = $rsSections->GetNext() )
	{
		$arResult["SECTIONS"][ $arSect["ID"] ] = $arSect;
		if( $arSect["DEPTH_LEVEL"] == 1 )
		{
			$arMnfct[ $arSect["UF_MANUFACTURER"] ][] = $arSect["ID"];
		}
	}	

	/* ------- ELEMENTS ----------- */	
		
	$arResult["ELEMENTS"] = array();
	$arSort = array(
		"IBLOCK_SECTION_ID" => "ASC",
		"SORT" => "ASC"
	);
	$arFilter = array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"]
	);
	$arSelect = array(
		"ID",
		"IBLOCK_SECTION_ID",
		"NAME",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
	);
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);

	while($arElement = $rsElements->Fetch())
	{
		$arResult["ELEMENTS"][ $arElement['ID'] ] = $arElement;
		$arResult["SECTIONS"][ $arElement['IBLOCK_SECTION_ID'] ]["ELEMENTS"][] = $arElement['ID'];
	}
	
/* ------------- MANUFACTURERS ------------ */
	
	foreach( $arMnfct as $key => $val )
	{
		$arResult["MANUFACTURERS"][ $key ] = array_unique( $val );
	}	
	
	
/* ------ include childs ----- */

	foreach( array( 3, 2, 1 ) as $DL )
	{
		foreach( $arResult["SECTIONS"] as $sectId => $arSect )
		{
			if( $arSect["DEPTH_LEVEL"] == $DL )
			{
				if( $arSect["IBLOCK_SECTION_ID"] )
				{
					$arResult["SECTIONS"][ $arSect["IBLOCK_SECTION_ID"] ]["CHILDS"][] = $sectId;
					if( count( $arSect["ELEMENTS"] ) )
					{
						foreach( $arSect["ELEMENTS"] as $el )
						{
							$arResult["SECTIONS"][ $arSect["IBLOCK_SECTION_ID"] ]["ALL_ELEMENTS"][] = $el;
						}
					}
				}
				else
				{
					if( count( $arSect["ELEMENTS"] ) )
					{
						foreach( $arSect["ELEMENTS"] as $el )
						{
							$arResult["SECTIONS"][ $sectId ]["ALL_ELEMENTS"][] = $el;
						}
					}
				}
			}
		}
	}
	
	return $arResult;
}
?>