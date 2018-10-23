<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
#echo "<pre>"; print_r($arResult); echo "</pre>";
foreach ($arResult["GRID"]["ROWS"] as $item)
{
	foreach( $item["PROPS"] as $prop )
	{
		if( $prop["CODE"] == "COLORS" )
		{
			$rsColor = CIBlockElement::GetByID( $prop["VALUE"] );
			if($arColor = $rsColor->GetNext())
			{
				$arImg = array();
				$arImg = CFile::ResizeImageGet( 
					$arColor["PREVIEW_PICTURE"], 
					array('width'=>75, 'height'=>75), 
					BX_RESIZE_IMAGE_PROPORTIONAL, 
					true
				);
				
				$color = array(
					"ID" => $arColor["ID"],
					"NAME" => $arColor["NAME"],
					"PICTURE" => $arImg["src"],
				);
			}
		}
		
		if( $prop["CODE"] == "MATHERIAL" )
		{
			$rsMath = CIBlockElement::GetByID( $prop["VALUE"] );
			if($arMath = $rsMath->GetNext())
			{
				$arImg = array();
				$arImg = CFile::ResizeImageGet( 
					$arMath["PREVIEW_PICTURE"], 
					array('width'=>75, 'height'=>75), 
					BX_RESIZE_IMAGE_PROPORTIONAL, 
					true
				);
				
				$matherial = array(
					"ID" => $arMath["ID"],
					"NAME" => $arMath["NAME"],
					"PICTURE" => $arImg["src"],
				);
			}
		}
	}
	$basket[] = array(
		"ID" => $item["ID"],
		"PRODUCT_ID" => $item["PRODUCT_ID"],
		"NAME" => $item["NAME"],
		"QUANTITY" => $item["QUANTITY"],
		"PRICE" => $item["FULL_PRICE_FORMATED"],
		"DISCOUNT" => $item["DISCOUNT_PRICE_PERCENT_FORMATED"],
		"PICTURE" => $item["DETAIL_PICTURE_SRC"],
		"URL" => $item["DETAIL_PAGE_URL"],
		"COLOR" => $color,
		"MATHERIAL" => $matherial,
	);
}
?>
<h1>Корзина</h1>
<div id="basket_items">
<?if( count($basket) ):?>
<table id="basket_list">
	<thead>
		<tr>
			<td></td>
			<td></td>
			<td>Количество</td>
			<td>Материал</td>
			<td>Цвет</td>
			<td>Цена</td>
		</tr>
	</thead>
	<tbody>
		<?foreach($basket as $item):?>
		<tr id="<?=$item["ID"];?>">
			<td><a href="<?=$item["URL"];?>" target="_blank"><img src="<?=$item["PICTURE"];?>"></a></td>
			<td><a href="<?=$item["URL"];?>" target="_blank"><span class="name"><?=$item["NAME"];?></span></a></td>
			<td align="center"><?=$item["QUANTITY"];?></td>
			<td><img src="<?=$item["MATHERIAL"]["PICTURE"];?>"><br/><?=$item["MATHERIAL"]["NAME"];?></td>
			<td><img src="<?=$item["COLOR"]["PICTURE"];?>"><br/><?=$item["COLOR"]["NAME"];?></td>
			<td><span class="price"><?=$item["PRICE"];?></span></td>
		</tr>
		<?endforeach?>
	</tbody>
</table>
<p id="sum_basket">Итого: <?=$arResult["allSum_FORMATED"]?></p>
<?else:?>
<p>Корзина пуста.</p>
<?endif?>
</div>