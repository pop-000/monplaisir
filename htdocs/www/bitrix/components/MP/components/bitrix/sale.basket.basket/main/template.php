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
		
		if( $prop["CODE"] == "MATHERIALS" )
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
<span id="FUSER_ID" style="display:none"><?=CSaleBasket::GetBasketUserID();?></span>
<form method="post" action="/personal/order/make/" name="basket_form" id="basket_form">
	<input type="hidden" name="FUSER_ID" value="<?=CSaleBasket::GetBasketUserID();?>">
	<div id="basket_items">
	<?if( count($basket) ):?>
	<table id="basket_list">
		<thead>
			<tr>
				<th class="th-img"></th>
				<th class="th-name"></th>
				<th class="th-quant">Количество</th>
				<th class="th-math">Материал</th>
				<th class="th-color">Цвет</th>
				<th class="th-price">Цена</th>
				<th class="th-del"></th>
			</tr>
		</thead>
		<tbody>
			<?foreach($basket as $item):?>
			<tr id="<?=$item["ID"];?>">
				<td class="td-img"><a href="<?=$item["URL"];?>" target="_blank"><img src="<?=$item["PICTURE"];?>"></a></td>
				<td class="td-name"><a href="<?=$item["URL"];?>" target="_blank"><span class="name"><?=$item["NAME"];?></span></a></td>
				<td class="td-quant"><?=$item["QUANTITY"];?></td>
				<td class="td-math"><img src="<?=$item["MATHERIAL"]["PICTURE"];?>"><br/><?=$item["MATHERIAL"]["NAME"];?></td>
				<td class="td-color"><img src="<?=$item["COLOR"]["PICTURE"];?>"><br/><?=$item["COLOR"]["NAME"];?></td>
				<td class="td-price"><span class="price"><?=$item["PRICE"];?></span></td>
				<td class="td-del"><i class="fa fa-times del_item" rel="<?=$item["ID"];?>" aria-hidden="true" style="font-size:28px"></i></td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>
	<?if($arResult["DISCOUNT_PRICE_ALL"] > 0):?>
	<p class="cart_sub_sum">Сумма: <?=$arResult["PRICE_WITHOUT_DISCOUNT"];?></p>
	<p class="cart_disc">Скидка: <?=$arResult["DISCOUNT_PRICE_ALL_FORMATED"];?></p>
	<?endif;?>
	<p id="sum_basket">Итого: <?=$arResult["allSum_FORMATED"]?></p>
	<input type="submit" id="order" value="Оформить заказ">
	<?else:?>
	<p>Корзина пуста.</p>
	<?endif?>
	</div>
</form>