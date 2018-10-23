<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
#echo "<pre>"; print_r($arResult); echo "</pre>";
if(count($arResult["ELEMENTS"]))
{
	foreach($arResult["ELEMENTS"] as $El)
	{
		if($El["PREVIEW_PICTURE"])
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$El["PREVIEW_PICTURE"], 
				array('width'=>100, 'height'=>100), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
			$basket[$El["ID"]]["IMG"] = $arImg["src"];
			
		}
		if($El["PROPS"]["COLORS"]["PREVIEW_PICTURE"])
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$El["PROPS"]["COLORS"]["PREVIEW_PICTURE"], 
				array('width'=>75, 'height'=>75), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
			$basket[$El["ID"]]["COLOR_IMG"] = $arImg["src"];
			$basket[$El["ID"]]["COLOR_ALT"] = $El["PROPS"]["COLORS"]["NAME"];
		}
		if($El["PROPS"]["MATHERIALS"]["PREVIEW_PICTURE"])
		{
			$arImg = array();
			$arImg = CFile::ResizeImageGet( 
				$El["PROPS"]["MATHERIALS"]["PREVIEW_PICTURE"], 
				array('width'=>75, 'height'=>75), 
				BX_RESIZE_IMAGE_PROPORTIONAL, 
				true
			);
			$basket[$El["ID"]]["MATHERIALS_IMG"] = $arImg["src"];
			$basket[$El["ID"]]["MATHERIALS_ALT"] = $El["PROPS"]["MATHERIALS"]["NAME"];
		}
		$basket[$El["ID"]] = array(
		"ID" => $El["ID"],
		"PRODUCT_ID" => $El["PRODUCT_ID"],
		"NAME" => $El["NAME"],
		"QUANTITY" => $El["QUANTITY"],
		"PRICE" => $El["PRICE"]["PRICE"],
		"SUM" => $El["SUM"],
		"DISCOUNT" => $El["PRICE"]["DISCOUNT"],
		"IMG" => $basket[$El["ID"]]["IMG"],
		"URL" => $El["DETAIL_PAGE_URL"],
		"COLORS_IMG" => $basket[$El["ID"]]["COLOR_IMG"],
		"COLORS_ALT" => $basket[$El["ID"]]["COLOR_ALT"],
		"MATHERIALS_IMG" => $basket[$El["ID"]]["MATHERIALS_IMG"],
		"MATHERIALS_ALT" => $basket[$El["ID"]]["MATHERIALS_ALT"],
		);
	}
}

?>
<h1>Корзина</h1>
<form method="post" action="/personal/order/make/" name="basket_form" id="basket_form">
	<input type="hidden" id="FUSER_ID" name="FUSER_ID" value="<?=CSaleBasket::GetBasketUserID();?>">
	<div id="basket_items">
	<?if( count($basket) ):?>
	<table id="basket_list">
		<thead>
			<tr>
				<th class="th-img"></th>
				<th class="th-name"></th>
				<th class="th-math">Материал</th>
				<th class="th-color">Цвет</th>
				<th class="th-price">Цена</th>
				<th class="th-quant">Количество</th>
				<th class="th-sum">Сумма</th>
				<th class="th-del"></th>
			</tr>
		</thead>
		<tbody>
			<?foreach($basket as $El):?>
			<tr id="<?=$El["ID"];?>">
				<td class="td-img"><a href="<?=$El["URL"];?>?bid=<?=$El["ID"];?>"><img src="<?=$El["IMG"];?>"></a></td>
				<td class="td-name"><a href="<?=$El["URL"];?>?bid=<?=$El["ID"];?>"><span class="name"><?=$El["NAME"];?></span></a></td>
				<td class="td-math"><img src="<?=$El["MATHERIALS_IMG"];?>" title="<?=$El["MATHERIALS_ALT"];?>"><br/><?=$El["MATHERIALS_ALT"];?></td>
				<td class="td-color"><img src="<?=$El["COLORS_IMG"];?>" title="<?=$El["COLORS_ALT"];?>"><br/><?=$El["COLORS_ALT"];?></td>
				<td class="td-price"><span class="price"><? echo number_format($El["PRICE"], 0, '.', ' ');?></span></td>
				<td class="td-quant"><input id="q_<?php echo $El["ID"]; ?>" type="number" min=1 rel="<?php echo $El["ID"]; ?>" name="quantity[<?php echo $El["ID"]; ?>]" 
					value="<? echo floor($El["QUANTITY"]);?>" onchange="update_item(q_<?php echo $El["ID"]; ?>)" /></td>
				<td class="td-sum"><span class="price"><? echo number_format($El["SUM"], 0, '.', ' ');?></span></td>
				<td class="td-del"><button type="submit" formaction="del.php" name="item_del_<?=$El["ID"];?>" value="<?=$El["ID"];?>" class="del_cross" title="Удалить">
				<i class="fa fa-times del_item" aria-hidden="true" style="font-size:28px"></i></button>
				</td>
			</tr>
			<?endforeach?>
		</tbody>
	</table>
	<?if($arResult["SUM_DISCOUNT"] > 0):?>
	<p class="cart_sub_sum">Сумма: <? echo number_format($arResult["SUM_WITHOUT_DISCOUNT"],0, '.', ' ');?></p>
	<p class="cart_disc">Скидка: <? echo number_format($arResult["SUM_DISCOUNT"],0, '.', ' ');?></p>
	<?endif;?>
	<p id="sum_basket">Итого: <? echo number_format($arResult["ALL_SUM"],0, '.', ' ');?></p>
	<input type="submit" id="order" value="Оформить заказ" class="btn">
	<?else:?>
	<p>Корзина пуста.</p>
	<?endif?>
	</div>
</form>