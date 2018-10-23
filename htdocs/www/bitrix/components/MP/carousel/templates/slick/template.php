<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php 
$APPLICATION->AddHeadScript('//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js');
$APPLICATION->SetAdditionalCSS("//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css"); 
$APPLICATION->SetAdditionalCSS("slick-theme.css"); 
?>
<?# echo "<pre>"; print_r($arResult); echo "</pre>"; ?>
<?php if( count( $arResult ) ): ?>
<section class="carousel">
	<div class="carousel-single">
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
</section>
<?endif?>