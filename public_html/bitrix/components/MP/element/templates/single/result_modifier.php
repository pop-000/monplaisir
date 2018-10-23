<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<? #echo "<pre>"; print_r($arResult); echo "</pre>"; ?>
<?php
$photo_main_width = 300;
$photo_main_height = 300;
$photo_small_width = 100;
$photo_small_height = 100;
$color_size = 100;

// ========= наценки с условием
// ------ материал
$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>12), false, false, Array("ID", "NAME"));
while($ob = $res->GetNextElement())
{
	 $arFields = $ob->GetFields();
	 
	 $props = CIBlockElement::GetProperty( 12, $arFields["ID"]);
	 $fields = array();
	 while ($prop = $props->GetNext())
    {
		if( $prop["CODE"] == "goods" )
		{
			$fields[ $prop["CODE"] ][] = $prop["VALUE"];

			$res_childs = GetIBlockSectionList ( 2, $prop["VALUE"], false, false, false );
			while( $child = $res_childs-> GetNext() )
			{
				$fields[ $prop["CODE"] ][] = $child["ID"];
			}
		}
		else
		{
			$fields[ $prop["CODE"] ] = $prop["VALUE"];
		}
    }
	$add_price_cond_math[] = $fields;
}
#echo "<pre>add_price_cond_math"; print_r($add_price_cond_math); echo "</pre>"; exit();
// ------ цвет
$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>13), false, false, Array("ID", "NAME"));
while($ob = $res->GetNextElement())
{
	 $arFields = $ob->GetFields();
	 
	 $props = CIBlockElement::GetProperty( 13, $arFields["ID"]);
	 $fields = array();
	 while ($prop = $props->GetNext())
    {
		if( $prop["CODE"] == "goods" )
		{
			$fields[ $prop["CODE"] ][] = $prop["VALUE"];
			$res_childs = GetIBlockSectionList ( 2, $prop["VALUE"], false, false, false );
			while( $child = $res_childs-> GetNext() )
			{
				$fields[ $prop["CODE"] ][] = $child["ID"];
			}
		}
		else
		{
			$fields[ $prop["CODE"] ] = $prop["VALUE"];
		}
    }
	$add_price_cond_colors[] = $fields;
}
#echo "<pre>"; print_r($add_price_cond_colors); echo "</pre>";

//============

$ITEM = $arResult["ITEM"];

/* -------------- photo -------------- */

$arImg = array();

$ratio = $ITEM["DETAIL_PICTURE"]["WIDTH"] / $ITEM["DETAIL_PICTURE"]["HEIGHT"];
if( $ratio >= 1 )
{
	$w = $photo_main_width;
	$h = floor( $w / $ratio );
}
else
{
	$h = $photo_main_height;
	$w = floor( $h * $tatio );
}
$arImg = CFile::ResizeImageGet( 
	$ITEM["DETAIL_PICTURE"]["ID"], 
	array('width'=>$w, 'height'=>$h), 
	BX_RESIZE_IMAGE_PROPORTIONAL, 
	true
);

