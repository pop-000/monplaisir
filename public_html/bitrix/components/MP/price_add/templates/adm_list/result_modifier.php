<? #echo "<pre>"; print_r($arResult); echo "</pre>"; exit();
foreach($arResult["ITEMS"] as $item )
{
	$res[ $item["ID"] ] = array(
		"ID" => $item["ID"],
		"NAME" => $item["NAME"],
		"mnfct" => $arResult["MANUFACTURERS"][ $item["mnfct"] ],
		"rub" => $item["RUB"],
		"percent" => $item["PERCENT"],
		);
	
	$goods = array();
	foreach( $item["goods"] as $goods_id )
	{
		$goods[] = $arResult["GOODS"][ $goods_id ]["NAME"] . " [" . $goods_id . "]";
	}
	$res[ $item["ID"] ]["goods"] = implode(", ", $goods );
	
	$matherials = array();
	foreach( $item["matherials"] as $math_id )
	{
		$matherials[] = $arResult["MATHERIALS"][ $math_id ]["NAME"] . " [" . $math_id . "]";
	}
	$res[ $item["ID"] ]["matherials"] = implode(", ", $matherials );
	
	$colors = array();
	foreach( $item["colors"] as $color_id )
	{
		$colors[] = $arResult["COLORS"][ $color_id ]["NAME"] . " [" . $color_id . "]";
	}
	$res[ $item["ID"] ]["colors"] = implode(", ", $colors );
	
}
$arResult = $res;
?>