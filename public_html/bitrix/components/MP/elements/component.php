<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $DB;
global $USER;
global $APPLICATION;
global $CACHE_MANAGER;
CModule::IncludeModule("catalog");

$arResult["IBLOCK_ID"] = 2;
$arResult["IBLOCK_CODE"] = "mebel";

$arResult["SHOW_SECTION"] = $arParams["SHOW_SECTION"];
$arResult["SECTION_ID"] = $arParams["SECTION_ID"];
$arResult["USE_FILTR"] = $arParams["USE_FILTR"];
$arResult["ADM_LIST"] = $arParams["ADM_LIST"];
$arResult["GOODS"] = $arParams["GOODS"];
$arResult["NAME"] = $arParams["NAME"];
$arResult["PROP_SEL"] = $arParams["PROP_SEL"];

$arResult["COLORS_COUNT"] = $arParams["COLORS_COUNT"];
if( !$arResult["COLORS_COUNT"] ) 
	$arResult["COLORS_COUNT"] = 10;

$arResult["COLORS_IMG_SIZE"] = $arParams["COLORS_IMG_SIZE"];
if( !$arResult["COLORS_IMG_SIZE"] ) 
	$arResult["COLORS_IMG_SIZE"] = 20;

if( !$arParams["CACHE_TIME"] ) $arParams["CACHE_TIME"] = 60*60*24*30;

if( $arResult["SHOW_SECTION"] && !$arResult["SECTION_ID"] )
{
	ShowError(GetMessage("CATALOG_SECTION_NOT_FOUND"));
	@define("ERROR_404", "Y");
	if($arParams["SET_STATUS_404"]==="Y")
		CHTTP::SetStatus("404 Not Found");
	return;
}
else
{
	$dbRes = CIBlockSection::GetByID( $arResult["SECTION_ID"] );
	$arResult["SECTION"] = $dbRes->GetNext();
}

/* ------------- FILTR ----------- */
if( $arResult["USE_FILTR"] )
{
	if( $_GET["filtr"] )
	{
		$filtr = "ON";
		if($_GET["matherials"])
		{
			$arResult["FILTR"]["MATHERIALS"] = $_GET["matherials"];
		}
		if($_GET["colors"])
		{
			$arResult["FILTR"]["COLORS"] = $_GET["colors"];
		}
	}
}
/* ------------------------------- */

/* -------------- basket ---------------- */
if (CModule::IncludeModule("sale"))
{
	$FUSER_ID = CSaleBasket::GetBasketUserID(True);
	$dbBasket = CSaleBasket::GetList(
			array(),
			array(
					"FUSER_ID" => $FUSER_ID,
					"LID" => SITE_ID,
					"ORDER_ID" => "NULL"
					),
			false,
			false,
			array()
			);
	while($arBaskeltItem = $dbBasket -> GetNext())
	{
		$arResult["BASKET_PROD"][] = $arBaskeltItem["PRODUCT_ID"];
	}
}

/*************************************************************************
			Work with cache
*************************************************************************/
if( $this->StartResultCache( $arParams["CACHE_TIME"], array( $USER->GetGroups(), $arNavigation )) )
{
	if( $arResult["USE_FILTR"] || $arResult["ADM_LIST"] ) // сброс кэширования
	{
		$this->AbortResultCache();
	}

/* =============== MANUFACTURERS ============= */
	
	$rsMnfct = CIBlockElement::GetList($arSort, array("IBLOCK_ID" => 4), false, false, array("ID", "NAME"));
	while( $arMnfct = $rsMnfct->GetNext() )
	{
		$arResult["MANUFACTURERS"][ $arMnfct["ID"]]["NAME"] = $arMnfct["NAME"];
	}
	
/* =============== MATHERIALS && COLORS ============= */	

	$propFields = array(
		array( "NAME" => "MATHERIALS", "ID" => 9 ),
		array( "NAME" => "COLORS", "ID" => 8 )
	);
	
	foreach( $propFields as $field )
	{
		$arSort = array(
			"left_margin" => "asc"
		);
		$arFilter = array(
			"IBLOCK_ID" => $field["ID"],
			"ACTIVE" => "Y",
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
			"UF_PRICE_ADD",
			"UF_PRICE_ADD_PERCENT"
		);
		$res = array();
		$rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, false );
		while( $arSect = $rsSections->GetNext() )
		{
			$res[ $arSect["ID"] ] = $arSect;
			if( $arSect["DEPTH_LEVEL"] == 1 )
			{
				$arResult["MANUFACTURERS"][ $arSect["UF_MANUFACTURER"] ][ $field["NAME"] ][] = $arSect["ID"];
			}
		}

		foreach( array( 3, 2 ) as $DL )
		{
			foreach( $res as $sectId => $arSect )
			{
				if( $arSect["DEPTH_LEVEL"] == $DL )
				{
					if( $arSect["IBLOCK_SECTION_ID"] )
					{
						$res[ $arSect["IBLOCK_SECTION_ID"] ]["CHILDS"][] = $sectId;
					}
				}
			}
		}
		$arMATH_COL[ $field["NAME"] ] = $res;
	}
	
