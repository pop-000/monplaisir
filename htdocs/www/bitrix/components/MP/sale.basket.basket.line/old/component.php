<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PATH_TO_BASKET"] = Trim($arParams["PATH_TO_BASKET"]);
if (strlen($arParams["PATH_TO_BASKET"]) <= 0)
	$arParams["PATH_TO_BASKET"] = "/personal/basket.php";

if(
	isset($_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID]) &&
	isset($_SESSION["SALE_BASKET_SUM"][SITE_ID]) &&
	isset($_SESSION["SALE_BASKET_DISCOUNT"][SITE_ID]) && 2 == 1
)
{
	$num_products = $_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID];
	$sum_products = $_SESSION["SALE_BASKET_SUM"][SITE_ID];
	$discount_products = $_SESSION["SALE_BASKET_DISCOUNT"][SITE_ID];
}
else
{
	if(!CModule::IncludeModule("sale"))
	{
		ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
		return;
	}
	$fUserID = CSaleBasket::GetBasketUserID(True);
	$fUserID = IntVal($fUserID);
	$num_products = 0;
	$sum = 0;
	$discount = 0;
	if ($fUserID > 0)
	{
		$dbRes = CSaleBasket::GetList(
			array(),
			array(
				"FUSER_ID" => $fUserID,
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL",
				"CAN_BUY" => "Y",
				"DELAY" => "N",
				"SUBSCRIBE" => "N",
			),
			false,
			false,
			array(
				"PRICE", "DISCOUNT_PRICE", "QUANTITY",
			)
		);
		while ($arItem = $dbRes->GetNext())
		{
			$num = $arItem["QUANTITY"];
			$num_products += $num;
			$sum = $arItem["PRICE"] * $num;
			$sum_products += $sum;
			$discount = $arItem["DISCOUNT_PRICE"] * $num;
			$discount_products += $discount;
		}
	}
	$_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID] = intval($num_products);
	$_SESSION["SALE_BASKET_SUM"][SITE_ID] = intval($sum_products);
	$_SESSION["SALE_BASKET_DISCOUNT"][SITE_ID] = intval($discount_products);
}


$arResult["NUM_PRODUCTS"] = $num_products;
$arResult["SUM"] = $sum_products;
$arResult["DISCOUNT"] = $discount_products;
$arResult["SUM_OLD"] = $discount_products + $sum_products;

$this->IncludeComponentTemplate();
?>