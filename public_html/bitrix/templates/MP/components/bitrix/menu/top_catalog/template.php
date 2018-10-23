<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="top_catalog">
	<?php 
		$total_count = count($arResult["ALL_ITEMS"]);
		$parent_count = count($arResult["MENU_STRUCTURE"]);
		$column_count = 2;
		$items_per_column = floor( $total_count / $column_count );
		
		$i = 0;
		$cur_column = 1;
#echo "<pre>"; print_r( $items_per_column ); echo "</pre>"; exit();
	foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns):      

		$i++;
		$childs_count = 0;
		
		if( is_array($arColumns) )
		{
			foreach($arColumns as $k => $v)
			{
				$childs_count += count( $v );
			}
		}
		else
		{
			unset( $childs_count );
		}	 

		if( $i == 1 )
		{
			?>
	<div class="cat_clmn">
		<ul class="top_catalog">
			<?
		}
		
		if( ( $i + $childs_count ) - ( $cur_column * $items_per_column ) > 3 )
		{
			$cur_column++;
			?>
		</ul>	
	</div> <!-- end column -->
	<div class="cat_clmn">
		<ul class="top_catalog">
			<?
		}			
		?>	
			<li class="parent<?if ($childs_count > 0):?> dropdown<?endif?>"> <!-- first level-->
				<a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>">
					<span class="icon">
						<img src="<?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["icon_src"]?>" alt="">
					</span>
					<span class="menu_item_text">
						<?=$arResult["ALL_ITEMS"][$itemID]["TEXT"]?>
					</span>	
				</a>
			
		<?if ($childs_count > 0):?>
				<?foreach($arColumns as $key=>$arRow):?>
					<ul class="childs" id="ch_<?php echo $i; ?>">
					<?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?> 
					<?php $i++;	?>
					<!-- second level-->
						<li>
							<a href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>">
								<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>
							</a>
						</li>
					<?endforeach;?>
					</ul> <!-- end childs -->
				<?endforeach;?>
		<?php endif; ?>
			</li> <!-- end item -->
			
	<?php if( $i == $total_count ): ?> <!-- total_count  -->
		</ul>
	</div>	
	<?php endif; ?>	
	<?endforeach;?>
</div>	