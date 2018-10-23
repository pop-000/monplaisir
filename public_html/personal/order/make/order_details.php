<h2>Состав заказа</h2>
<table class="order_detail">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th>Название</th>
			<th>Цвет</th>
			<th>Материал</th>
			<th>Цена</th>
			<th>Кол-во</th>
			<th>Сумма</th>
		</tr>
	</thead>
	<tbody>
	<? foreach($arBasketItems as $item):
		$sum_row = ceil($item["PRICE"] * $item["QUANTITY"]);
		$sum_discount = ceil($item["DISCOUNT_PRICE"] * $item["QUANTITY"]);
	?>
		<tr>
			<td><img src="<?=$item["IMG"];?>" height="100" /></td>
			<td><?=$item["NAME"];?></td>
			<td><img src="<?=$item["PROPS"]["COLORS"]["PICTURE"];?>" width="75" height="75" /><br>
				<?=$item["PROPS"]["COLORS"]["NAME"];?>
			</td>
			<td><img src="<?=$item["PROPS"]["MATHERIALS"]["PICTURE"];?>" width="75" height="75" /><br/>
				<?=$item["PROPS"]["MATHERIALS"]["NAME"];?>
			</td>
			<td><?=number_format(ceil($item["PRICE"]), 0, "", " ");?> руб.</td>
			<td><?=ceil($item["QUANTITY"]);?></td>
			<td><?=number_format($sum_row, 0, "", " ");?> руб.</td>
		</tr>
	<? 
	$count++;
	$sum_total += $sum_row;
	$sum_discount_total += $sum_discount;	
	endforeach;
	?>	
	</tbody>
</table>
<hr/>
<div class="resume">
	<p>Позиций: <?=$count;?><br/>
	<b>Сумма: <?=number_format(ceil($sum_total), 0, "", " ");?> руб.</b>
<? if(!empty($sum_discount_total)):?>
	<br/>
	Скидка: <?=number_format(ceil($sum_discount_total), 0, "", " ");?> руб.
<? endif; ?>
	</p>
</div>