/* =============== SECTIONS ============= */
	
	if( $arResult["SHOW_SECTION"] )
	{
		$arOrder = array(
			"LEFT_MARGIN" => "ASC"
		);
		$arFilter = array( 
			'IBLOCK_ID' => $arResult["IBLOCK_ID"],
			'>LEFT_MARGIN' => $arResult["SECTION"]['LEFT_MARGIN'],
			'<RIGHT_MARGIN' => $arResult["SECTION"]['RIGHT_MARGIN'],
			'>DEPTH_LEVEL' => $arResult["SECTION"]['DEPTH_LEVEL']
			);
		$arSelect = array( 
			"ID",
			"NAME", 
			"LEFT_MARGIN", 
			"RIGHT_MARGIN", 
			"DEPTH_LEVEL", 
			"IBLOCK_ID", 
			"IBLOCK_SECTION_ID", 
			"LIST_PAGE_URL", 
			"SECTION_PAGE_URL" 
		);
		$rsSections = CIBlockSection::GetList( $arOrder, $arFilter, array(), $arSelect );
		$rsSections->SetUrlTemplates("", $arParams["SECTION_URL"]);
		while( $arSect = $rsSections->GetNext() )
		{
			$arResult["SECTIONS"][ $arSect["ID"] ] = $arSect;
			$arResult["SECTIONS"]["IDS"][] = $arSect["ID"];
		}
		$arResult["SECTIONS"]["IDS"][] = $arResult["SECTION_ID"]; 
	}
	
