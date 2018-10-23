<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<? #echo "<pre>"; print_r($arResult); echo "</pre>"; exit(); ?>
<table cellpadding="8" cellspacing="0" border="0" class="tbl_goods_list">
	<thead>
		<th>ID</th>
		<th>Картинка</th>
		<th>Название</th>
	</thead>
	<? foreach ($arResult['SECTIONS'] as $section)
	{
	?>
	<tr>
		<td class="sect"><?=$section["ID"];?></td>
		<td class="sect"><?=$section["PREVIEW_PICTURE"];?></td>
		<td class="sect"><?=$section["NAME"];?></td>
	</tr>
	<?
		if( count( $section["ELEMENTS"] ) )
		{
			foreach( $section["ELEMENTS"] as $el )
			{
			?>
	<tr>		
		<td><?=$el["ID"];?></td>
		<td><img src="<?=$el["IMG_SRC"];?>"></td>
		<td><?=$el["NAME"];?></td>
	</tr>	
			<?
			}
		}
	}
	?>
</table>