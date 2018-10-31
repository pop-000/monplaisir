<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
#echo "<pre>"; print_r($arResult); echo "</pre>";
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . "/spinner/jquery.loading-indicator.css");
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . "/spinner/jquery.loading-indicator.js");
if(count($arResult["ELEMENTS"])):?>

	<h1>Корзина</h1>
	<form method="post" action="/personal/order/make/" name="basket_form" id="basket_form">
		<input type="hidden" id="FUSER_ID" name="FUSER_ID" value="<?=CSaleBasket::GetBasketUserID();?>">
		<div id="basket_items">
		<table id="basket_list">
			<thead>
				<tr>
					<th class="th-img"></th>
					<th class="th-name"></th>
					<th class="th-color">Цвет</th>
					<th class="th-math">Материал</th>
					<th class="th-price">Цена</th>
					<th class="th-quant">Количество</th>
					<th class="th-sum">Сумма</th>
					<th class="th-del"></th>
				</tr>
			</thead>
			<tbody>
		<? foreach($arResult["ELEMENTS"] as $El):?>
		
				<tr id="<?=$El["ID"];?>">
					
					<? if($El["PREVIEW_PICTURE"])
						{
							$arImg = array();
							$arImg = CFile::ResizeImageGet( 
								$El["PREVIEW_PICTURE"], 
								array('width'=>100, 'height'=>100), 
								BX_RESIZE_IMAGE_PROPORTIONAL, 
								true
							);
							$photo = $arImg["src"];
						}
					?>
					<td class="td-img"><a href="<?=$El["DETAIL_PAGE_URL"];?>?bid=<?=$El["ID"];?>"><img src="<?=$photo;?>"></a></td>
					<td class="td-name"><a href="<?=$El["DETAIL_PAGE_URL"];?>?bid=<?=$El["ID"];?>"><span class="name"><?=$El["NAME"];?></span></a>
					</td>
					
					<? if($El["PROPS"]["COLORS"]["PREVIEW_PICTURE"])
					{
						$arImg = array();
						$arImg = CFile::ResizeImageGet( 
							$El["PROPS"]["COLORS"]["PREVIEW_PICTURE"], 
							array('width'=>75, 'height'=>75), 
							BX_RESIZE_IMAGE_PROPORTIONAL, 
							true
						);
						$colors_photo = $arImg["src"];
						$colors_alt = $El["PROPS"]["COLORS"]["NAME"];
					}
					?>
					
					<td class="td-color"><img src="<?=$colors_photo;?>" title="<?=$colors_alt;?>"><br/><?=$colors_alt;?></td>
					
					<?if($El["PROPS"]["MATHERIALS"]["PREVIEW_PICTURE"])
					{
						$arImg = array();
						$arImg = CFile::ResizeImageGet( 
							$El["PROPS"]["MATHERIALS"]["PREVIEW_PICTURE"], 
							array('width'=>75, 'height'=>75), 
							BX_RESIZE_IMAGE_PROPORTIONAL, 
							true
						);
						$matherials_photo = $arImg["src"];
						$matherials_alt = $El["PROPS"]["MATHERIALS"]["NAME"];
					}
					?>
										
					<td class="td-math">
					<? if(empty($El["HARD"])): ?>
					<img src="<?=$matherials_photo;?>" title="<?=$matherials_alt;?>"><br/><?=$matherials_alt;?>
					<? endif; ?>
					</td>
					
					<td class="td-price"><span class="price"><? echo number_format($El["PRICE"]["PRICE"], 0, '.', ' ');?></span></td>
					
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
	</form>	
<?else:?>
	<p>Корзина пуста.</p>
<?endif?>