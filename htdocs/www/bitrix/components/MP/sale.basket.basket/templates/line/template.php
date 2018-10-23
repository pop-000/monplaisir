<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php #echo "<pre>"; print_r($arResult); echo "</pre>"; ?>
<a href="<?=$arParams["PATH_TO_BASKET"]?>" id="basket_mini_pict">
	<div id="basket_mini_count"><?=$arResult["COUNT_PRODUCT"];?></div>
</a>	
<div id="basket_mini_sum">
<?if($arResult["ALL_SUM"] > 0):?>
<?php if( $arResult["SUM_WITHOUT_DISCOUNT"] > $arResult["ALL_SUM"] ): ?>
	<div class="sum_old">
		<svg width="50" height="18" style="position:absolute; top:0; left:0"><line x1="50" y1="0" x2="0" y2="18" style="stroke:rgb(255,0,0);stroke-width:1" /></svg>
		<? echo number_format($arResult["SUM_WITHOUT_DISCOUNT"], 0, '.', ' ');?>
	</div>
<?php endif; ?>
<?=number_format ( $arResult["ALL_SUM"] , 0 , "." , " " );?> <span class="small-x">руб.</span>
<?php else: ?>
	<span class="small-x">Пусто</span>
<?endif;?>
</div>