<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Список цветов");

if (!CModule::IncludeModule("iblock"))
{
	exit("Блок не загружен");
}
$iblocks = array( 12 => "материал", 13 => "цвет" )
?>
<h1>Наценки <?=$iblocks[$_POST["iblock"]];?></h1>
<form method="post" enctype="multipart/form-data">
	<select name="iblock">
		<option value="">-- Выберите раздел --</option>
		<option value="12">Материалы</option>
		<option value="13">Цвета</option>
	</select>
	<input type="submit" value="Отправить">
</form>
<hr/>
<? if( $_POST["iblock"] && array_key_exists( $_POST["iblock"], $iblocks ) )
{
	$APPLICATION->IncludeComponent(
	"MP:price_add",
	"adm_list",
	array( "IBLOCK_ID" => $_POST["iblock"] ),
	false
	);
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>