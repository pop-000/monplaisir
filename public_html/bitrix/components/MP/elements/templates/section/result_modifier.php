<?php
#echo "<pre>"; print_r( $arResult ); echo "</pre><hr />"; exit();
$arProps = array( "MATHERIALS", "COLORS" );
foreach( $arProps as $prop )
{
	foreach( $arResult["PROP_SETS"][$prop] as $set_key => $set_val )
	{
		$res = array();
		foreach( $set_val["ELEMENTS"] as $elmnt )
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$elmnt["PREVIEW_PICTURE"], 
				array('width'=>$arResult["COLORS_IMG_SIZE"], 'height'=>$arResult["COLORS_IMG_SIZE"]), 
				BX_RESIZE_IMAGE_EXACT, 
				true);
			$res[] = array(
				"NAME" => $elmnt["NAME"],
				"SRC" => $arImg["src"],
			);	
		}
		$arResult["PROP_SETS"][ $prop ][ $set_key ]["ELEMENTS"] = $res;
	}
}
foreach( $arResult["ITEMS"] as $id_item => $item )
{
	$cut = strpos( $item["NAME"], "," );
	if( $cut )
	{
		$arResult["ITEMS"][ $id_item ]["NAME"] = substr( $item["NAME"], 0, $cut );
	}
	foreach( $arProps as $field )
	{
		$set = $item[ "SETS" ][ $field ];
		$arResult["ITEMS"][ $id_item ][ "SETS" ][ $field ] = $arResult["PROP_SETS"][ $field ][ $set ];
		unset( $set );
	}
}
unset( $arResult["PROP_SETS"] );	
?>