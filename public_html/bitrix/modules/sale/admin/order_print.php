<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && isset($_REQUEST["CRM_MANAGER_USER_ID"]));

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

ClearVars();
$ID = IntVal($ID);
if ($ID <= 0)
	LocalRedirect("sale_order.php?lang=".LANG.GetFilterParams("filter_", false));

$db_order = CSaleOrder::GetList(Array("ID"=>"DESC"), Array("ID"=>$ID));
if (!$db_order->ExtractFields("str_"))
	LocalRedirect("sale_order.php?lang=".LANG.GetFilterParams("filter_", false));

$APPLICATION->SetTitle(GetMessage("SALE_PRINT_RECORD", array("#ID#"=>$ID)));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $GLOBALS["USER"]->GetUserGroupArray(), $GLOBALS["USER"]->GetID());
$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $GLOBALS["USER"]->GetUserGroupArray());

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($Print)>0 && check_bitrix_sessid() && $bUserCanViewOrder)
{
	if(count($REPORT_ID) > 0)
	{
		$db_basket = CSaleBasket::GetList(array(), array("ORDER_ID"=>$ID));
		$productCountInBasket = $db_basket->SelectedRowsCount();
		$showAll = "N";
		if ($productCountInBasket == count($BASKET_IDS))
			$showAll = "Y";


		if ($showAll == "N")
		{
			$sBasket = "";
			$sQuantity = "";
			$bFirst = True;
			$countBasketId = count($BASKET_IDS);
			for ($i = 0; $i < $countBasketId; $i++)
			{
				if (IntVal($BASKET_IDS[$i])<=0)
					continue;
				$sBasket .= ($bFirst? "": ",").IntVal($BASKET_IDS[$i]);
				$sQuantity .= ($bFirst? "": ",").${"QUANTITY_".IntVal($BASKET_IDS[$i])};
				$bFirst = false;
			}

			$urlParams = "BASKET_IDS=".urlencode($sBasket)."&QUANTITIES=".urlencode($sQuantity);
		}
		else
			$urlParams = "SHOW_ALL=Y";

		$PROPS_ENABLE = (!isset($_POST["PROPS_ENABLE"]) || $_POST["PROPS_ENABLE"] == N) ? "N" : "Y";

		?>
		<script language="JavaScript">
		<?
		$countReportId = count($REPORT_ID);
		for ($i = 0; $i < $countReportId; $i++)
		{
			?>
			window.open('/bitrix/admin/sale_print.php?PROPS_ENABLE=<?=$PROPS_ENABLE?>&doc=<?echo CUtil::JSEscape($REPORT_ID[$i]) ?>&ORDER_ID=<?echo $ID ?>&<?=$urlParams?>', '_blank');
			<?
		}
		?>
		</script>
		<?
	}
	else
		$errorMessage = GetMessage("SOP_ERROR_REPORT");
}

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("SOP_TO_LIST"),
				"LINK" => "/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
			)
	);

$aMenu[] = array("SEPARATOR" => "Y");

if ($bUserCanEditOrder)
{
	$aMenu[] = array(
			"TEXT" => GetMessage("SOP_TO_EDIT"),
			"LINK" => "/bitrix/admin/sale_order_new.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
		);
}

