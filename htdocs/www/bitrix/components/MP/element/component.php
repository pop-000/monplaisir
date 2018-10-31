<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $DB;
global $USER;
global $APPLICATION;
global $CACHE_MANAGER;
CModule::IncludeModule("catalog");

$arResult["IBLOCK_ID"] = 2;
$id = $arParams["ID"];

$arResult["COLORS_IMG_SIZE"] = $arParams["COLORS_IMG_SIZE"];
if( !$arResult["COLORS_IMG_SIZE"] ) 
	$arResult["COLORS_IMG_SIZE"] = 20;

if( !$arParams["CACHE_TIME"] ) $arParams["CACHE_TIME"] = 60*60*24*30;

if( !$id )
{
	ShowError(GetMessage("ELEMENT_NOT_FOUND"));
	@define("ERROR_404", "Y");
	if($arParams["SET_STATUS_404"]==="Y")
		CHTTP::SetStatus("404 Not Found");
	return;
}

if(isset($_GET["bid"]))
{
	$BASKET_ID = intval($_GET["bid"]);
}

/*
$arResult["MATHERIALS"] = intval( $_GET["m"] );
$arResult["COLORS"] = intval( $_GET["c"] );
*/
/* -------------- basket ---------------- */
if (CModule::IncludeModule("sale"))
{
	$FUSER_ID = CSaleBasket::GetBasketUserID(True);
	$dbBasket = CSaleBasket::GetList(
			array(),
			array(
					"FUSER_ID" => $FUSER_ID,
					"LID" => SITE_ID,
					"ORDER_ID" => "NULL",
					"PRODUCT_ID" => $id
					),
			false,
			false,
			array("ID")
			);
	while($arBasketItem = $dbBasket->Fetch())
	{
		$resBasketProps = CSaleBasket::GetPropsList(
			array(),
			array("BASKET_ID" => $arBasketItem["ID"]),
			false,
			false,
			array("CODE", "VALUE")
		);
		
		while($arBasketProps = $resBasketProps->GetNext())
		{
			if($arBasketProps["CODE"] == "MATHERIALS")
			{
				$arBasket[$arBasketItem["ID"]]["MATHERIALS"] = $arBasketProps["VALUE"];
			}
			if($arBasketProps["CODE"] == "COLORS")
			{
				$arBasket[$arBasketItem["ID"]]["COLORS"] = $arBasketProps["VALUE"];
			}
		}
		
		if(!empty($BASKET_ID))
		{
			if($BASKET_ID == $arBasketItem["ID"])
			{
				$arResult["MATHERIALS"] = $arBasket[$arBasketItem["ID"]]["MATHERIALS"];
				$arResult["COLORS"] = $arBasket[$arBasketItem["ID"]]["COLORS"];		
			}		
		}
		else
		{
			$arResult["MATHERIALS"] = $arBasket[$arBasketItem["ID"]]["MATHERIALS"];
			$arResult["COLORS"] = $arBasket[$arBasketItem["ID"]]["COLORS"];
		}
		$arResult["IN_BASKET"] = true;
	}
}

if(!empty($arBasket))
{
	$arResult["BASKET"] = json_encode($arBasket);
}

$arResult["FUSER_ID"] = $FUSER_ID;