$ITEM["PHOTO"] = array(
	"SRC" => $arImg["src"],
	"TITLE" => $ITEM["DETAIL_PICTURE"]["TITLE"],
	"ALT" => $ITEM["DETAIL_PICTURE"]["ALT"],
	"WIDTH" =>  $arImg["width"],
	"HEIGHT" =>  $arImg["height"],
	"LINK" => $ITEM["DETAIL_PICTURE"]["SRC"]
);
/* -------------- more photo -------------- */
if( count( $ITEM["PROPERTIES"]["MORE_PHOTO"]["VALUES"] ) )
{
	foreach( $ITEM["PROPERTIES"]["MORE_PHOTO"]["VALUES"] as $photo )
	{
		$arImg = array();
		$arImg = CFile::GetFileArray( $photo );
		$arImg["LINK"] = $arImg["SRC"];
		$arImg["ALT"] = $ITEM["DETAIL_PICTURE"]["ALT"];
		$arImg["TITLE"] = $ITEM["DETAIL_PICTURE"]["TITLE"];
		
		if( $arImg["WIDTH"] > $photo_small_width
			|| $arImg["HEIGHT"] > $photo_small_height )
		{
			$arImgRs = CFile::ResizeImageGet( 
				$arImg["ID"], 
				array('width'=>$photo_small_width, 'height'=>$photo_small_height), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
			
			$arImg["SRC"] = $arImgRs["src"];
			$arImg["WIDTH"] = $arImgRs["width"];
			$arImg["HEIGHT"] = $arImgRs["height"];
			unset( $arImgRs );
		}

		$ITEM["MORE_PHOTO"][] = $arImg;
	}
}

if( $ITEM["PROPERTIES"]["HARD"]["VALUE"] == "Да" )
{
	$ITEM["HARD"] = true;
}
/*------------- colors && matherials -------------- */

foreach( $arResult["PROPS"] as &$prop )
{
	// ----------- elements
	$i = 0;
	foreach( $prop["ELEMENTS"] as $item )
	{ 
		$arRes = array();
#echo "<pre>"; print_r($item); echo "</pre><hr/>";			
		$arImg = array();
		$arImg = CFile::ResizeImageGet( 
			$item["PREVIEW_PICTURE"], 
			array('width'=>$color_size, 'height'=>$color_size), 
			BX_RESIZE_IMAGE_EXACT, 
			true);
			
		$arRes["ID"] = $item["ID"];
		$arRes["NAME"] = $item["NAME"];
		$arRes["SRC"] = $arImg["src"];
		
		$arImg = array();
		$arImg = CFile::GetFileArray( $item["DETAIL_PICTURE"] ); 
			
		$arRes["LINK"] = $arImg["SRC"];
		
		$sect_lett = strtolower( substr( $prop["NAME"], 0, 1 ) );
		
		//--------- если нет выбранного, устанавливаем первый
		$i++;
		if( 
			( !$arResult[ $prop["NAME"] ] && $i == 1 )
			||
			( $arResult[ $prop["NAME"] ] == $item["ID"] )
		)
		{
			$arRes["SELECTED"] = true;
		}
		
		// ============ добавляем наценки
		
		// ----- с условием

		foreach( $add_price_cond_math as $cond )
		{
			if(  in_array( $arResult["ITEM"]["IBLOCK_SECTION_ID"], $cond["goods"] ) &&
				$item["IBLOCK_SECTION_ID"] == $cond["matherials"] ){

				if( $cond["add_price"] )
				{
					$arRes["PRICE_ADD"] = $cond["add_price"];
				}
				else
				{
					if( $cond["add_price_percent"] )
					{
						$arRes["PRICE_ADD"] = floor( $arResult["ITEM"]["PRICE"]["PRICE"] / 100 * $cond["add_price_percent"] );
					}
				}
			}
		}
	
		foreach( $add_price_cond_colors as $cond )
		{
#echo "<pre>"; print_r($cond); echo "</pre><br/>goods: "; echo $arResult["ITEM"]["IBLOCK_SECTION_ID"]; "<br/>colors: "; echo $item["IBLOCK_SECTION_ID"] . "<hr/>";		
			if(  in_array( $arResult["ITEM"]["IBLOCK_SECTION_ID"], $cond["goods"] ) &&
				$item["IBLOCK_SECTION_ID"] == $cond["colors"] ){
				if( $cond["add_price"] )
				{
					$arRes["PRICE_ADD"] = $cond["add_price"];
				}
				else
				{
					if( $cond["add_price_percent"] )
					{
						$arRes["PRICE_ADD"] = floor( $arResult["ITEM"]["PRICE"]["PRICE"] / 100 * $cond["add_price_percent"] );
					}
				}
			}
		}
#echo "<pre>"; print_r($arRes["PRICE_ADD"]); echo "</pre>";
		// ----- 
		
		// ------- HTML

		$html = '<div class="prop_element';
		if( $arRes["SELECTED"])
		{
			$html .= ' selected';
		}
		$html .= '" id="' . $sect_lett . '_' . $arRes["ID"] . '">' . PHP_EOL;
		$html .= '<a href="' . $arRes["LINK"] . '" class="prop_el_' . $sect_lett . '">' . PHP_EOL;
		$html .= '<img src="' . $arRes["SRC"] . '"';
		$html .= 'alt="' . $arRes["NAME"] .'" title="' . $arRes["NAME"] . '">' . PHP_EOL;
		$html .= '</a>' . PHP_EOL;
		$html .= '<div class="prop_element_name">' . $arRes["NAME"] . '</div>' . PHP_EOL;
		
		if( $arRes["PRICE_ADD"] )
		{
			$html .= '<div class="prop_price_add">+<span class="prop_price_add_val">' . $arRes["PRICE_ADD"] . '</span> руб.</div>' . PHP_EOL;
		}
		
		$html .= '</div>' . PHP_EOL;
		
		if( $arRes["SELECTED"])
		{
			$prop["SELECTED"] = '<div class="prop_sel_el" id="' . $sect_lett . '_' . 'prop_sel_el" rel="' . $sect_lett . '_' . $arRes["ID"] . '">' . PHP_EOL;
			$prop["SELECTED"] .= '<img src="' . $arRes["SRC"] . '" alt="' . $arRes["NAME"] .'" title="Изменить">' . PHP_EOL;
			$prop["SELECTED"] .= '<div class="prop_element_name">' . $arRes["NAME"] . '</div>' . PHP_EOL;
			
			if( $arRes["PRICE_ADD"] )
			{
				$prop["SELECTED"] .= '<div class="prop_price_add">+ ' . $arRes["PRICE_ADD"] . ' руб.</div>' . PHP_EOL;
				$arResult["PRICE_ADD"]["SECT"][$sect_lett] = $arRes["PRICE_ADD"];
				$arResult["PRICE_ADD"]["SUM"] += $arRes["PRICE_ADD"];
			}
			
			$prop["SELECTED"] .= '</div>' . PHP_EOL;
		}
		
		/*		
		$arElements[ $item["ID"] ] = $arRes;
		*/
		$arElements[ $item["ID"] ] = $html;
	}
	$arResult["ELEMENTS"] = $arElements;

}

$arResult["ITEM"] = $ITEM;