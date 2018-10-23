<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<?$APPLICATION->SetAdditionalCSS( $componentPath . "/templates/card/element_card.css");?>
<? #echo "<pre>"; print_r( $arResult ); echo "</pre>"; exit(); ?>

<h1><span class="title"><?=$arResult["NAME"]?></span></h1>

<?if( $arResult["SECTION"]["DESCRIPTION"] ):?>
<div class="sect_descr"><?=$arResult["SECTION"]["DESCRIPTION"]?></div>
<?endif;?>

<?php
$card = $_SERVER["DOCUMENT_ROOT"] . $componentPath . "/templates/card/element_card.phtml";
if( !file_exists( $card ) )
	echo "Файл недоступен!";
?>
<?if( count( $arResult["SECTION"]["ITEMS"] ) )
{
	?>
	<div class="catalogue-elements">
	<?
	foreach( $arResult["SECTION"]["ITEMS"] as $item )
	{
		if( count( $arResult["ITEMS"][$item] ) )
		{
			$EL = $arResult["ITEMS"][$item];
			include( $card );
		}
	}
	?>
	</div>
	<?
}

if( count( $arResult["SECTIONS"] ) )
{
	foreach( $arResult["SECTIONS"] as $SECT )
	{
		if( strlen( $SECT["NAME"] ))
		{
			$lev = $SECT["DEPTH_LEVEL"];
			if( !$lev ) $lev = 2;
			{
				?>
			<h<?=$lev;?>><span class="title"><?=$SECT["NAME"]?></span></h<?=$lev;?>>
				<?
			}
		}
		
		if( $SECT["DESCRIPTION"] )
		{
			?>
		<div class="sect_descr"><?=$SECT["DESCRIPTION"]?></div>
			<?
		}
		?>
		<div class="catalogue-elements">
		<?
		if( count( $SECT["ITEMS"] ) )
		{
			foreach( $SECT["ITEMS"] as $item )
			{
				if( count( $arResult["ITEMS"][$item] ) )
				{
					$EL = $arResult["ITEMS"][$item];
					include( $card );
				}
			}
		}
		?>
		</div>
		<?
	}
}
?>