<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("sale"); 
$arBasket = CSaleBasket::GetList(
		array( "NAME" => "ASC" ),
		array(
                "FUSER_ID" => $_POST["FUSER_ID"],
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL"
                ),
        false,
        false,
        array()
	);
	if( count( $arBasket ))
	{
		while( $item = $arBasket->GetNext() )
		{
			$sum += $item["PRICE"] * $item["QUANTITY"];
			$count ++;
		}
	}
echo $sum . "," . $count;
?>