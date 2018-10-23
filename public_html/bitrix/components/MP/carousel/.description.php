<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("VM_IBLOCK_BANERS_NAME"),
	"DESCRIPTION" => GetMessage("VM_IBLOCK_BANERS_TEMPLATE_DESCRIPTION"),
	"ICON" => "/images/baners.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 90,
	"PATH" => array(
		"ID" => GetMessage("VM_IBLOCK_GROUP_NAME"),
		"CHILD" => array(
			"ID" => "MP_actions",
			"NAME" => GetMessage("IBLOCK_BANER_GROUP_NAME"),
			"SORT" => 30,
		)
	),
);

?>