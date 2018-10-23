<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
	<?if(!empty($arResult)):?>
		<ul id="menu_top_line">
			<?foreach($arResult as $arItem):?>
				<?
				if($arItem["SELECTED"]){
					$sel = ' class="selected"';
				} else {
					unset($sel);
				}
				
				if( $arItem["LINK"] == "/catalog/" ){
					$catalog = true;
				} else {
					$catalog = false;
				}
				?>
				<li <?php if( $catalog ):?>id="catalog_link"<?endif?>>
					<a href="<?=$arItem["LINK"]?>"<?=$sel;?>> 
						<?=$arItem["TEXT"]?>
					</a>
					<?php if( $catalog ):?>	

					<?$APPLICATION->IncludeComponent("bitrix:menu", "top_catalog", array(
						"ROOT_MENU_TYPE" => "left",
						"MENU_CACHE_TYPE" => "A",
						"MENU_CACHE_TIME" => "36000000",
						"MENU_CACHE_USE_GROUPS" => "Y",
						"MENU_THEME" => "",
						"CACHE_SELECTED_ITEMS" => "N",
						"MENU_CACHE_GET_VARS" => array(
						),
						"MAX_LEVEL" => "3",
						"CHILD_MENU_TYPE" => "left",
						"USE_EXT" => "Y",
						"DELAY" => "N",
						"ALLOW_MULTI_SELECT" => "N",
						),
						false
					);?>

					<?php endif; ?>
				</li>
			<?endforeach?>
		</ul>
	<?endif?>
	