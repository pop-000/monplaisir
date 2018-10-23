<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Список материалов");

if (!CModule::IncludeModule("iblock"))
{
	exit("Блок не загружен");
}
// список разделов
$obSections = CIBlockSection::GetList(
				Array("SORT"=>"ASC"),
				Array("SECTION_ID" => false, "IBLOCK_ID" => 8),
				false,
				Array("ID", "NAME"),
				false
				);
while($arRow = $obSections -> GetNext())
{
	$arSections[] = array(
		"ID" => $arRow["ID"],
		"NAME" => $arRow["NAME"]
	);
	if( $arRow["ID"] == $_POST['section'] )
	{
		$cur_section = $arRow;
	}
}
#echo "<pre>"; print_r($cur_section); echo "</pre>";

?>
<h1>Список материалов</h1>
<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
	<p>Категория: <select name="section">
			<option value="">--Все производители--</option>
			<? foreach($arSections as $s)
			{
			?>
			<option value="<?=$s['ID'];?>" <? if( $s['ID'] == $cur_section["ID"] ) echo "selected=\"selected\""; ?>><?=$s['NAME'];?></option>
			<?
			}
			?>
			</select>
		<input type="submit" name="set" value="Применить" />	
</form>
<?
if( $_POST['section'] )
{
	?>
	<h2>
	<?=$cur_section["NAME"]?>
	</h2>
	<?
	$APPLICATION->IncludeComponent(
	"MP:matherials",
	"adm_list",
	array( "IBLOCK_ID" => 8 ),
	false
	);
}
else
{
	foreach($arSections as $section)
	{
		?>
		<h2><?=$section['NAME'];?></h2>
		<?
		$APPLICATION->IncludeComponent(
		"MP:matherials",
		"adm_list",
		array(
			"IBLOCK_ID" => 8,
			"SECTION_ID" => $section["ID"]
		),
		false
		);	
		echo "<hr />";
	}
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>