<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<?$APPLICATION->SetTitle($arResult["ITEM"]["NAME"]);
$APPLICATION->SetAdditionalCSS("/js/fancybox/jquery.fancybox.min.css");
$APPLICATION->AddHeadScript('/js/fancybox/jquery.fancybox.min.js');?>
<? #echo "<pre>"; print_r($arResult); echo "</pre>"; ?>
<? #echo "<pre>"; print_r($_SESSION); echo "</pre>"; ?>

<?$ITEM = $arResult["ITEM"];?>

<?if($ITEM["PRICE"]["DISCOUNT_PRICE"] != $ITEM["PRICE"]["PRICE"]):
		$price = $ITEM["PRICE"]["DISCOUNT_PRICE"];
		$price_old = $ITEM["PRICE"]["PRICE"];
	else:
		$price = $ITEM["PRICE"]["PRICE"];
	endif;?>
	
<div id="fuser_id" style="display:none"><?=$arResult["FUSER_ID"];?></div>
<div id="item_id" style="display:none"><?=$ITEM["ID"];?></div>
<div id="old_price" style="display:none"><?=$price_old?></div>
<div id="price" style="display:none"><?=$price;?></div>
<div id="sel_math" style="display:none"><?=$arResult["MATHERIALS"];?></div>
<div id="sel_col" style="display:none"><?=$arResult["COLORS"];?></div>
<div id="buy_link" style="display:none"><?=$ITEM["BUY"];?></div>

<?php if(isset($arResult["BASKET"])):?>
<div id="basket" style="display:none">
	<?php echo $arResult["BASKET"]; ?>
</div>
<?php endif; ?>

<section class="block_photo">		
	<div class="photo_main">
		<a href="<?=$ITEM["PHOTO"]["LINK"];?>" data-fancybox="gallery" data-caption="<?=$ITEM["PHOTO"]["ALT"];?>">
		<img src="<?=$ITEM["PHOTO"]["SRC"];?>" 
			id="main_photo" 
			title="<?=$ITEM["PHOTO"]["TITLE"];?>" 
			alt="<?=$ITEM["PHOTO"]["ALT"];?>" />
		</a>
		<div class="markers">
			<?foreach( $ITEM["MARKERS"] as $prop_code => $prop_name ):
				switch( $prop_code ):
					case "SPECIALOFFER":?>
					<span class="specialoffer" title="<?=$prop_name;?>"></span>
						<?break;
					case "NEWPRODUCT":?>
					<span class="newproduct" title="<?=$prop_name;?>">Новинка</span>
						<?break;
					case "SALELEADER":?>
					<span class="saleleader" title="<?=$prop_name;?>"></span>
						<?break;		
				endswitch;
			endforeach;?>
		</div>		
	</div>

	<?php if( count( $ITEM["MORE_PHOTO"] ) ): ?>
	<div class="photo_add"> 
		<?foreach( $ITEM["MORE_PHOTO"] as $photo ):
		?>
		<div class="small_photo">
			<a href="<?=$photo["LINK"]?>" data-fancybox="gallery" data-caption="<?=$photo["ALT"];?>">
			<img src="<?=$photo["SRC"]?>"
				title="<?=$photo["TITLE"];?>" 
				alt="<?=$photo["ALT"];?>" />
			</a>	
		</div>	
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	
	<?foreach( $ITEM["PROP_TO_LABELS"] as $prop ):?>
	<div class="<?=$prop["CODE"];?>"><?=$prop["NAME"];?></div>
	<?endforeach;?>	
	
