<?php 
//    CSaleBasket::Add
//    /bitrix/modules/sale/mysql/basket.php:400

    function Add($arFields)
    {
        global $DB;

        if (isset($arFields["ID"]))
            unset($arFields["ID"]);

        CSaleBasket::Init();
        if (!CSaleBasket::CheckFields("ADD", $arFields))
            return false;

        foreach(GetModuleEvents("sale", "OnBeforeBasketAdd", true) as $arEvent)
            if (ExecuteModuleEventEx($arEvent, Array(&$arFields))===false)
                return false;

        $bFound = false;
        $bEqAr = false;

        $boolProps = (array_key_exists('PROPS', $arFields) && !empty($arFields["PROPS"]) && is_array($arFields["PROPS"]));

        // check if this item is already in the basket
        $arDuplicateFilter = array(
            "FUSER_ID" => $arFields["FUSER_ID"],
            "PRODUCT_ID" => $arFields["PRODUCT_ID"],
            "LID" => $arFields["LID"],
            "ORDER_ID" => "NULL"
        );

        if (!(isset($arFields["TYPE"]) && $arFields["TYPE"] == CSaleBasket::TYPE_SET))
        {
            if (isset($arFields["SET_PARENT_ID"]))
                $arDuplicateFilter["SET_PARENT_ID"] = $arFields["SET_PARENT_ID"];
            else
                $arDuplicateFilter["SET_PARENT_ID"] = "NULL";
        }

        $db_res = CSaleBasket::GetList(
            array(),
            $arDuplicateFilter,
            false,
            false,
            array("ID", "QUANTITY")
        );
        while($res = $db_res->Fetch())
        {
            if(!$bEqAr)
            {
                $arPropsCur = array();
                $arPropsOld = array();

                if ($boolProps)
                {
                    foreach($arFields["PROPS"] as &$arProp)
                    {
                        if (array_key_exists('VALUE', $arProp)&& '' != $arProp["VALUE"])
                        {
                            $propID = '';
                            if (array_key_exists('CODE', $arProp) && '' != $arProp["CODE"])
                            {
                                $propID = $arProp["CODE"];
                            }
                            elseif (array_key_exists('NAME', $arProp) && '' != $arProp["NAME"])
                            {
                                $propID = $arProp["NAME"];
                            }
                            if ('' == $propID)
                                continue;
                            $arPropsCur[$propID] = $arProp["VALUE"];
                        }
                    }
                    if (isset($arProp))
                        unset($arProp);
                }

                $dbProp = CSaleBasket::GetPropsList(
                    array(),
                    array("BASKET_ID" => $res["ID"]),
                    false,
                    false,
                    array('NAME', 'VALUE', 'CODE')
                );
                while ($arProp = $dbProp->Fetch())
                {
                    if ('' != $arProp["VALUE"])
                    {
                        $propID = '';
                        if ('' != $arProp["CODE"])
                        {
                            $propID = $arProp["CODE"];
                        }
                        elseif ('' != $arProp["NAME"])
                        {
                            $propID = $arProp["NAME"];
                        }
                        if ('' == $propID)
                            continue;
                        $arPropsOld[$propID] = $arProp["VALUE"];
                    }
                }

                $bEqAr = false;
                if (count($arPropsCur) == count($arPropsOld))
                {
                    $bEqAr = true;
                    foreach($arPropsCur as $key => $val)
                    {
                        if (!array_key_exists($key, $arPropsOld) || $arPropsOld[$key] != $val)
                        {
                            $bEqAr = false;
                            break;
                        }
                    }
                }

                if ($bEqAr)
                {
                    $ID = $res["ID"];
                    $arFields["QUANTITY"] += $res["QUANTITY"];
                    CSaleBasket::Update($ID, $arFields);
                    $bFound = true;
                    continue;
                }
            }
        }

        if (!$bFound)
        {
            $arInsert = $DB->PrepareInsert("b_sale_basket", $arFields);

            $strSql = "INSERT INTO b_sale_basket(".$arInsert[0].", DATE_INSERT, DATE_UPDATE) VALUES(".$arInsert[1].", ".$DB->GetNowFunction().", ".$DB->GetNowFunction().")";
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

            $ID = intval($DB->LastID());

            $boolOrder = false;
            if (array_key_exists('ORDER_ID', $arFields))
            {
                $boolOrder = (0 < intval($arFields['ORDER_ID']));
            }

            if (!$boolOrder && !CSaleBasketHelper::isSetItem($arFields))
            {
                $siteID = (isset($arFields["LID"])) ? $arFields["LID"] : SITE_ID;
                $_SESSION["SALE_BASKET_NUM_PRODUCTS"][$siteID]++;
            }

            if ($boolProps)
            {
                foreach ($arFields["PROPS"] as &$prop)
                {
                    if ('' != $prop["NAME"])
                    {
                        $arInsert = $DB->PrepareInsert("b_sale_basket_props", $prop);

                        $strSql = "INSERT INTO b_sale_basket_props(BASKET_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
                        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
                    }
                }
                if (isset($prop))
                    unset($prop);
            }

            // if item is set parent
            if (isset($arFields["TYPE"]) && $arFields["TYPE"] == CSaleBasket::TYPE_SET)
            {
                CSaleBasket::Update($ID, array("SET_PARENT_ID" => $ID));

                if (!isset($arFields["MANUAL_SET_ITEMS_INSERTION"])) // set items will be added separately (from admin form data)
                {
                    /** @var $productProvider IBXSaleProductProvider */
                    if ($productProvider = CSaleBasket::GetProductProvider($arFields))
                    {
                        if (method_exists($productProvider, "GetSetItems"))
                        {
                            $arSets = $productProvider::GetSetItems($arFields["PRODUCT_ID"], CSaleBasket::TYPE_SET);

                            if (is_array($arSets))
                            {
                                foreach ($arSets as $arSetData)
                                {
                                    foreach ($arSetData["ITEMS"] as $setItem)
                                    {
                                        $setItem["SET_PARENT_ID"] = $ID;
                                        $setItem["LID"] = $arFields["LID"];
                                        $setItem["QUANTITY"] = $setItem["QUANTITY"] * $arFields["QUANTITY"];

                                        CSaleBasket::Add($setItem);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($boolOrder)
            {
                CSaleOrderChange::AddRecord(
                    $arFields["ORDER_ID"],
                    "BASKET_ADDED",
                    array(
                        "PRODUCT_ID" => $arFields["PRODUCT_ID"],
                        "NAME" => $arFields["NAME"],
                        "QUANTITY" => $arFields["QUANTITY"]
                    )
                );
            }
        }

        foreach(GetModuleEvents("sale", "OnBasketAdd", true) as $arEvent)
            ExecuteModuleEventEx($arEvent, Array($ID, $arFields));

        return $ID;
    }