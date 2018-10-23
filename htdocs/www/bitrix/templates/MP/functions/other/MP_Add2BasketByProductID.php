<?php 
function MP_Add2BasketByProductID($PRODUCT_ID, $PRICE, $COLORS=null, $MATHERIALS=null)
{
    global $APPLICATION;

	$PRODUCT_ID = intval($PRODUCT_ID);
    if (0 >= $PRODUCT_ID)
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_EMPTY_PRODUCT_ID'), "EMPTY_PRODUCT_ID");
        return false;
    }

    $QUANTITY = 1;

    if (!CModule::IncludeModule("sale"))
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_NO_SALE_MODULE'), "NO_SALE_MODULE");
        return false;
    }

    if (CModule::IncludeModule("statistic") && array_key_exists('SESS_SEARCHER_ID', $_SESSION) && 0 < intval($_SESSION["SESS_SEARCHER_ID"]))
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_SESS_SEARCHER'), "SESS_SEARCHER");
        return false;
    }

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
    if (!($arCatalogProduct = $rsProducts->Fetch()))
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_NO_PRODUCT'), "NO_PRODUCT");
        return false;
    }

    $dblQuantity = doubleval($arCatalogProduct["QUANTITY"]);
    $intQuantity = intval($arCatalogProduct["QUANTITY"]);
    $boolQuantity = ('Y' != $arCatalogProduct["CAN_BUY_ZERO"] && 'Y' == $arCatalogProduct["QUANTITY_TRACE"]);
    if ($boolQuantity && 0 >= $dblQuantity)
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_PRODUCT_RUN_OUT'), "PRODUCT_RUN_OUT");
        return false;
    }

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
	
    if (!empty($COLORS))
    {
        $arProps[] = array(
                "NAME" => "Цвет",
                "CODE" => "COLORS",
                "VALUE" => $COLORS,
                "SORT" => 50
        );
    } 
	
	if (!empty($MATHERIALS))
    {
        $arProps[] = array(
                "NAME" => "Материал",
                "CODE" => "MATHERIALS",
                "VALUE" => $MATHERIALS,
                "SORT" => 50
        );
    }

    $arFields = array(
        "PRODUCT_ID" => $PRODUCT_ID,
        "PRODUCT_PRICE_ID" => '',
        "PRICE" => $PRICE,
        "CURRENCY" => $arCallbackPrice["CURRENCY"],
        "WEIGHT" => $arCatalogProduct["WEIGHT"],
        "DIMENSIONS" => serialize(array(
                                    "WIDTH" => $arCatalogProduct["WIDTH"],
                                    "HEIGHT" => $arCatalogProduct["HEIGHT"],
                                    "LENGTH" => $arCatalogProduct["LENGTH"]
                                    )
        ),
        "QUANTITY" => ($boolQuantity && $dblQuantity < $QUANTITY ? $dblQuantity : $QUANTITY),
        "LID" => SITE_ID,
        "DELAY" => "N",
        "CAN_BUY" => "Y",
        "NAME" => $arProduct["~NAME"],
        "MODULE" => "catalog",
        "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
        "NOTES" => $arCallbackPrice["NOTES"],
        "DETAIL_PAGE_URL" => $arProduct["~DETAIL_PAGE_URL"],
        "CATALOG_XML_ID" => $strIBlockXmlID,
        "PRODUCT_XML_ID" => $arProduct["XML_ID"],
        "VAT_RATE" => $arCallbackPrice['VAT_RATE'],
        "PROPS" => $arProps,
        "TYPE" => ($arCatalogProduct["TYPE"] == CCatalogProduct::TYPE_SET) ? CCatalogProductSet::TYPE_SET : NULL
    );

    $addres = CSaleBasket::Add($arFields);

    if ($addres)
    {
        if (CModule::IncludeModule("statistic"))
            CStatistic::Set_Event("sale2basket", "catalog", $arFields["DETAIL_PAGE_URL"]);
    }

    return $addres;
}