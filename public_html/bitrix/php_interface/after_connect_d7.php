<?
$connection = \Bitrix\Main\Application::getConnection();
$connection->queryExecute("SET NAMES 'utf8'");
$connection->queryExecute("SET sql_mode='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
$connection->queryExecute('SET collation_connection = "utf8_unicode_ci"');
?>