if ($bUserCanViewOrder)
{
	$aMenu[] = array(
			"TEXT" => GetMessage("SOP_TO_DETAIL"),
			"LINK" => "/bitrix/admin/sale_order_detail.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
		);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if (!$bUserCanViewOrder)
{
	CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOD_NO_PERMS2VIEW")).". ");
}
else
{
	CAdminMessage::ShowMessage($errorMessage);
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="order_print">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="lang" value="<?echo LANG ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<?=bitrix_sessid_post()?>

	<?
	$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("SOPN_TAB_PRINT"), "ICON" => "sale", "TITLE" => GetMessage("SOPN_TAB_PRINT_DESCR"))
		);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	?>

	<?
	$tabControl->BeginNextTab();
	?>

		<tr>
			<td><?echo GetMessage("SALE_PR_ORDER_N")?>:</td>
			<td><?echo $ID ?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("P_ORDER_DATE")?>:</td>
			<td><?echo $str_DATE_INSERT_FORMAT ?></td>
		</tr>
		<tr>
			<td><?echo GetMessage("P_ORDER_LANG")?>:</td>
			<td>
				<?
				echo "[".$str_LID."] ";
				$db_lang = CLang::GetByID($str_LID);
				if ($arLang = $db_lang->GetNext())
				{
					echo $arLang["NAME"];
				}
				?>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("P_ORDER_STATUS")?>:</td>
			<td>
				<?$ar_status = CSaleStatus::GetByID($str_STATUS_ID);?>
				[<?echo $ar_status["ID"] ?>] <?echo htmlspecialcharsbx($ar_status["NAME"]) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?echo GetMessage("P_ORDER_CANCELED")?> / <?echo GetMessage("P_ORDER_PAYED") ?> / <?echo GetMessage("P_ORDER_ALLOW_DELIVERY") ?>:
			</td>
			<td>
				<?
				echo (($str_CANCELED=="Y")?"<font color=\"#FF0000\"><b>":"");
				echo (($str_CANCELED=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
				echo (($str_CANCELED=="Y")?"</b>":"");
				?>
				/
				<?
				echo (($str_PAYED=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
				?>
				/
				<?
				echo (($str_ALLOW_DELIVERY=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO") );
				?>

			</td>
		</tr>
		<tr>
			<td><?=GetMessage('SOPN_SELECT_ORDER_PROPS');?>:</td>
			<td><input type="checkbox" name="PROPS_ENABLE" value="Y" checked></td>
		</tr>
		<tr>
			<td colspan="2">
				<table border="0" cellspacing="1" cellpadding="3" width="100%" class="internal">
					<tr class="heading">
						<td><?echo GetMessage("SALE_PR_INCLUDE")?></td>
						<td><?echo GetMessage("SALE_PR_NAME")?></td>
						<td><?echo GetMessage("SALE_PR_QUANTITY")?></td>
						<td><?echo GetMessage("SALE_PR_PRICE")?></td>
						<td><?echo GetMessage("SALE_PR_SUM")?></td>
					</tr>
					<?
					$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ID));
					while ($arBasket = $db_basket->GetNext())
					{
						?>
						<tr>
							<td valign="top" style="text-align:center;">
								<input type="checkbox" checked name="BASKET_IDS[]" value="<?echo $arBasket["ID"] ?>">
							</td>
							<td valign="top">
								<?echo $arBasket["NAME"];?>
								<?
								$dbBasketProps = CSaleBasket::GetPropsList(
										array("SORT" => "ASC", "NAME" => "ASC"),
										array("BASKET_ID" => $arBasket["ID"]),
										false,
										false,
										array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
									);
								while ($arBasketProps = $dbBasketProps->GetNext())
								{
									if(strlen($arBasketProps["VALUE"]) > 0 && $arBasketProps["CODE"] != "CATALOG.XML_ID" && $arBasketProps["CODE"] != "PRODUCT.XML_ID")
										echo "<div style=\"font-size:8pt\">".$arBasketProps["NAME"].": ".$arBasketProps["VALUE"]."</div>";
								}

								$arCurFormat = CCurrencyLang::GetCurrencyFormat($arBasket["CURRENCY"]);
								$vatString = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
								?>
							</td>
							<td valign="top" style="text-align:right;">
								<input type="text" size="3" name="QUANTITY_<?echo $arBasket["ID"] ?>" value="<?echo $arBasket["QUANTITY"];?>">
							</td>
							<td valign="top" nowrap style="text-align:right;">
								<?=number_format($arBasket["PRICE"], 2, ',', ' ')." ".$vatString?>
							</td>
							<td valign="top" nowrap style="text-align:right;">
								<?=number_format($arBasket["QUANTITY"]*$arBasket["PRICE"], 2, ',', ' ')." ".$vatString?>
							</td>
						</tr>
						<?
					}
					?>
				</table>
			</td>
		</tr>
		<?
		$arCurFormat = CCurrencyLang::GetCurrencyFormat($str_CURRENCY);
		$vatString = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));

		$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ID));
		while ($ar_tax_list = $db_tax_list->Fetch())
		{
			?>
			<tr>
				<td align="right" width="50%">
					<?
					echo htmlspecialcharsbx($ar_tax_list["TAX_NAME"]);
					if ($ar_tax_list["IS_IN_PRICE"]=="Y")
						echo " (".(($ar_tax_list["IS_PERCENT"]=="Y")?"".DoubleVal($ar_tax_list["VALUE"])."%, ":"").GetMessage("SALE_TAX_INPRICE").")";
					elseif ($ar_tax_list["IS_PERCENT"]=="Y")
						echo " (".DoubleVal($ar_tax_list["VALUE"])."%)";
					?>:
				</td>
				<td align="left" width="50%">
					<?=number_format($ar_tax_list["VALUE_MONEY"], 2, ',', ' ')." ".$vatString?>
				</td>
			</tr>
			<?
		}
		?>
		<tr>
			<td align="right" width="50%">
				<?echo GetMessage("SALE_F_DELIVERY")?>:
			</td>
			<td align="left" width="50%">
				<?=number_format($str_PRICE_DELIVERY, 2, ',', ' ')." ".$vatString?>
			</td>
		</tr>
		<tr>
			<td align="right" width="50%"><?echo GetMessage("SALE_F_ITOG")?>:</td>
			<td align="left" width="50%">
				<?=number_format($str_PRICE, 2, ',', ' ')." ".$vatString?>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2">&nbsp; </td>
		</tr>

		<tr>
			<td align="right" valign="top"><?echo GetMessage("SALE_PR_SHABLON")?>:<br><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
			<td>
				<select size="5" multiple name="REPORT_ID[]">
					<?
					$arSysLangs = array();
					$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"), array("ACTIVE" => "Y"));
					while ($arLang = $db_lang->Fetch())
						$arSysLangs[] = $arLang["LID"];

					$arReports = array();
					if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/"))
					{
						if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/"))
						{
							while (($file = readdir($handle)) !== false)
							{
								if ($file == "." || $file == "..")
									continue;

								if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file) && ToUpper(substr($file, strlen($file)-4))==".PHP")
								{
									$rep_title = $file;
									$file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file, "rb");
									$file_contents = fread($file_handle, 2500);
									fclose($file_handle);

									$rep_langs = "";
									$arMatches = array();
									if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
									{
										$arMatches[3] = Trim($arMatches[3]);
										if (strlen($arMatches[3])>0) $rep_title = $arMatches[3];
										$arMatches[2] = Trim($arMatches[2]);
										if (strlen($arMatches[2])>0) $rep_langs = $arMatches[2];
									}

									if (strlen($rep_langs)>0)
									{
										$bContinue = True;
										$countarSys = count($arSysLangs);
										for ($ic = 0; $ic < $countarSys; $ic++)
										{
											if (strpos($rep_langs, $arSysLangs[$ic])!==false)
											{
												$bContinue = False;
												break;
											}
										}
										if ($bContinue)
											continue;
									}

									$arReports[] = array(
											"PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/reports/".$file,
											"FILE" => $file,
											"TITLE" => $rep_title
										);
								}
							}
						}
						closedir($handle);
					}

					if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/"))
					{
						while (($file = readdir($handle)) !== false)
						{
							if ($file == "." || $file == "..")
								continue;

							if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file)
								&& !in_array($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file, $arReports)
								&& ToUpper(substr($file, strlen($file)-4))==".PHP")
							{
								$rep_title = $file;
								if (is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file)
									&& ToUpper(substr($file, strlen($file)-4))==".PHP")
								{
									$file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/ru/reports/".$file, "rb");
									$file_contents = fread($file_handle, 2500);
									fclose($file_handle);
								}
								else
								{
									$file_handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file, "rb");
									$file_contents = fread($file_handle, 2500);
									fclose($file_handle);
								}

								$rep_langs = "";
								$arMatches = array();
								if (preg_match("#<title([\s]+langs[\s]*=[\s]*\"([^\"]*)\"|)[\s]*>([^<]*)</title[\s]*>#i", $file_contents, $arMatches))
								{
									$arMatches[3] = Trim($arMatches[3]);
									if (strlen($arMatches[3])>0) $rep_title = $arMatches[3];
									$arMatches[2] = Trim($arMatches[2]);
									if (strlen($arMatches[2])>0) $rep_langs = $arMatches[2];
								}

								if (strlen($rep_langs)>0)
								{
									$bContinue = True;
									$countArSysLang = count($arSysLangs);
									for ($ic = 0; $ic < $countArSysLang; $ic++)
									{
										if (strpos($rep_langs, $arSysLangs[$ic])!==false)
										{
											$bContinue = False;
											break;
										}
									}
									if ($bContinue)
										continue;
								}

								$arReports[] = array(
										"PATH" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/reports/".$file,
										"FILE" => $file,
										"TITLE" => $rep_title
									);
							}
						}
					}
					closedir($handle);

					$countArReport = count($arReports);
					for ($ir = 0; $ir < $countArReport; $ir++):
						?>
						<option value="<?echo substr($arReports[$ir]["FILE"], 0, strlen($arReports[$ir]["FILE"])-4); ?>"><?echo $arReports[$ir]["TITLE"];?></option>
						<?
					endfor;
					?>
				</select>
			</td>
		</tr>

	<?
	$tabControl->EndTab();
	?>

	<?
	$tabControl->Buttons();
	?>
	<input type="hidden" name="Print" value="<?echo GetMessage("SALE_PRINT")?>">
	<?
	if (!$crmMode)
	{
		?><input type="submit" class="button" value="<?echo GetMessage("SALE_PRINT")?>"><?
	}
	?>

	<?
	$tabControl->End();
	?>

	</form>
	<?
}
?>
<br>
<?echo BeginNote();?>
	<?echo GetMessage("SALE_PR_NOTE1")?><br><br>
	<?echo GetMessage("SALE_PR_NOTE2")?><br><br>
	<?echo GetMessage("SALE_PR_NOTE3")?><br><br>
	<?echo GetMessage("SALE_PR_NOTE4")?><br><br>
	<?echo GetMessage("SALE_PR_NOTE5")?>
<?echo EndNote();?>


<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>