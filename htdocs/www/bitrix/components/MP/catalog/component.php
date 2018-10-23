<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#SECTION_CODE#/",
	"element" => "#SECTION_CODE#/#ELEMENT_ID#/",
	"compare" => "compare.php?action=COMPARE",
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"SECTION_ID",
	"SECTION_CODE",
	"ELEMENT_ID",
	"ELEMENT_CODE",
	"action",
);

$arVariables = array();

$engine = new CComponentEngine($this);
if (\Bitrix\Main\Loader::includeModule('iblock'))
{
	$engine->addGreedyPart("#SECTION_CODE_PATH#");
	$engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));
}
$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

$componentPage = $engine->guessComponentPath(
	$arParams["SEF_FOLDER"],
	$arUrlTemplates,
	$arVariables
);

if(!$componentPage && isset($_REQUEST["q"]))
	$componentPage = "search";

$b404 = false;
if(!$componentPage)
{
	$componentPage = "sections";
	$b404 = true;
}

if($componentPage == "section")
{
	if (isset($arVariables["SECTION_ID"]))
		$b404 |= (intval($arVariables["SECTION_ID"])."" !== $arVariables["SECTION_ID"]);
	else
		$b404 |= !isset($arVariables["SECTION_CODE"]);
}

if($b404 && $arParams["SET_STATUS_404"]==="Y")
{
	$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
	if ($folder404 != "/")
		$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
	if (substr($folder404, -1) == "/")
		$folder404 .= "index.php";

		if($folder404 != $APPLICATION->GetCurPage(true))
		CHTTP::SetStatus("404 Not Found");
}

CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
$arResult = array(
	"FOLDER" => $arParams["SEF_FOLDER"],
	"URL_TEMPLATES" => $arUrlTemplates,
	"VARIABLES" => $arVariables,
	"ALIASES" => $arVariableAliases
);

$this->IncludeComponentTemplate($componentPage);
?>
