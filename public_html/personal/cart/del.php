<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

CModule::IncludeModule("sale");
if(isset($_POST))
{
	foreach($_POST as $key=>$val)
	{
		if(strpos($key, "_del_"))
		{
			CSaleBasket::Delete($val);
		}
	}
}
LocalRedirect('/personal/cart/');
?>
