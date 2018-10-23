<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
#echo "<pre>"; print_r($arResult); echo "</pre>";
if( count($arResult["SECTIONS"]) )
{
	foreach($arResult["SECTIONS"] as $sect)
	{
		if( $sect["UF_ICON"] )
		{
			$arImg = CFile::GetFileArray( $sect["UF_ICON"] );
		}
		else
		{
			unset( $arImg );
		}
		?>
		<div class="plate">
			<a href="<?=$sect["SECTION_PAGE_URL"];?>">
				<img src="<?=$arImg["SRC"];?>" alt="<?=$sect["NAME"];?>" title="<?=$sect["NAME"];?>"/>
				<div class="name"><?=$sect["NAME"];?></div>
			</a>	
		</div>
		<?
	}
}
?>