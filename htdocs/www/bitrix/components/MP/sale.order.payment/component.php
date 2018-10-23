<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION;

$APPLICATION->RestartBuffer();

$ORDER_ID = intval($_REQUEST["ORDER_ID"]);
$arOrder = CSaleOrder::GetByID($ORDER_ID);
$arPaySysAction = CSalePaySystemAction::GetByID(10);

echo "<pre>"; print_r($arPaySysAction); echo "</pre>";

CSalePaySystemAction::InitParamArrays($arOrder, 10, $arPaySysAction["PARAMS"]);

include('/bitrix/modules/rbs.payment/payment/payment.php');

if(strlen($arPaySysAction["ENCODING"]) > 0)
{
	define("BX_SALE_ENCODING", $arPaySysAction["ENCODING"]);
	AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
	function ChangeEncoding($content)
	{
		global $APPLICATION;
		header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
		$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
		$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
	}
}
?>