<?php
function get_basket_items($FUSER_ID)
{
	CModule::IncludeModule('sale');

	$result = array(
		"BASKET_ITEMS"=>array(),
		"SUM" => 0
		);
	
	$dbBasketItems = CSaleBasket::GetList(
			array(
					"NAME" => "ASC",
					"ID" => "ASC"
				),
			array(
					"FUSER_ID" => $FUSER_ID,
					"LID" => SITE_ID,
					"ORDER_ID" => "NULL"
				),
			false,
			false,
			array("ID", "PRODUCT_ID", "QUANTITY", "PRICE")
		);
	while ($arItems = $dbBasketItems->Fetch())
	{
		CSaleBasket::UpdatePrice($arItems["ID"], 
								 $arItems["PRODUCT_ID"], 
								 $arItems["QUANTITY"]);
		$arItems = CSaleBasket::GetByID($arItems["ID"]);
		$db_res = CSaleBasket::GetPropsList(
				array(
						"SORT" => "ASC",
						"NAME" => "ASC"
					),
				array("BASKET_ID" => $arItems["ID"])
			);
		while ($prop = $db_res->Fetch())
		{
			$rsProp = CIBlockElement::GetByID( $prop["VALUE"] );
			if($arProp = $rsProp->GetNext())
			{
				$arImg = array();
				$arImg = CFile::ResizeImageGet( 
					$arProp["PREVIEW_PICTURE"], 
					array('width'=>75, 'height'=>75), 
					BX_RESIZE_IMAGE_PROPORTIONAL, 
					true
				);
				
				$arItems["PROPS"][ $prop["CODE"] ] = array(
					"ID" => $arProp["ID"],
					"NAME" => $arProp["NAME"],
					"PICTURE" => $arImg["src"],
				);
			}
		}
		
		$dbres = CIBlockElement::GetByID($arItems["PRODUCT_ID"]);
		if($obres = $dbres->GetNextElement())
		{	
			$arFields = $obres->GetFields();
			$arImg = CFile::ResizeImageGet( 
					 $arFields["PREVIEW_PICTURE"], 
					array('width'=>100, 'height'=>100), 
					BX_RESIZE_IMAGE_PROPORTIONAL, 
					true
				);
			$arItems["IMG"] = $arImg["src"];
		}
		
		$result["BASKET_ITEMS"][] = $arItems;
		$result["SUM"] += $arItems["PRICE"];
	}
	return $result;
}
?>