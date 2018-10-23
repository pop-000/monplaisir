<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("catalog"))
{
	ShowError(GetMessage("CATALOG_MODULE_NOT_INSTALL"));
	return;
}
$arResult["ALL_SUM"] = $arResult["SUM_DISCOUNT"] = 0;
$FUSER_ID = CSaleBasket::GetBasketUserID();
$arResult["FUSER_ID"] = $FUSER_ID;
// список элементов
$dbBasketItems = CSaleBasket::GetList(
		array(),
		array(
			"FUSER_ID" => $FUSER_ID,
			"LID" => SITE_ID,
			"ORDER_ID" => "NULL",
			#"ID" => $id,
		),
		false,
		false,
		array("ID", "PRODUCT_ID", "QUANTITY", "PRICE", "NAME")
	);
// поля элемента	
while($arItem = $dbBasketItems->Fetch())
{	
	// доп. свойства        
	$dbres = CIBlockElement::GetByID($arItem["PRODUCT_ID"]);
	if($obres = $dbres->GetNextElement())
	{	
		$arFields = $obres->GetFields();
  
		$arItem["NAME"] = $arFields["NAME"];
		$arItem["PREVIEW_PICTURE"] = $arFields["PREVIEW_PICTURE"];
		$arItem["DETAIL_PAGE_URL"] = $arFields["DETAIL_PAGE_URL"];
		$arItem["IBLOCK_SECTION_ID"] = $arFields["IBLOCK_SECTION_ID"];
		
		$properties = $obres->getProperties();
		$arItem["MANUFACTURER"] = $properties["MANUFACTURER"]["VALUE"];
	}
	
// цены
	$arPrice = array();

	$dbPrice = CPrice::GetList(
		array("PRICE" => "ASC"),
		array("PRODUCT_ID" => $arItem["PRODUCT_ID"], "CAN_ACCESS" => "Y", "CAN_BUY" => "Y", "CATALOG_GROUP_ID" => array(3)),
		false,
		false,
		array()
	);
	$arPrice = $dbPrice->Fetch();

	$price = $arItem["PRICE"];
	/*
echo $price;
echo "<hr>";
echo "<pre>"; print_r($arPrice); echo "</pre>";	
*/

	$arItem["PRICE"] = array("PRICE"=>0, "OLD_PRICE"=>0, "DISCOUNT"=>0);
	
	if($arPrice["PRICE"] > $price) 
	{	
		$arItem["PRICE"]["PRICE"] = $price;
		$arItem["PRICE"]["OLD_PRICE"] = $arPrice["PRICE"];
		$arItem["PRICE"]["DISCOUNT"] = ceil($arPrice["PRICE"] - $price);
	}
	else
	{
		$arItem["PRICE"]["PRICE"] = $price;
	}

// материал и цвет		

	$resBasketProps = CSaleBasket::GetPropsList(
        array(),
        array("BASKET_ID" => $arItem["ID"])
    );
	
	while($arBasketProps = $resBasketProps->GetNext())
	{
	    $arMathCol["ID"] = $arBasketProps["VALUE"];
	    $arMathCol["CODE"] = $arBasketProps["CODE"];
	    if($arMathCol["CODE"] == "MATHERIALS")
	    {
		   $arMathCol["IBLOCK_ID"] = 9;
		   $arMathCol["ADD_PRICE_IBLOCK_ID"] = 12;
	    }
	    if($arMathCol["CODE"] == "COLORS")
	    {
		   $arMathCol["IBLOCK_ID"] = 8;
		   $arMathCol["ADD_PRICE_IBLOCK_ID"] = 13;
	    }
	   
	    $dbres = CIBlockElement::GetByID($arMathCol["ID"]);
	    if($obres = $dbres->GetNextElement())
		{	
			$arFields = $obres->GetFields();
			$arMathCol["MATH_IBLOCK_SECTION_ID"] = $arFields["IBLOCK_SECTION_ID"];
			$arMathCol["PREVIEW_PICTURE"] = $arFields["PREVIEW_PICTURE"];
			$arMathCol["NAME"] = $arFields["NAME"];
/*  наценки  */			
			$arPriceAddFilter = array(
				"IBLOCK_ID"=> $arMathCol["ADD_PRICE_IBLOCK_ID"], 
				"PROPERTY_mnfct"=> $arItem["MANUFACTURER"],
				"PROPERTY_goods" => $arItem["IBLOCK_SECTION_ID"]
				);		
			if($arMathCol["CODE"] == "MATHERIALS")
			{
				$arPriceAddFilter["PROPERTY_matherials"] = $arMathCol["MATH_IBLOCK_SECTION_ID"];
			}
			if($arMathCol["CODE"] == "COLORS")
			{
				$arPriceAddFilter["PROPERTY_colors"] = $arMathCol["MATH_IBLOCK_SECTION_ID"];
			}
		
			$dbresPriceAdd = CIBlockElement::GetList( 
				array(), 
				$arPriceAddFilter,
				false, false,
				array("PROPERTY_add_price", "PROPERTY_add_price_percent")
				);
			$arPriceAdd = $dbresPriceAdd->Fetch();
			
			$arMathCol["ADD_PRICE_RUB"] = $arMathCol["ADD_PRICE_PERCENT"] = $arItem["PRICE"]["ADD"] = 0;
			
			$arMathCol["ADD_PRICE_RUB"] = $arPriceAdd["PROPERTY_ADD_PRICE_VALUE"];
			$arMathCol["ADD_PRICE_PERCENT"] = $arPriceAdd["PROPERTY_ADD_PRICE_PERCENT_VALUE"];
			
	/* цены */
	
			if($arMathCol["ADD_PRICE_RUB"] > 0)
			{
				$arItem["PRICE"]["ADD"] += $arMathCol["ADD_PRICE_RUB"];
			}
			/*
			if($arMathCol["ADD_PRICE_PERCENT"] > 0)
			{
				$arItem["PRICE"]["ADD"] += $arItem["PRICE"] / 100 * $arMathCol["ADD_PRICE_PERCENT"];
			}
			*/
			if($arItem["PRICE"]["ADD"] > 0)
			{
				$arItem["PRICE"]["ADD"] = ceil($arItem["PRICE"]["ADD"]);
			}

			$arItem["PRICE"]["PRICE"] = ceil($arItem["PRICE"]["PRICE"]);
			$arItem["SUM"] = $arItem["PRICE"]["PRICE"] * $arItem["QUANTITY"];
			if($arItem["PRICE"]["DISCOUNT"] > 0)
			{
				$arItem["SUM_DISCOUNT"] = $arItem["PRICE"]["DISCOUNT"] * $arItem["QUANTITY"];
				$arItem["SUM_DISCOUNT"] = ceil($arItem["SUM_DISCOUNT"]);
			}
		}

		/*--------------*/
		
	   $arItem["PROPS"][$arMathCol["CODE"]] = $arMathCol;
   
	}
		
	$arResult["ELEMENTS"][] = $arItem;
	
	/* суммы */
	
	$arResult["ALL_SUM"] += $arItem["SUM"];
	$arResult["SUM_DISCOUNT"] += $arItem["SUM_DISCOUNT"];
	$arResult["COUNT_PRODUCT"]++;
}
if($arResult["SUM_DISCOUNT"] > 0)
{
	$arResult["SUM_DISCOUNT"] = ceil($arResult["SUM_DISCOUNT"]);
	$arResult["SUM_WITHOUT_DISCOUNT"] = ceil($arResult["ALL_SUM"] + $arResult["SUM_DISCOUNT"]);
}
else
{
	$arResult["SUM_WITHOUT_DISCOUNT"] = 0;
}
$arResult["ALL_SUM"] = ceil($arResult["ALL_SUM"]);
/*
session_start();
$_SESSION["BASKET"] = array(
	"COUNT_PRODUCT" => $arResult["COUNT_PRODUCT"],
	"SUM" => $arResult["ALL_SUM"],
	"SUM_WITHOUT_DISCOUNT" => $arResult["SUM_WITHOUT_DISCOUNT"],
);
*/
$this->IncludeComponentTemplate();
?>