</section>
<section class="block_options">	
	<h1><?=$ITEM["NAME"];?></h1>

	<?foreach( $arResult["PROPS"] as $prop ):
		$prop_lett = strtolower( substr( $prop["NAME"], 0, 1 ) );
	?>
	<div class="prop">
		<h3><?=$prop["RU"];?></h3>
		<div class="prop_sel">
			<?=$prop["SELECTED"]; ?>
			<button onclick="show_el( 'prop_items_<?=$prop_lett;?>' )">Выбрать</button>
		</div>
		<p class="small">Вариантов: <?=$prop["COUNT"];?></p>
	</div>
	<?endforeach;?>
	

	<div id="price_block">
	
		<?if($price_old):?>
		<div class="old_price">
			<svg width="80" height="30" style="position:absolute; top:8px; left:-5px">
				<line x1="80" y1="0" x2="0" y2="30" style="stroke:rgb(255,0,0);stroke-width:1" />
			</svg>
			<?=number_format( $price_old , 0 , "." , " " )?>&nbsp;
		</div>
		<?endif;?>
	
		<div id="price_calculate" <?if($arResult["PRICE_ADD"]["SUM"]):?>style="display:none"<?endif;?>>
			<span id="price_main"><?=$price?></span>
			<?foreach($arResult["PRICE_ADD"]["SECT"] as $k => $v):?>
				+ <span class="price_add" id="price_add_<?=$k;?>"><?=$v;?></span>
			<?endforeach;
			$price += $arResult["PRICE_ADD"]["SUM"]; ?>
		= <span id="calculate_price_fin"><?=$price;?></span> руб.
		</div>
	
	
		<div class="price">
			<span class="price_fin">
				<span id="price_fin"><?=number_format( $price , 0 , "." , " " )?></span>
				<span id="price_main_value" style="display:none"><?=$price;?></span>
				<span class="rub">руб.</span>
			</span>	
		</div>	
	</div>

	<h4>Размеры (мм):</h4>
	<div class="sizes">
		<table cellpadding="8" cellspacing="0">
			<?if( $ITEM["GABARIT"]["DIAMETR"]):?>
			<thead>
				<tr>
					<th>Диаметр</th>
					<th>Высота</th>
				</tr>	
			</thead>
			<tbody>
				<tr>
					<td><?=$ITEM["GABARIT"]["DIAMETR"];?></td>
					<td><?=$ITEM["GABARIT"]["HEIGHT"];?></td>
				</tr>
			</tbody>
			<?else:?>
			<thead>
				<th>Высота</th>
				<th>Ширина</th>
				<th>Глубина</th>
			</thead>
			<tbody>
				<tr>
					<td><?=$ITEM["GABARIT"]["HEIGHT"];?></td>
					<td><?=$ITEM["GABARIT"]["WIDTH"];?></td>
					<td><?=$ITEM["GABARIT"]["DEPTH"];?></td>
				</tr>
			</tbody>
			<?endif;?>
		</table>
		<?if( $ITEM["PROPERTIES"]["WEIGHT"]["VALUE"] ):?>
		<p>Вес: <?=$ITEM["PROPERTIES"]["WEIGHT"]["VALUE"];?> кг.</p>
		<?endif;?>
	</div>
	
	<?if($arResult["IN_BASKET"]):?>
	<a href="/personal/cart/" id="button_basket" class="already">Уже в корзине</a>
	<?php else: ?>
	<button id="button_basket" class="buy" onclick="add2basket(<?=$ITEM["ID"];?>)">В корзину</button>
	<?php endif; ?>
	
	<?if( $ITEM["PREVIEW_TEXT"] ):?>
	<p><?=$ITEM["PREVIEW_TEXT"];?></p>
	<?endif;?>
	
</section>
	
<!--
<div id="contmenu">
	<button id="but_select">Выбрать</button><br />
	<button id="but_browse">Крупнее</button>
</div>
-->	
<div class="mask"></div>
<?php
	foreach( $arResult["PROPS"] as $prop ):
	$prop_lett = strtolower( substr( $prop["NAME"], 0, 1 ) );
?>
<div class="prop_items" id="prop_items_<?=$prop_lett;?>">
	<?php 
	foreach( $prop["SECTIONS"] as $sect)
	{
		if( count( $sect["ELEMENTS"] ) )
		{
			?>
			<h4 class="prop_title"><?=$sect["NAME"];?></h4>
			<?
			if( $sect["DESCRIPTION"] )
			{
				?>
				<div class="prop_sect_descr"><?=$sect["DESCRIPTION"];?></div>
				<?
			}

			foreach( $sect["ELEMENTS"] as $el )
			{
				echo $arResult["ELEMENTS"][ $el ];
			}
		}
		if( count( $sect["CHILDS"] ) )
		{
			foreach( $sect["CHILDS"] as $child )
			{
				if( count( $child["ELEMENTS"] ))
				{
					?>
					<h4 class="prop_title"><?=$child["NAME"];?></h4>
					<?
					if( $child["DESCRIPTION"] )
					{
						?>
						<div class="prop_sect_descr"><?=$child["DESCRIPTION"];?></div>
						<?
					}
					
					foreach( $child["ELEMENTS"] as $el )
					{
						echo $arResult["ELEMENTS"][ $el ];
					}
				}
				
				if( count( $child["CHILDS"] ))
				{
					foreach( $child["CHILDS"] as $grandchild )
					{
						if( count( $grandchild["ELEMENTS"] ))
						{
							?>
							<h4 class="prop_title"><?=$grandchild["NAME"];?></h4>
							<?
							if( $grandchild["DESCRIPTION"] )
							{
								?>
								<div class="prop_sect_descr"><?=$grandchild["DESCRIPTION"];?></div>
								<?
							}
							
							foreach( $grandchild["ELEMENTS"] as $el )
							{
								echo $arResult["ELEMENTS"][ $el ];
							}
						}
					}
				}
			}
		}
	}
	?>
</div>
<?endforeach;?>
<br clear="both">