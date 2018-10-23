<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("sale"); 

if(!empty($_POST["ID_BASKET"]) && !empty($_POST["QUANTITY"]))
{
	CSaleBasket::Update(
		$_POST["ID_BASKET"],
		array("QUANTITY" => $_POST["QUANTITY"])
	);
}
?>