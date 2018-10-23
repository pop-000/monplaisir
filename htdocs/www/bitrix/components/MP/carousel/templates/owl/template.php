<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php 
$APPLICATION->SetAdditionalCSS("owl.carousel.min.css"); 
$APPLICATION->SetAdditionalCSS("owl.theme.default.min.css"); 
#$APPLICATION->AddHeadScript('owl.carousel.min.js');
?>
<?# echo "<pre>"; print_r($arResult); echo "</pre>"; ?>
<?php if( count( $arResult ) ): ?>
<div class="owl-carousel owl-theme">
<?foreach($arResult as $arItem):?>
	<div class="carousel-item">
		<img src="<?=$arItem["IMG"]["SRC"]?>">
		<?if( !empty( $arItem["URL"] ) ):?>
		<a href="<?=$arItem["URL"]?>">
		<?endif;?>	
		<?if( strlen( $arItem["PREVIEW_TEXT"] ) ):?>
			<div class="carousel-item-text">
				<?=$arItem["PREVIEW_TEXT"]?>
			</div>
		<?endif;?>	
		<?if( !empty( $arItem["URL"] ) ):?>
		</a>
		<?endif;?>	
	</div>	
<?endforeach?>
</div>
<?endif?>