<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><?$APPLICATION->IncludeComponent("MP:sale.basket.basket", "main", array(
	"COLUMNS_LIST" => array(
		0 => "NAME",
		1 => "DISCOUNT",
		2 => "WEIGHT",
		3 => "QUANTITY",
		4 => "PROPS",
		5 => "DELETE",
		6 => "DELAY",
		7 => "TYPE",
		8 => "PRICE",
		9 => "PROPERTY_MANUFACTURER",
		10 => "PROPERTY_MATHERIALS",
		11 => "PROPERTY_COLORS",
	),
	"PATH_TO_ORDER" => "/personal/order/make/",
	"HIDE_COUPON" => "Y",
	"PRICE_VAT_SHOW_VALUE" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"USE_PREPAYMENT" => "N",
	"QUANTITY_FLOAT" => "N",
	"SET_TITLE" => "Y"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>