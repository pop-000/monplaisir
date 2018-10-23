<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<?#echo "<pre>"; print_r($arResult); echo "</pre>";?>
<table cellpadding="8" cellspacing="0" border="0" class="tbl_goods_list">
	<thead>
		<th>Фото</th>
		<th>Название</th>
		<th>Материал</th>
		<th>Цвет</th>
		<th>Новинка</th>
		<th>Лидер продаж</th>
		<th>Акция</th>
		<th>Диаметр</th>
		<th>Ширина</th>
		<th>Глубина</th>
		<th>Высота</th>
		<th>Цена</th>
	</thead>
	<? foreach ($arResult['ITEMS'] as $EL)
	{
	?>
	<tr>
		<td><img src="<?=$EL["PREVIEW_PICTURE"]["SRC"]?>" height="60px"/></td>
		<td><?=$EL["NAME"];?></td>
		<td>
		<? if( $EL["PROPERTIES"]["MATHERIALS"] )
		{
			foreach( $EL["PROPERTIES"]["MATHERIALS"]["VALUES"] as $math ){
				echo $arResult["PROP_NAMES"]["MATHERIALS"][$math] . "<br />";
			}
		}
		else
		{
			echo "&mdash;&mdash;&mdash;";
		}
		?>
		</td>
		<td>
		<? if( $EL["PROPERTIES"]["COLORS"] )
		{
			foreach( $EL["PROPERTIES"]["COLORS"]["VALUES"] as $math ){
				echo $arResult["PROP_NAMES"]["COLORS"][$math] . "<br />";
			}
		}
		?>
		</td>
		<td>		
			<?if($EL["MARKERS"]["NEWPRODUCT"]):?>
			Да
			<?endif;?>
		</td>
		<td>
			<?if($EL["MARKERS"]["SALELEADER"]):?>
				Да
			<?endif;?>	
		</td>
		<td>
			<?if($EL["MARKERS"]["SPECIALOFFER"]):?>
				Да
			<?endif;?>
		</td>
		<td><?=$EL["GABARIT"]["DIAMETR"];?></td>
		<td><?=$EL["GABARIT"]["WIDTH"];?></td>
		<td><?=$EL["GABARIT"]["DEPTH"];?></td>
		<td><?=$EL["GABARIT"]["HEIGHT"];?></td>
		<td>
			<?=number_format( $EL["PRICE"]['PRICE'] , 0 , "." , " " );?>
		</td>
	</tr>
	<?
	}
	?>
</table>