/* =============== ELEMENTS ============= */

	$arResult["ITEMS"] = array();
	
	$arSort = array(
		"SECTION_ID" => "ASC",
		"SORT" => "ASC"
		);
	$arFilter = array(
		"ACTIVE_DATE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		);
	if( $arResult["SHOW_SECTION"] ){
		$arFilter["SECTION_ID"] = $arResult["SECTIONS"]["IDS"];
	}
	if( count( $arResult["GOODS"] ))
	{
		$arFilter["ID"] = $arResult["GOODS"];
	}
	if( !$arParams["ADM_LIST"] )
	{
		/*
		$arNavParams = array(
			"nPageSize" => '12',
			"bDescPageNumbering" => 'Описание',
			"bShowAll" => 'Y',
		);
		*/			
	}
	$arSelect = array(
		"ID",
		"NAME",
		"PREVIEW_PICTURE",
		"DETAIL_PICTURE",
		"DETAIL_PAGE_URL",
	);
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);

		
	while($obElement = $rsElements->GetNextElement())
	{
		$arItem = $obElement->GetFields();

		$arItem['ID'] = intval($arItem['ID']);
		
		if( $arResult["SHOW_SECTION"] ){
			if( $arItem['IBLOCK_SECTION_ID'] == $arResult["SECTION_ID"] )
			{
				$arResult["SECTION"]["ITEMS"][] = $arItem['ID'];
			}
			else
			{
				$arResult["SECTIONS"][ $arItem['IBLOCK_SECTION_ID'] ]["ITEMS"][] = $arItem['ID'];
			}
		}

		/* ------------ PRICES ---------- */
		
		$arPrice = array();
		if( $arParams["ADM_LIST"] )
		{
			$dbPrice = CPrice::GetList(
				array("PRICE" => "ASC"),
				array("PRODUCT_ID" => $arItem['ID']),
				false,
				false,
				array()
			);
			while($arRow = $dbPrice -> GetNext())
			{
				$arPrice[] = array("ID" => $arRow["CATALOG_GROUP_ID"],
									"NAME" => $arRow["CATALOG_GROUP_NAME"],
									"PRICE" => ceil($arRow["PRICE"])
									);
			}
		}
		else
		{
			$dbPrice = CPrice::GetList(
				array("PRICE" => "ASC"),
				array("PRODUCT_ID" => $arItem['ID'], "CAN_ACCESS" => "Y", "CAN_BUY" => "Y", "CATALOG_GROUP_ID" => array(3)),
				false,
				false,
				array()
			);
			$arPrice = $dbPrice->Fetch();
		}
		$arDiscounts = CCatalogDiscount::GetDiscountByPrice(
				$arPrice["ID"],
				$USER->GetUserGroupArray(),
				"N",
				false,
				false
			);
	#	echo "<pre>"; print_r( $arDiscounts ); echo "</pre>"; exit();	
		$discount_price = CCatalogProduct::CountPriceWithDiscount(
				$arPrice["PRICE"],
				$arPrice["CURRENCY"],
				$arDiscounts
			);
		$arPrice["DISCOUNT"] = ceil($arDiscounts[0]["VALUE"]);	
		if($discount_price > 0) $arPrice["DISCOUNT_PRICE"] = ceil($discount_price);
		if($arPrice["PRICE"]) $arPrice["PRICE"] = ceil($arPrice["PRICE"]);
		$arItem["PRICE"] = $arPrice;
		
		$arPrice["DISCOUNT_PRICE"] > 0 ?  $arForFiltr["PRICE"][] = $arPrice["DISCOUNT_PRICE"] : $arForFiltr["PRICE"][] = $arPrice["PRICE"];


		/* ------------ PICTURES ------------ */
		
		$img_fields = array( "PREVIEW_PICTURE", "DETAIL_PICTURE" );
		foreach( $img_fields as $img_field )
		{
			$arImg = array();
			$arImg = CFile::GetFileArray( $arItem[ $img_field ] );
			
			$arItem[ $img_field ] = array(
					"ID" => $arImg["ID"],
					"SRC" => $arImg["SRC"],
					"WIDTH" => $arImg["WIDTH"],
					"HEIGHT" => $arImg["HEIGHT"]
			);
			
			if ($arItem[ $img_field ])
			{
				if ($arItem[ $img_field ]["ALT"] == "")
					$arItem[ $img_field ]["ALT"] = $arItem["NAME"];
				if ($arItem[ $img_field ]["TITLE"] == "")
					$arItem[ $img_field ]["TITLE"] = $arItem["NAME"];
			}
		}
	
		/* ------------ PROPERTIES && MORE_PHOTO ------------ */
		
		$dbItemProps = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem['ID'], array(), Array());

		while($arProps = $dbItemProps->GetNext())
		{
			if( $arProps["VALUE"] )
			{
				$arItem["PROPERTIES"][ $arProps["CODE"] ][ "NAME" ] = $arProps["NAME"];

				if( $arProps["MULTIPLE"] == "Y" )
				{
					$arItem["PROPERTIES"][ $arProps["CODE"] ][ "VALUES" ][] = $arProps["VALUE"];
				}
				else
				{
					switch( $arProps["PROPERTY_TYPE"] )
					{
						case "L":
							$arItem["PROPERTIES"][ $arProps["CODE"] ]["VALUE"] = $arProps["VALUE_ENUM"];
							break;
							
						default:
							$arItem["PROPERTIES"][ $arProps["CODE"] ]["VALUE"] = $arProps["VALUE"];
							break;	
					}
				}
			}
		}
	
		/* --------- MATHERIALS && COLORS ----------*/
	
		/* ----------- если не установлен цвет и материал устанавливаем из производителя корневой уровень ------------ */			

		if( !count( $arItem["PROPERTIES"]["COLORS"]["VALUES"] ) )
		{
			$mnfct_id = $arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"];
			$arItem["PROPERTIES"]["COLORS"]["VALUES"][] = $arResult["MANUFACTURERS"][ $mnfct_id ]["COLORS"][0];
		}
		
		if( !count( $arItem["PROPERTIES"]["MATHERIALS"]["VALUES"] ) )
		{
			if( $arItem["PROPERTIES"]["HARD"]["VALUE"] != "Да" )
			{
				$mnfct_id = $arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"];
				$mnfct = $arResult["MANUFACTURERS"][ $mnfct_id ]["MATHERIALS"][0];
				$arItem["PROPERTIES"]["MATHERIALS"]["VALUES"] = $mnfct;
			}
		}

		/* ---------------- */
		
		foreach( $propFields as $field )
		{
			foreach( $arItem["PROPERTIES"][ $field["NAME"] ]["VALUES"] as $val )
			{
				$arPropUsed[ $field["NAME"] ][] = $val; 
			}
		}
		
		
		// ----------- упрощаем 
	
		$arItem["MANUFACTURER"] = $arResult["MANUFACTURERS"][ $arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"] ]["NAME"];
		$arItem["HARD"] = $arItem["PROPERTIES"]["HARD"]["VALUE"];
		$arItem["GABARIT"] = array(
			"HEIGHT" => $arItem["PROPERTIES"]["HEIGHT"]["VALUE"],
			"WIDTH" => $arItem["PROPERTIES"]["WIDTH"]["VALUE"],
			"DEPTH" => $arItem["PROPERTIES"]["DEPTH"]["VALUE"],
			"DIAMETR" => $arItem["PROPERTIES"]["DIAMETR"]["VALUE"]
			);
			
		if(  $arItem["PROPERTIES"]["SPECIALOFFER"] )
			$arItem["MARKERS"]["SPECIALOFFER"] = $arItem["PROPERTIES"]["SPECIALOFFER"]["NAME"];
	
		if(  $arItem["PROPERTIES"]["NEWPRODUCT"] )
			$arItem["MARKERS"]["NEWPRODUCT"] = $arItem["PROPERTIES"]["NEWPRODUCT"]["NAME"];
		
		if(  $arItem["PROPERTIES"]["SALELEADER"] )
			$arItem["MARKERS"]["SALELEADER"] = $arItem["PROPERTIES"]["SALELEADER"]["NAME"];
		
		if( in_array( $arItem["ID"], $arResult["BASKET_PROD"] ) )
			$arItem["IN_BASKET"] = true;
		
		$arResult["ITEMS"][ $arItem["ID"] ] = $arItem;
	}

