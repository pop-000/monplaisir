<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$module_id = "catalog";

if ($USER->CanDoOperation('catalog_read')) :

	if ($ex = $APPLICATION->GetException())
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$strError = $ex->GetString();
		ShowError($strError);

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}

	IncludeModuleLangFile(__FILE__);

	if(CModule::IncludeModule("iblock")):

		$arIBlockType = array(
			"-" => GetMessage("CAT_1C_CREATE"),
		);
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while ($arr=$rsIBlockType->Fetch())
		{
			if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			{
				$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
			}
		}

		$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
		$arSites = array(
			"-" => GetMessage("CAT_1C_CURRENT"),
		);
		while ($arSite = $rsSite->GetNext())
		{
			$arSites[$arSite["LID"]] = $arSite["NAME"];
		}

		$arUGroupsEx = Array();
		$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
		while($arUGroups = $dbUGroups -> Fetch())
		{
			$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
		}

		$arAction = array(
			"N" => GetMessage("CAT_1C_NONE"),
			"A" => GetMessage("CAT_1C_DEACTIVATE"),
			"D" => GetMessage("CAT_1C_DELETE"),
		);

		$arBaseOptions = array(
			array("1C_IBLOCK_TYPE", GetMessage("CAT_1C_IBLOCK_TYPE"), "-", Array("list", $arIBlockType)),
			array("1C_SITE_LIST", GetMessage("CAT_1C_SITE_LIST"), "-", Array("list", $arSites)),
			array("1C_GROUP_PERMISSIONS", GetMessage("CAT_1C_GROUP_PERMISSIONS"), "-", Array("mlist", 5, $arUGroupsEx)),
			array("1C_USE_OFFERS", GetMessage("CAT_1C_USE_OFFERS_2"), "N", Array("checkbox")),
			array("1C_TRANSLIT_ON_ADD", GetMessage("CAT_1C_TRANSLIT_ON_ADD_2"), "N", Array("checkbox")),
			array("1C_TRANSLIT_ON_UPDATE", GetMessage("CAT_1C_TRANSLIT_ON_UPDATE_2"), "N", Array("checkbox")),
		);

		$arExtOptions = array(
			array("1C_INTERVAL", GetMessage("CAT_1C_INTERVAL"), "30", Array("text", 20)),
			array("1C_FILE_SIZE_LIMIT", GetMessage("CAT_1C_FILE_SIZE_LIMIT"), 200*1024, Array("text", 20)),
			array("1C_USE_ZIP", GetMessage("CAT_1C_USE_ZIP"), "Y", Array("checkbox")),
			array("1C_USE_CRC", GetMessage("CAT_1C_USE_CRC"), "Y", Array("checkbox")),
			array("1C_ELEMENT_ACTION", GetMessage("CAT_1C_ELEMENT_ACTION_2"), "D", Array("list", $arAction)),
			array("1C_SECTION_ACTION", GetMessage("CAT_1C_SECTION_ACTION_2"), "D", Array("list", $arAction)),
			array("1C_FORCE_OFFERS", GetMessage("CAT_1C_FORCE_OFFERS_2"), "N", Array("checkbox")),
			array("1C_USE_IBLOCK_TYPE_ID", GetMessage("CAT_1C_USE_IBLOCK_TYPE_ID"), "N", Array("checkbox")),
			array("1C_SKIP_ROOT_SECTION", GetMessage("CAT_1C_SKIP_ROOT_SECTION_2"), "N", Array("checkbox")),
			array("1C_USE_IBLOCK_PICTURE_SETTINGS", GetMessage("CAT_1C_USE_IBLOCK_PICTURE_SETTINGS"), "N", Array("checkbox")),
			array("1C_GENERATE_PREVIEW", GetMessage("CAT_1C_GENERATE_PREVIEW"), "Y", Array("checkbox")),
			array("1C_PREVIEW_WIDTH", GetMessage("CAT_1C_PREVIEW_WIDTH"), 100, Array("text", 20)),
			array("1C_PREVIEW_HEIGHT", GetMessage("CAT_1C_PREVIEW_HEIGHT"), 100, Array("text", 20)),
			array("1C_DETAIL_RESIZE", GetMessage("CAT_1C_DETAIL_RESIZE"), "Y", Array("checkbox")),
			array("1C_DETAIL_WIDTH", GetMessage("CAT_1C_DETAIL_WIDTH"), 300, Array("text", 20)),
			array("1C_DETAIL_HEIGHT", GetMessage("CAT_1C_DETAIL_HEIGHT"), 300, Array("text", 20)),
		);

		$arOptionsDeps = array(
			"1C_USE_IBLOCK_PICTURE_SETTINGS" => array(
				"1C_GENERATE_PREVIEW",
				"1C_PREVIEW_WIDTH",
				"1C_PREVIEW_HEIGHT",
				"1C_DETAIL_RESIZE",
				"1C_DETAIL_WIDTH",
				"1C_DETAIL_HEIGHT",
			),
		);

		if($_SERVER['REQUEST_METHOD'] == "POST" && strlen($Update)>0 && $USER->CanDoOperation('edit_php') && check_bitrix_sessid())
		{
			foreach ($arBaseOptions as $Option)
			{
				$name = $Option[0];
				$val = $_REQUEST[$name];
				if ($Option[3][0] == "checkbox" && $val != "Y")
					$val = "N";
				if ($Option[3][0] == "mlist")
					$val = implode(",", $val);
				COption::SetOptionString("catalog", $name, $val, $Option[1]);
			}

			foreach ($arExtOptions as $Option)
			{
				$name = $Option[0];
				$val = $_REQUEST[$name];
				if ($Option[3][0] == "checkbox" && $val != "Y")
					$val = "N";
				if ($Option[3][0] == "mlist")
					$val = implode(",", $val);
				COption::SetOptionString("catalog", $name, $val, $Option[1]);
			}

			return;
		}

		$showExtOptions = false;
		foreach($arExtOptions as $Option)
		{
			$val = COption::GetOptionString("catalog", $Option[0], $Option[2]);
			if ($val != $Option[2])
				$showExtOptions = true;
		}

		foreach($arBaseOptions as $Option)
		{
			$val = COption::GetOptionString("catalog", $Option[0], $Option[2]);
			$type = $Option[3];
			$strOptionName = htmlspecialcharsbx($Option[0]);
			?>
		<tr>
			<td <? echo ('textarea' == $type[0] || 'mlist' == $type[0] ? 'valign="top"' : ''); ?> width="40%"><?	if($type[0]=="checkbox")
							echo '<label for="'.$strOptionName.'">'.$Option[1].'</label>';
						else
							echo $Option[1];?>:</td>
			<td width="60%">
					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>" value="Y"<?if($val=="Y")echo" checked";?> onclick="Check(this.id);">
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?elseif($type[0]=="list"):?>
						<select name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>">
						<?foreach($type[1] as $key=>$value):?>
							<option value="<?echo htmlspecialcharsbx($key)?>" <?if($val==$key) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
						<?endforeach?>
						</select>
					<?elseif($type[0]=="mlist"):
						$val = explode(",", $val)?>
						<select multiple name="<?echo $strOptionName; ?>[]" size="<?echo $type[1]?>" id="<?echo $strOptionName; ?>">
						<?foreach($type[2] as $key=>$value):?>
							<option value="<?echo htmlspecialcharsbx($key)?>" <?if(in_array($key, $val)) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
						<?endforeach?>
						</select>
					<?endif?>
			</td>
		</tr>
		<?
		}
		?>
		<tr class="heading">
			<td id="td_extended_options" colspan="2">
				<?if ($showExtOptions):?>
					<?echo GetMessage("CAT_1C_EXTENDED_SETTINGS")?>
				<?else:?>
					<a class="bx-action-href" href="javascript:showExtOptions()"><?echo GetMessage("CAT_1C_EXTENDED_SETTINGS")?></a>
				<?endif;?>
			</td>
		</tr>
		<?
		foreach($arExtOptions as $Option)
		{
			$val = COption::GetOptionString("catalog", $Option[0], $Option[2]);
			$type = $Option[3];
			$strOptionName = htmlspecialcharsbx($Option[0]);
			?>
		<tr id="tr_<?echo htmlspecialcharsbx($Option[0])?>" <?if (!$showExtOptions) echo 'style="display:none"'?>>
			<td <? echo ('textarea' == $type[0] || 'mlist' == $type[0] ? 'valign="top"' : ''); ?> width="40%"><?	if($type[0]=="checkbox")
							echo '<label for="'.$strOptionName.'">'.$Option[1].'</label>';
						else
							echo $Option[1];?>:</td>
			<td width="60%">
					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>" value="Y"<?if($val=="Y")echo" checked";?> onclick="Check(this.id);">
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?elseif($type[0]=="list"):?>
						<select name="<?echo $strOptionName; ?>" id="<?echo $strOptionName; ?>">
						<?foreach($type[1] as $key=>$value):?>
							<option value="<?echo htmlspecialcharsbx($key)?>" <?if($val==$key) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
						<?endforeach?>
						</select>
					<?elseif($type[0]=="mlist"):
						$val = explode(",", $val)?>
						<select multiple name="<?echo $strOptionName; ?>[]" size="<?echo $type[1]?>" id="<?echo $strOptionName; ?>">
						<?foreach($type[2] as $key=>$value):?>
							<option value="<?echo htmlspecialcharsbx($key)?>" <?if(in_array($key, $val)) echo "selected"?>><?echo htmlspecialcharsbx($value)?></option>
						<?endforeach?>
						</select>
					<?endif?>
			</td>
		</tr>
		<?
		}

		?>
	<script type="text/javascript">
	var controls = <?echo CUtil::PhpToJSObject($arOptionsDeps)?>;
	function Check(checkbox)
	{
		if(controls[checkbox] != null)
		{
			for(var i=0;i<controls[checkbox].length;i++)
				if(document.getElementById(checkbox).checked)
					document.getElementById(controls[checkbox][i]).disabled = true
				else
					document.getElementById(controls[checkbox][i]).disabled = false
		}
	}
	var bExtOptions = <?echo $showExtOptions? 'true': 'false'?>;
	function showExtOptions()
	{
		if (bExtOptions)
		{
		<?foreach($arExtOptions as $Option):?>
			BX('<?echo CUtil::JSEscape('tr_'.$Option[0])?>').style.display = 'none';
		<?endforeach;?>
		}
		else
		{
		<?foreach($arExtOptions as $Option):?>
			BX('<?echo CUtil::JSEscape('tr_'.$Option[0])?>').style.display = 'table-row';
		<?endforeach;?>
		}
		bExtOptions = !bExtOptions;
		BX.onCustomEvent('onAdminTabsChange');
	}
		<?foreach($arOptionsDeps as $key => $value):?>
			Check('<?echo CUtil::JSEscape($key)?>');
		<?endforeach;?>
	</script>
		<?

	else:
		CAdminMessage::ShowMessage(GetMessage("CAT_NO_IBLOCK_MOD"));
	endif;

endif;
?>