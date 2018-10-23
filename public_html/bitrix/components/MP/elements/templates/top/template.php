<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 
#$APPLICATION->AddHeadScript('//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js');
#$APPLICATION->SetAdditionalCSS("//cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css");
#echo "<pre>"; print_r($arResult); echo "</pre>";
$path = $_SERVER["DOCUMENT_ROOT"] . $componentPath . "/templates/card/";
$APPLICATION->SetAdditionalCSS( "/bitrix/components/MP/elements/templates/card/element_card.css" );
?>
<h1><?=$arResult["NAME"];?></h1>
<?php
$file = $path . "element_card.phtml";
?>
<div class="catalogue-elements">
<?if( file_exists($file) )
{
	foreach($arResult["ITEMS"] as $EL)
	{
		include $file;
	}
}
else
{	
	echo "Файл не доступен! (" . $file . ")";
}
?>
</div>