/* =============== MATHERIALS && COLORS ============= */
	
	foreach( $propFields as $field )
	{
		$arPropUsed[ $field["NAME"] ] = array_unique( $arPropUsed[ $field["NAME"] ] );
	}
	

	foreach( $propFields as $field )
	{	
		// ---- добавляем детей в выборку 
		foreach( $arPropUsed[ $field["NAME"] ] as $key => $prop_sect_id )
		{
			$arSect = $arMATH_COL[ $field["NAME"] ][ $prop_sect_id ];
			$arResult["PROP_NAMES"][ $field["NAME"] ][ $prop_sect_id ] = $arMATH_COL[ $field["NAME"] ][ $prop_sect_id ][ "NAME" ];
			$arPropUsed[ $field["NAME"] ][ $prop_sect_id ] = $arSect;
			
			if( count( $arSect["CHILDS"] ) )
			{
				foreach( $arSect["CHILDS"] as $child )
				{
					$child_item = $arMATH_COL[ $field["NAME"] ][$child];
					$arPropUsed[ $field["NAME"] ][ $prop_sect_id ]["CHILDS"][ $child ] = $child_item;
					$arPropUsed[ $field["NAME"] ][ $child_item["ID"] ] = $child_item;
					
					if( is_array( $child_item["CHILDS"] ) )
					{
						foreach( $child_item["CHILDS"] as $grandch )
						{
							$grandch_item = $arMATH_COL[ $field["NAME"] ][ $grandch ];
							$arPropUsed[ $field["NAME"] ][ $grandch ] = $grandch_item;
							if( count( $grandch_item["CHILDS"] ) )
							{
								foreach( $grandch_item["CHILDS"] as $grgr )
								{
									$arPropUsed[ $field["NAME"] ][ $grgr ] = $arMATH_COL[ $field["NAME"] ][ $grgr ];
								}
							}
						}
					}
				}
			}
		}
	}
	
	// удаляем пустышки
	foreach( $propFields as $field )
	{
		foreach( $arPropUsed[ $field["NAME"] ] as $key => $val )
		{
			if( !is_array($val))
			{
				unset( $arPropUsed[ $field["NAME"] ][ $key ] ); 
			}
		}
	}
#echo "<pre>"; print_r( $arPropUsed ); echo "</pre>"; exit();		
	// собираем id используемых разделов
	foreach( $propFields as $field )
	{
		foreach( $arPropUsed[ $field["NAME"] ] as $key => $val )
		{
			if( $val["ID"] )
			{
				$prop_used_ids[ $field["NAME"] ][] = $val["ID"];
			}
			if( count($val["CHILDS"]) )
			{
				foreach( $val["CHILDS"] as $child )
				{
					$prop_used_ids[ $field["NAME"] ][] = $child["ID"];
				}
				if( count( $child["CHILDS"] ) )
				{
					foreach( $child["CHILDS"] as $grand_child )
					{
						$prop_used_ids[ $field["NAME"] ][] = $grand_child;
					}
				}
			}
		}
	}
