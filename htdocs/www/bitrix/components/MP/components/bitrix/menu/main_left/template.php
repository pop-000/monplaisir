<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<nav id="main_menu_container" class="clearfix">
	<ul id="main_menu">
		<li class="root search">
			<a href="/search">
				<span class="icon"></span>
				<span class="root_name">Поиск&hellip;</span>
			</a>	
		</li>
	<?foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns):
		if (is_array($arColumns) && count($arColumns) > 0){
			$parent = true;
		} else {
			$parent = false;
		}
		?>
    <!-- first level-->
		<li class="root<?if($arResult["ALL_ITEMS"][$itemID]["SELECTED"]):?> current<?endif?><?if( $parent ):?> dropdown<?endif?>">
			<a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>" class="root_item">
				<span class="icon" style="background:url('<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["icon_src"]?>') no-repeat center"></span>
				<span class="root_name"><?=$arResult["ALL_ITEMS"][$itemID]["TEXT"]?></span>	
			</a>	
	<?if ( $parent ):?>
		<?foreach($arColumns as $key=>$arRow):?>
			<ul class="childs">
			<?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?>  <!-- second level-->
				<li>
					<a href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>">
						<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>
					</a>
				</li>
			<?endforeach;?>
			</ul>
		<?endforeach;?>
	<?endif?>
		</li>
	<?endforeach;?>
	</ul>
</nav>	