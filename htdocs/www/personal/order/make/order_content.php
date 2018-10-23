<table>
	<tr>
		<td><?=$ITEMS["PRODUCT_ID"]?></td>
		<td><a href="<?=$ITEMS['PRODUCT_URL']?></a>" target="_blank"><img src="<?=$ITEMS["PICT"]?>"/></a></td>
		<td><a href="<?=$ITEMS['PRODUCT_URL']?></a>" target="_blank"><?=$ITEMS['NAME']?></a></td>
		<td><a href="<=$ITEM["PRICE"]"?></td>
		
		<?if($ITEM["PROPS"]["MATHERIAL"]["ID"]):?>
		<td><a href="<?=$site_url;?>"><?=$ITEM["PROPS"]["MATHERIAL"]["PICTURE"]?>></a>
			<br><?=$ITEM["PROPS"]["MATHERIAL"]["NAME"] .(. $ITEM["PROPS"]["MATHERIAL"]["ID"].')' ?></td>
		<?else:?>
		<td>&nbsp;</td>
		
		<?if($ITEM["PROPS"]["COLORS"]["ID"]):?>
		<td><a href="<?=$site_url;?>"</a><?=$ITEM["PROPS"]["COLORS"]["PICTURE"]?></a>
			<br><?=$ITEM["PROPS"]["COLORS"]["NAME"] .(. $ITEM["PROPS"]["COLORS"]["ID"].')' . </td>
		<?else:?>
		<td>&nbsp;</td>
	</tr>	
</table>
		
<h3>Итого: <?=number_format($arRESULT["sum"]);?> руб.</h3>