<?php 
function auth_or_reg($email)
{
	global $USER;
	
	$rsUsers = CUser::GetList($by="", $order="", array('=EMAIL' => $email));
	$arUser = $rsUsers->Fetch();
	
	if(empty($arUser["id"]))
	{
		$arResult = $USER->SimpleRegister($email);
		$id = $USER->GetID();
		$rsUser = CUser::GetById($id);
		$arUser = $rsUser->Fetch();
		CUser::SendUserInfo($id, SITE_ID, "Приветствуем Вас как нового пользователя нашего сайта!");
	}
	
	if(!empty($arUser["ID"]))
	{
		return $arUser;
	}
	else
	{
		return false;
	}
}
?>