/*************************************************************************
			Work with cache
*************************************************************************/
if( $this->StartResultCache( $arParams["CACHE_TIME"], array( $USER->GetGroups() )) )
{
	$res = CIBlockElement::GetByID( $id );
	$arItem = $res->GetNext();
	
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
	$discount_price = CCatalogProduct::CountPriceWithDiscount(
			$arPrice["PRICE"],
			$arPrice["CURRENCY"],
			$arDiscounts
		);
	if($discount_price > 0) $arPrice["DISCOUNT_PRICE"] = ceil($discount_price);
	if($arPrice["PRICE"]) $arPrice["PRICE"] = ceil($arPrice["PRICE"]);
	$arItem["PRICE"] = $arPrice;

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
	
	// ------------ производитель
	$mnfct = $arItem["PROPERTIES"]["MANUFACTURER"]["VALUE"];
	$rsMnfct = CIBlockElement::GetByID( $mnfct );
	if( $arMnfct = $rsMnfct->GetNext() )
	{
		$arItem["MANUFACTURER"] = $arMnfct["NAME"];
	}
	
	// ----------- упрощаем 
	
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
	
	/* ************* BUY ************* */
	$arItem["BUY"] = SITE_TEMPLATE_PATH . "/functions/MP_add2basket.php";
	
	$arResult["ITEM"] = $arItem;
	
/* =============== MATHERIALS && COLORS ============= */	

	$propFields = array();
	
	$propFields[] = array( 
			"NAME" => "COLORS", 
			"ID" => 8,
			"RU" => "Цвет" );
			
	if(empty($arItem["HARD"]))
	{
		$propFields[] = array( 
			"NAME" => "MATHERIALS", 
			"ID" => 9,
			"RU" => "Материал" );
	}
	
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
		
		/* ----------- если не установлен цвет и материал устанавливаем из производителя корневой уровень ------------ */			
		if( !count( $arItem["PROPERTIES"][ $field["NAME"] ]["VALUES"] ) )
		{
			if( !( $field["NAME"] == "MATHERIALS" && $arItem["HARD"] == "Да" ) )
			{
				if( $mnfct )
				{
					$arFilter = array(
						"IBLOCK_ID" => $field["ID"],
						"UF_MANUFACTURER" => $mnfct,
						"DEPTH_LEVEL" => 1
					);
					$rsPropSect = CIBlockSection::GetList(array(), $arFilter, false, array("ID"), false );
					if( $arRes = $rsPropSect->GetNext() )
					{
						$field["SECTIONS"][ $arRes["ID"] ] = $res[ $arRes["ID"] ];
					}
				}
			}
		}
		else
		{
			foreach( $arItem["PROPERTIES"][ $field["NAME"] ]["VALUES"] as $sect_id )
			{
				$field["SECTIONS"][ $sect_id ] = $res[ $sect_id ];
			}
		}

		// ---- собираем дочернии секции

		$arUsed = array();
		
		foreach( $field["SECTIONS"] as $PropSectId => $PropSect )
		{
			$arUsed[] = $PropSectId;
				
			if( count( $PropSect["CHILDS"] ) )
			{
				foreach( $PropSect["CHILDS"] as $child_id )
				{
					$arUsed[] = $child_id;
					$child = $res[ $child_id ];
					$field["SECTIONS"][ $child_id ] = $child;

					if( count( $child["CHILDS"] ) )
					{
						foreach( $child["CHILDS"] as $grandchild )
						{
							$arUsed[] = $grandchild;
							$field["SECTIONS"][ $grandchild ] = $res[ $grandchild ];
						}
					}
				}
			}
		}
	
// ---- получаем элементы 
		if( count( $field["SECTIONS"] ) )
		{
			$field["ELEMENTS"] = array();
			$count = 0;
			$arSort = array(
				"IBLOCK_SECTION_ID" => "ASC",
				"SORT" => "ASC"
			);
			$arFilter = array(
				"IBLOCK_ID" => $field["ID"],
				"SECTION_ID" => $arUsed,
			);
#echo "<pre>"; print_r( $arFilter ); echo "</pre></hr/>";			
			$arSelect = array(
				"ID",
				"IBLOCK_SECTION_ID",
				"NAME",
				"PREVIEW_PICTURE",
				"DETAIL_PICTURE",
			);
				
			$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);

			while($arElement = $rsElements->GetNext())
			{
				$field["SECTIONS"][ $arElement["IBLOCK_SECTION_ID"] ]["ELEMENTS"][] = $arElement["ID"];
				$field["ELEMENTS"][ $arElement["ID"] ] = $arElement;
				$count++;
			}
			$field["COUNT"] = $count;			
			$arResult["PROPS"][ $field["NAME"] ] = $field;
		}
	}
	
	if (\Bitrix\Main\Loader::includeModule('sale'))
	{
		if (!isset($_SESSION["VIEWED_ENABLE"]) && isset($_SESSION["VIEWED_PRODUCT"]) && $_SESSION["VIEWED_PRODUCT"] != $arResult["ITEM"]["ID"])
		{
			$_SESSION["VIEWED_ENABLE"] = "Y";
			$arFields = array(
				"PRODUCT_ID" => IntVal($_SESSION["VIEWED_PRODUCT"]),
				"MODULE" => "catalog",
				"LID" => SITE_ID,
			);
			CSaleViewedProduct::Add($arFields);
		}

		if (isset($_SESSION["VIEWED_ENABLE"]) && $_SESSION["VIEWED_ENABLE"] == "Y" && $_SESSION["VIEWED_PRODUCT"] != $arResult["ITEM"]["ID"])
		{
			$arFields = array(
				"PRODUCT_ID" => $arResult["ITEM"]["ID"],
				"MODULE" => "catalog",
				"LID" => SITE_ID,
				"IBLOCK_ID" => $arResult["IBLOCK_ID"]
			);
			CSaleViewedProduct::Add($arFields);
		}

		$_SESSION["VIEWED_PRODUCT"] = $arResult["ITEM"]["ID"];
	}
	
	$this->IncludeComponentTemplate();
}	
?>