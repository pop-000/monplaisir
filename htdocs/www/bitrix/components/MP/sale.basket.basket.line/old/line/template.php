<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php #echo "<pre>"; print_r($_SESSION); echo "</pre>"; ?>
<a href="<?=$arParams["PATH_TO_BASKET"]?>" id="basket_mini_pict">
	<div id="basket_mini_count"><?=$arResult["NUM_PRODUCTS"];?></div>
</a>	
<div id="basket_mini_sum">
<?if($arResult["SUM"] > 0):?>
<?php if( $arResult["SUM_OLD"] > $arResult["SUM"] ): ?>
	<div class="sum_old">
		<svg width="50" height="18" style="position:absolute; top:0; left:0"><line x1="50" y1="0" x2="0" y2="18" style="stroke:rgb(255,0,0);stroke-width:1" /></svg>
		<?=$arResult["SUM_OLD"];?>
	</div>
<?php endif; ?>
<?=number_format ( $arResult["SUM"] , 0 , "." , " " );?> <span class="small-x">руб.</span>
<?php else: ?>
	<span class="small-x">Пусто</span>
<?endif;?>
</div>
