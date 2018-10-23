<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<? #echo "<pre>"; print_r($arResult); echo "</pre>"; exit(); ?>

<table cellpadding="8" cellspacing="0" border="0" class="tbl_goods_list">
	<thead>
		<th>ID</th>
		<th>Название</th>
		<th>Производитель</th>
		<th>Руб.</th>
		<th>Процент</th>
		<th>Товары</th>
		<th>Материалы</th>
		<th>Цвета</th>
	</thead>
	<? foreach ($arResult as $item)
	{
	?>
	<tr>
		<td><?=$item["ID"];?></td>
		<td><?=$item["NAME"];?></td>
		<td><?=$item["mnfct"];?></td>
		<td><?=$item["rub"];?></td>
		<td><?=$item["percent"];?></td>
		<td><?=$item["goods"];?></td>
		<td><?=$item["matherials"];?></td>
		<td><?=$item["colors"];?></td>
	</tr>
	<?
	}
	?>
</table>