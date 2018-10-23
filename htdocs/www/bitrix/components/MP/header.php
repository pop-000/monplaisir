<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".SITE_TEMPLATE_ID."/header.php");
CJSCore::Init(array("fx"));
$curPage = $APPLICATION->GetCurPage(true);
include($_SERVER["DOCUMENT_ROOT"] . "/constants/constants.php");
if( $curPage === SITE_DIR."index.php" ) $homepage = true;
?>
<!DOCTYPE html>
<html xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="<?=SITE_DIR?>favicon.ico" />
	<link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH;?>/fonts/font-awesome.css" />
	<script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
	<script src="<?=SITE_TEMPLATE_PATH;?>/js/header_script.js"></script>
	<?$APPLICATION->ShowHead();?>
	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body>
	<div id="panel"><?$APPLICATION->ShowPanel();?></div>
	<?php if(!strpos($curPage, 'cart')):?>
	<div id="mini_basket" class="not-mobile">
	
	<?$APPLICATION->IncludeComponent("MP:sale.basket.basket", "line", array(
			"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
			"PATH_TO_PERSONAL" => SITE_DIR."personal/",
			"SHOW_PERSONAL_LINK" => "N",
			"SHOW_NUM_PRODUCTS" => "Y",
			"SHOW_TOTAL_PRICE" => "Y",
			"SHOW_PRODUCTS" => "N",
			"POSITION_FIXED" =>"N",
			"SHOW_AUTHOR" => "Y",
			"PATH_TO_REGISTER" => SITE_DIR."personal/auth/",
			"PATH_TO_PROFILE" => SITE_DIR."personal/profile/"
		),
		true,
		array()
	);?>
	</div>
	<?php endif; ?>
	<?$APPLICATION->IncludeComponent("bitrix:menu", "main_left", array(
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
	<header>
		<div class="wrap wrap-indent top_plate">
			<a href="<?=SITE_DIR?>" class="logo"></a>
			<? // ---------- mobile --------- // ?>
			<div class="mobile menu-mobile">
				<button class="mobile-menu-cat"></button>
				<a href="tel:78122071049" class="mobile-menu-phone"></a>
				<?$APPLICATION->IncludeComponent("MP:sale.basket.basket.line", "line", array(
						"PATH_TO_BASKET" => SITE_DIR."personal/cart/",
						"PATH_TO_PERSONAL" => SITE_DIR."personal/",
						"SHOW_PERSONAL_LINK" => "N",
						"SHOW_NUM_PRODUCTS" => "Y",
						"SHOW_TOTAL_PRICE" => "Y",
						"SHOW_PRODUCTS" => "N",
						"POSITION_FIXED" =>"N",
						"SHOW_AUTHOR" => "Y",
						"PATH_TO_REGISTER" => SITE_DIR."login/",
						"PATH_TO_PROFILE" => SITE_DIR."personal/"
					),
					true,
					array()
				);?>
			</div>
			<? // ---------- end mobile --------- // ?>
			<nav class="top_menu">
				<?$APPLICATION->IncludeComponent("bitrix:menu", "top_line", Array(
					"ROOT_MENU_TYPE" => "top",	// Тип меню для первого уровня
					"MENU_CACHE_TYPE" => "A",	// Тип кеширования
					"MENU_CACHE_TIME" => "36000000",	// Время кеширования (сек.)
					"MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
					"CACHE_SELECTED_ITEMS" => "N",
					"MENU_CACHE_GET_VARS" => "",	// Значимые переменные запроса
					"MAX_LEVEL" => "1",	// Уровень вложенности меню
					"CHILD_MENU_TYPE" => "left",	// Тип меню для остальных уровней
					"USE_EXT" => "Y",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
					"DELAY" => "N",	// Откладывать выполнение шаблона меню
					"ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
					),
					false
				);?>
			</nav>
			<div class="contacts-info">
				<div class="shedule">
					<?$APPLICATION->IncludeFile( SITE_DIR."include/shedule.php");?>
				</div>				
				<div class="phone">
					<?$APPLICATION->IncludeFile( SITE_DIR."include/telephone_link.php");?>
					<div class="address">
					<?$APPLICATION->IncludeFile( SITE_DIR."include/address.php");?>
					</div>		
				</div>	
			</div>
		</div>
	</header>
	<div class="wrap wrap-indent">
		<?php if( $homepage ): 
		$APPLICATION->IncludeComponent(
				"MP:carousel",
				"slick",
				Array()
			);
		endif; ?>

		<main>
		<?php if( !$homepage ){ 
			if( strpos($curPage, "catalog") )
			{
				$APPLICATION->IncludeComponent(
					"MP:breadcrumbs",
					"mp", 
					array( 
						"START" => array( 
							"URL" => "catalog", 
							"NAME" => "Каталог" 
						),			
					)
				);
			}

		}?>					