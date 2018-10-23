<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PATH_TO_BASKET"] = Trim($arParams["PATH_TO_BASKET"]);
if (strlen($arParams["PATH_TO_BASKET"]) <= 0)
	$arParams["PATH_TO_BASKET"] = "/personal/basket.php";
session_start();
$_SESSION["BASKET"] = array(
	"COUNT_PRODUCT" => $arResult["COUNT_PRODUCT"],
	"SUM" => $arResult["ALL_SUM"],
	"SUM_WITHOUT_DISCOUNT" => $arResult["SUM_WITHOUT_DISCOUNT"],
);

$arResult["NUM_PRODUCTS"] = $_SESSION["BASKET"]["COUNT_PRODUCT"];
$arResult["SUM"] = $_SESSION["BASKET"]["SUM"];
$arResult["SUM_WITHOUT_DISCOUNT"] = $_SESSION["BASKET"]["SUM_WITHOUT_DISCOUNT"];

$this->IncludeComponentTemplate();
?>