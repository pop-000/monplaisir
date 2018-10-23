<?php
function authorize_or_register($email)
{
	global $USER;
	
	if($USER->IsAuthorized())
	{
		return $USER->GetID();
	}
	
	$rsUsers = CUser::GetList($by="", $order="", array('=EMAIL' => $email));
	while($arUser = $rsUsers->Fetch())
	{
		$id = $arUser["ID"];				
	}
	
	if(!empty($id))
	{	
		/* если введен e-mail администратора, перенаправляем на ввод пароля */
		$arGroups = CUser::GetUserGroup($id);
		$arAdminGroups = array(1, 6, 7);
		$arIntersect = array_intersect($arGroups, $arAdminGroups);
		if(!empty($arIntersect))
		{
			LocalRedirect("/login/");
			exit();
		}
		else
		{
			if($USER->Authorize($id, true))
			{
				return $id;
			}
			else
			{
				return false;
			}
		}
	}
	else
	{
		/*  регистрация */
		$arResult = $USER->SimpleRegister($email);
		$id = $USER->GetID();
		CUser::SendUserInfo($id, SITE_ID, "Приветствуем Вас как нового пользователя нашего сайта!");
		return $id;
	}
}
?>