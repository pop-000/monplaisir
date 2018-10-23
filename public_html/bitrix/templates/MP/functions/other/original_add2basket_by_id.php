<?php 
//    Add2BasketByProductID
//    /bitrix/modules/catalog/include.php:1336

function Add2BasketByProductID($PRODUCT_ID, $QUANTITY = 1, $arRewriteFields = array(), $arProductParams = false)
{
    global $APPLICATION;

    /* for old use */
    if (false === $arProductParams)
    {
        $arProductParams = $arRewriteFields;
        $arRewriteFields = array();
    }

    $boolRewrite = (!empty($arRewriteFields) && is_array($arRewriteFields));

    if ($boolRewrite && array_key_exists('SUBSCRIBE', $arRewriteFields) && 'Y' == $arRewriteFields['SUBSCRIBE'])
    {
        return SubscribeProduct($PRODUCT_ID, $arRewriteFields, $arProductParams);
    }

    $PRODUCT_ID = intval($PRODUCT_ID);
    if (0 >= $PRODUCT_ID)
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_EMPTY_PRODUCT_ID'), "EMPTY_PRODUCT_ID");
        return false;
    }

    $QUANTITY = doubleval($QUANTITY);
    if ($QUANTITY <= 0)
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

    $rsItems = CIBlockElement::GetList(
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
    if (!($arProduct = $rsItems->GetNext()))
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_ERR_NO_IBLOCK_ELEMENT'), "NO_IBLOCK_ELEMENT");
        return false;
    }

    $strCallbackFunc = "";
    $strProductProviderClass = "CCatalogProductProvider";

    if ($boolRewrite)
    {
        if (array_key_exists('CALLBACK_FUNC', $arRewriteFields))
            $strCallbackFunc = $arRewriteFields['CALLBACK_FUNC'];
        if (array_key_exists('PRODUCT_PROVIDER_CLASS', $arRewriteFields))
            $strProductProviderClass = $arRewriteFields['PRODUCT_PROVIDER_CLASS'];
    }

    $arCallbackPrice = CSaleBasket::ReReadPrice($strCallbackFunc, "catalog", $PRODUCT_ID, $QUANTITY, "N", $strProductProviderClass);
    if (!is_array($arCallbackPrice) || empty($arCallbackPrice))
    {
        $APPLICATION->ThrowException(GetMessage('CATALOG_PRODUCT_PRICE_NOT_FOUND'), "NO_PRODUCT_PRICE");
        return false;
    }

    $arProps = array();

    $strIBlockXmlID = strval(CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], 'XML_ID'));
    if ('' != $strIBlockXmlID)
    {
        $arProps[] = array(
            "NAME" => "Catalog XML_ID",
            "CODE" => "CATALOG.XML_ID",
            "VALUE" => $strIBlockXmlID
        );
    }

    $arProps[] = array(
        "NAME" => "Product XML_ID",
        "CODE" => "PRODUCT.XML_ID",
        "VALUE" => $arProduct["XML_ID"]
    );

    // add sku props
    $arParentSku = CCatalogSku::GetProductInfo($PRODUCT_ID);
    if ($arParentSku && count($arParentSku) > 0)
    {
        $arProductSkuProps = array();
        $arPropsSku = array();

        $dbOfferProperties = CIBlock::GetProperties($arProduct["IBLOCK_ID"], array(), array("!XML_ID" => "CML2_LINK"));
        while($arOfferProperties = $dbOfferProperties->Fetch())
        {
            if (('L' == $arOfferProperties["PROPERTY_TYPE"] || 'E' == $arOfferProperties["PROPERTY_TYPE"] || ('S' == $arOfferProperties["PROPERTY_TYPE"] && $arOfferProperties['USER_TYPE'] == 'directory')))
            {
                if (!empty($arProductParams) && is_array($arProductParams))
                {
                    foreach ($arProductParams as $arOneProductParams)
                    {
                        if ($arOfferProperties["CODE"] == $arOneProductParams["CODE"])
                            continue;
                    }
                }

                $arPropsSku[] = $arOfferProperties["CODE"];
            }
        }

        $arProductSkuProps = CIBlockPriceTools::GetOfferProperties(
            $PRODUCT_ID,
            $arParentSku["IBLOCK_ID"],
            $arPropsSku
        );

        // do not add existing props
        foreach ($arProductSkuProps as $productSkuProp)
        {
            $bSkip = false;
            foreach ($arProductParams as $existingSkuProp)
            {
                if ($existingSkuProp["CODE"] == $productSkuProp["CODE"])
                {
                    $bSkip = true;
                    break;
                }
            }

            if (!$bSkip)
                $arProductParams[] = $productSkuProp;
        }
    }

    if (!empty($arProductParams) && is_array($arProductParams))
    {
        foreach ($arProductParams as &$arOneProductParams)
        {
            $arProps[] = array(
                "NAME" => $arOneProductParams["NAME"],
                "CODE" => $arOneProductParams["CODE"],
                "VALUE" => $arOneProductParams["VALUE"],
                "SORT" => $arOneProductParams["SORT"]
            );
        }
        if (isset($arOneProductParams))
            unset($arOneProductParams);
    }

    $arFields = array(
        "PRODUCT_ID" => $PRODUCT_ID,
        "PRODUCT_PRICE_ID" => $arCallbackPrice["PRODUCT_PRICE_ID"],
        "PRICE" => $arCallbackPrice["PRICE"],
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

    if ($boolRewrite)
    {
        $arFields = array_merge($arFields, $arRewriteFields);
    }

    $addres = CSaleBasket::Add($arFields);
    if ($addres)
    {
        if (CModule::IncludeModule("statistic"))
            CStatistic::Set_Event("sale2basket", "catalog", $arFields["DETAIL_PAGE_URL"]);
    }

    return $addres;
}