#echo "<pre>"; print_r( $arPropUsed ); echo "</pre>"; exit();
// ---- получаем элементы 
	foreach( $propFields as $field )
	{
		$math = &$arPropUsed[ $field["NAME"] ];
		$arSort = array(
			"SORT" => "ASC"
		);
		$arFilter = array(
			"IBLOCK_ID" => $field["ID"],
			"SECTION_ID" => $prop_used_ids[ $field["NAME"] ],
		);
		$arSelect = array(
			"ID",
			"IBLOCK_SECTION_ID",
			"NAME",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
		);

		$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $el_limit, $arSelect);
		while($arElement = $rsElements->GetNext())
		{
			if( $arElement["IBLOCK_SECTION_ID"] && is_array( $math[ $arElement["IBLOCK_SECTION_ID"] ] ) )
			{
				$math[ $arElement["IBLOCK_SECTION_ID"] ]["ELEMENTS"][] = $arElement;
			}			
		}
	}
#echo "<pre>"; print_r( $arPropUsed ); echo "</pre>"; exit();	
	foreach( $propFields as $field )
	{
		foreach( $arPropUsed[ $field["NAME"] ] as $key => $val )
		{
			if( !is_array( $val ) )
			{
				unset( $arPropUsed[ $field["NAME"][$key] ]); 
			}
		}
	}
#echo "<pre>"; print_r( $arPropUsed ); echo "</pre>"; exit();
	
	// ---- корректируем количество элементов
	
	foreach( $propFields as $field )
	{
		foreach( $arPropUsed[ $field["NAME"] ] as $sect_id => $section )
		{
			
			$count = count( $section["ELEMENTS"] );
		// ----- если мало, берем из потомков	
			if( $count < $arResult["COLORS_COUNT"] )
			{
				// - дети	
				if( count( $section["CHILDS"] ))
				{
					foreach( $section["CHILDS"] as $child_sect_id => $val_child_sect_id )
					{
						if( count( $arPropUsed[ $field["NAME"] ][ $child_sect_id ]["ELEMENTS"] ) )
						{
							foreach( $arPropUsed[ $field["NAME"] ][ $child_sect_id ]["ELEMENTS"] as $el )
							{
								$section["ELEMENTS"][] = $el;
							}
						}
					}					
				}
				$count = count( $section["ELEMENTS"] );	
				// - внуки
				if( $count < $arResult["COLORS_COUNT"] )
				{
					foreach( $section["CHILDS"] as $child_sect_id => $val_child_sect_id )
					{
						foreach( $val_child_sect_id["CHILDS"] as $grand_ch_id)
						{
							if( count( $arPropUsed[ $field["NAME"] ][ $grand_ch_id ]["ELEMENTS"] ) )
							{
								foreach( $arPropUsed[ $field["NAME"] ][ $grand_ch_id ]["ELEMENTS"] as $el )
								{
									$section["ELEMENTS"][] = $el;
								}
							}
						}
					}
					
				}
			}
			$count = count( $section["ELEMENTS"] );
			
			if( $count )
			{
				// ----- если много, обрезаем
				if( $count > $arResult["COLORS_COUNT"] )
				{
					$section["ELEMENTS"] = array_slice( $section["ELEMENTS"], 0, $arResult["COLORS_COUNT"] );
				}
					
				if( is_array( $section["ELEMENTS"] ) )
				{
					$arResult["PROP_SETS"][ $field["NAME"] ][] = array(
						"SECTIONS" => $sect_id,
						"COUNT" => $count,
						"ELEMENTS" => $section["ELEMENTS"]
						);
				}
			}
		}
	}
#echo "<pre>"; print_r( $arResult["PROP_SETS"] ); echo "</pre>"; exit();
	// -------------- перебираем элементы и добавляем наборы
	foreach( $arResult["ITEMS"] as $item )
	{
		foreach( $propFields as $field )
		{
			$prop = $item["PROPERTIES"][ $field["NAME"] ]["VALUES"];
			
			if( count( $prop ) )
			{
				foreach( $arResult["PROP_SETS"][ $field["NAME"] ] as $id_set => $set_val )
				{
					if( in_array( $set_val["SECTIONS"], $prop ) || $set_val["SECTIONS"] == $prop )
					{
						$arResult["ITEMS"][ $item["ID"] ][ "SETS" ][ $field["NAME"] ] = $id_set;
					}
				}
			}
		}		
	}

/* ------------- end  MATHERIAL && COLORS ----------- */

	
	if( !$arResult["NAME"] )
		$arResult["NAME"] = $arResult["SECTION"]["NAME"];
	
	$this->IncludeComponentTemplate();
}	
?>