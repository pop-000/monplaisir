<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php"); 
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

$FUSER_ID = intval($_POST["FUSER_ID"]);
$PROD_ID = intval($_POST["ID"]);
$COLORS = intval($_POST["COLORS"]);
$MATHERIALS = intval($_POST["MATHERIALS"]);
$PRICE = intval(str_replace(" ", "", $_POST["PRICE"]));

if( 
!empty($PROD_ID) &&
!empty($COLORS) && 
!empty($PRICE) 
)
{
	$BASKET_ID = MP_add2basket($FUSER_ID, $PROD_ID, $COLORS, $MATHERIALS, $PRICE);
	if(!empty($BASKET_ID))
	{
		echo $BASKET_ID;
	}
}

function MP_add2basket($FUSER_ID, $PRODUCT_ID, $COLORS, $MATHERIALS=null, $PRICE)
{
    global $APPLICATION;
	global $DB;

    $rsProducts = CCatalogProduct::GetList(
        array(),
        array('ID' => $PRODUCT_ID),
        false,
        false,
        array(
            'ID',
            'CAN_BUY_ZERO',
            'QUANTITY_TRACE',
            'QUANTITY',
            'WEIGHT',
            'WIDTH',
            'HEIGHT',
            'LENGTH',
            'TYPE'
        )
    );

    $rsItem = CIBlockElement::GetList(
        array(),
        array(
            "ID" => $PRODUCT_ID,
            "ACTIVE" => "Y",
            "ACTIVE_DATE" => "Y",
            "CHECK_PERMISSIONS" => "Y",
            "MIN_PERMISSION" => "R",
        ),
        false,
        false,
        array(
            "ID",
            "IBLOCK_ID",
            "XML_ID",
            "NAME",
            "DETAIL_PAGE_URL",
        )
    );
	
	$arProduct = $rsItem->Fetch();
	
    $arProps[] = array(
                "NAME" => "Цвет",
                "CODE" => "COLORS",
                "VALUE" => $COLORS,
                "SORT" => 50
			);
	
	$arProps[] = array(
                "NAME" => "Материал",
                "CODE" => "MATHERIALS",
                "VALUE" => $MATHERIALS,
                "SORT" => 50
			);
			
	// проверка, добавлен ли уже этот товар в такой комплектации в корзину
	
	$arFilter = array(
		"PRODUCT_ID" => $PRODUCT_ID,
		"LID" => 's1',
		"FUSER_ID" => $FUSER_ID,
		"ORDER_ID" => null
	);

	$db_res = CSaleBasket::GetList(
		array(),
		$arFilter,
		false,
		false,
		array("ID")
	);
	
	while($res = $db_res->Fetch())
	{
		$dbProp = CSaleBasket::GetPropsList(
			array(),
			array("BASKET_ID" => $res["ID"]),
			false,
			false,
			array('NAME', 'VALUE', 'CODE')
		);
		
		unset($prop_colors, $prop_matherials);
		
		while($arProp = $dbProp->Fetch())
		{
			if($arProp["CODE"] == "COLORS")
			{
				$prop_colors = $arProp["VALUE"];
			}
			if($arProp["CODE"] == "MATHERIALS")
			{
				$prop_matherials = $arProp["VALUE"];
			}
		}
/*		
$data = "M: " . $MATHERIALS . "; C: " . $COLORS . "; pc: " . $prop_colors . " pm: " . $prop_matherials;
file_put_contents("test.txt", $data);
*/		
		if($prop_colors == $COLORS && $prop_matherials == $MATHERIALS)
			return false;
	}
	
	/* add */
	
    $arFields = array(
        "PRODUCT_ID" => $PRODUCT_ID,
        "PRODUCT_PRICE_ID" => 3,
        "PRICE" => $PRICE,
        "CURRENCY" => $arCallbackPrice["CURRENCY"],
        "WEIGHT" => $arCatalogProduct["WEIGHT"],
        "DIMENSIONS" => serialize(array(
                                    "WIDTH" => $arCatalogProduct["WIDTH"],
                                    "HEIGHT" => $arCatalogProduct["HEIGHT"],
                                    "LENGTH" => $arCatalogProduct["LENGTH"]
                                    )
        ),
        "QUANTITY" => 1,
        "LID" => 's1',
		"FUSER_ID" => $FUSER_ID,
        "DELAY" => "N",
        "CAN_BUY" => "Y",
        "NAME" => $arProduct["NAME"],
        "MODULE" => "catalog",
        "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
        "NOTES" => $arCallbackPrice["NOTES"],
        "DETAIL_PAGE_URL" => $arProduct["DETAIL_PAGE_URL"],
        "CATALOG_XML_ID" => $strIBlockXmlID,
        "PRODUCT_XML_ID" => $arProduct["XML_ID"],
        "VAT_RATE" => $arCallbackPrice['VAT_RATE'],
        "PROPS" => $arProps,
        "TYPE" => ($arCatalogProduct["TYPE"] == CCatalogProduct::TYPE_SET) ? CCatalogProductSet::TYPE_SET : NULL
    );
/*
$data = serialize($arFields);
file_put_contents("test.txt", $data);
*/	
	$arInsert = $DB->PrepareInsert("b_sale_basket", $arFields);

	$strSql = "INSERT INTO b_sale_basket(".$arInsert[0].", DATE_INSERT, DATE_UPDATE) VALUES(".$arInsert[1].", ".$DB->GetNowFunction().", ".$DB->GetNowFunction().")";
	$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

	$ID = intval($DB->LastID());

	$_SESSION["SALE_BASKET_NUM_PRODUCTS"]['s1']++;

	foreach ($arProps as $prop)
	{
		$arInsert = $DB->PrepareInsert("b_sale_basket_props", $prop);

		$strSql = "INSERT INTO b_sale_basket_props(BASKET_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

    return $ID;
}