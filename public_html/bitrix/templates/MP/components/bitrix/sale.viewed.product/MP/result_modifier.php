<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$curPage = $APPLICATION->GetCurPage();

foreach($arResult as $key => $val)
{
	if( $val["DETAIL_PAGE_URL"] != $curPage) // исключая текущий продукт
	{
		$img = "";
		if ($val["DETAIL_PICTURE"] > 0)
			$img = $val["DETAIL_PICTURE"];
		elseif ($val["PREVIEW_PICTURE"] > 0)
			$img = $val["PREVIEW_PICTURE"];

		$file = CFile::ResizeImageGet($img, array('width'=>$arParams["VIEWED_IMG_WIDTH"], 'height'=>$arParams["VIEWED_IMG_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);

		$val["PICTURE"] = $file;
		$arResult[$key] = $val;
	}
	else
	{
		unset( $arResult[$key] );
	}
}
?>