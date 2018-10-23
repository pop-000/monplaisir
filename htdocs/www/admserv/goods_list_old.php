<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;

if (!CModule::IncludeModule("iblock"))
{
	exit("Блок не загружен");
}
$obSections = CIBlockSection::GetList(
				Array("SORT"=>"ASC"),
				Array("SECTION_ID" => false),
				false,
				Array("ID", "NAME"),
				false
				);
while($arRow = $obSections -> GetNext())
{
	$count = 0;
	$arSectGoods = array();
	$obGoods = CIBlockElement::GetList(
				Array("SORT"=>"ASC"),
				Array("SECTION_ID"=>$arRow['ID']),
				false,
				false,
				Array()
				);
	while($row = $obGoods -> Fetch())
	{
		$arSectGoods[] = $row['ID']; 
		$count++;
		$count_all++;
	}
	$arSections[$arRow['ID']] = array("ID" => $arRow['ID'],
						"NAME" => $arRow['NAME'],
						"TEXT" => $arResult["TEXT"],
						"GOODS" => $arSectGoods,
						"COUNT" => $count
						);
}
if($_POST['section'])
{
	$count = $arSections[$_POST['section']]['COUNT'];
}
else
{
	$count = $count_all;
}
$APPLICATION->SetTitle("Список товаров");
?> <h1>Список товаров</h1>
<form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
	<p>Категория: <select name="section">
					<option value="">--Все категории--</option>
					<? foreach($arSections as $s)
					{
					?>
					<option value="<?=$s['ID'];?>" <? if($_POST['section'] == $s['ID']) echo "selected=\"selected\""; ?>><?=$s['NAME'];?></option>
					<?
					}
					?>
					</select>
		<input type="submit" name="set" value="Применить" />	

</form>

<? 
if(!$_POST['section'])
{
	?>
	Всего изделий: 
	<?
	echo $count_all;
}
else
{
	if(!$count)
	{ 
	?>
		Нет изделий 
	<? }else{ ?> 
		Изделий: 
	<?
		echo $count; 
	} 
}
?>


<?
if($_POST['section'] && $count > 0)
{
	?>
	<h2>
	<?=$arSections[$_POST['section']]['NAME'];?>
	</h2>
	<?
	/*
	$APPLICATION->IncludeComponent(
	"MP:elements",
	"section",
	array(
		"SECTION_ID" => $arResult["SECTION_ID"],
		"SHOW_SECTION" => true,
		"COLORS_COUNT" => $arParams["COLORS_COUNT"],
		"COLORS_IMG_SIZE" => $arParams["COLORS_IMG_SIZE"],
	),
	$component
	);
	*/	
	$APPLICATION->IncludeComponent(
	"MP:elements",
	"adm_list",
	array(
		"GOODS_IBLOCK_ID" => 2,
		"ID" => $_POST['section'],
		"GOODS" => $arSections[$_POST['section']]['GOODS'],
		"NAME" => $arSections[$_POST['section']]['NAME'],
		"ADM_LIST" => "Y"
	),
	$component
	);
}
else
{
	foreach($arSections as $section)
	{
		?>
		<h2><?=$section['NAME'];?></h2>
		Изделий: <?=$section['COUNT'];?>
		<?
		if($section['COUNT'] > 0)
		{
			$APPLICATION->IncludeComponent(
			"MP:elements",
			"adm_list",
			array(
				"GOODS_IBLOCK_ID" => 2,
				"ID" => $section['ID'],
				"GOODS" => $section['GOODS'],
				"NAME" => $section['NAME'],
				"ADM_LIST" => "Y",
				"SHOW_ALL" => "Y"
			),
			$component
			);
		}
		echo "<hr />";
	}
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>