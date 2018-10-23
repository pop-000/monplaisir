<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
		</main>
		<div id="viewed">
			<?$APPLICATION->IncludeComponent(
				"bitrix:sale.viewed.product",
					"MP",
					Array(
						"VIEWED_COUNT" => "7",
						"VIEWED_NAME" => "Y",
						"VIEWED_IMAGE" => "Y",
						"VIEWED_PRICE" => "Y",
						"VIEWED_CURRENCY" => "default",
						"VIEWED_CANBUY" => "N",
						"VIEWED_CANBASKET" => "N",
						"VIEWED_IMG_HEIGHT" => "100",
						"VIEWED_IMG_WIDTH" => "100",
						"BASKET_URL" => "/personal/cart/",
						"ACTION_VARIABLE" => "action",
						"PRODUCT_ID_VARIABLE" => "id",
						"SET_TITLE" => "N"
					)
				);?>
		</div>
	</div> <!-- wrap -->
	<footer>
		<div class="wrap">
			<div class="row">
				<div class="clmn column-left">
					<a href="<?=SITE_DIR?>" class="logo"></a>
					<div>
						<div class="phone"><?$APPLICATION->IncludeFile( SITE_DIR."include/telephone_link.php");?></div>
						<div class="shedule"><?$APPLICATION->IncludeFile( SITE_DIR."include/shedule.php");?></div>
					</div>
				</div>
				<div class="clmn column-center">
					<h4><?$APPLICATION->IncludeFile( SITE_DIR."include/catalog_title.php");?></h4>
					<div class="column-2">
					<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom_menu", array(
							"ROOT_MENU_TYPE" => "left",
							"MENU_CACHE_TYPE" => "A",
							"MENU_CACHE_TIME" => "36000000",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(
							),
							"CACHE_SELECTED_ITEMS" => "N",
							"MAX_LEVEL" => "1",
							"USE_EXT" => "Y",
							"DELAY" => "N",
							"ALLOW_MULTI_SELECT" => "N"
						),
						false
					);?>
					</div>
				</div>
				<div class="clmn column-right">
					<h4><?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/about_title.php"), false);?></h4>
					<?$APPLICATION->IncludeComponent("bitrix:menu", "bottom_menu", array(
							"ROOT_MENU_TYPE" => "bottom",
							"MAX_LEVEL" => "1",
							"MENU_CACHE_TYPE" => "A",
							"CACHE_SELECTED_ITEMS" => "N",
							"MENU_CACHE_TIME" => "36000000",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(
							),
						),
						false
					);?>
				</div>
			</div>
			<div class="row payment_logo">Мы принимаем к оплате:&emsp;
					<img src="<?=SITE_TEMPLATE_PATH?>/images/Mastercard.png" />
					<img src="<?=SITE_TEMPLATE_PATH?>/images/Visa.png" />
					<img src="<?=SITE_TEMPLATE_PATH?>/images/mir.png" />
			</div>
			<br clear="all">
			<div class="bottomline">
				<?$APPLICATION->IncludeFile( SITE_DIR."include/copyright.php" );?>
			</div>
		</div>	
	</footer>
</body>
</html>