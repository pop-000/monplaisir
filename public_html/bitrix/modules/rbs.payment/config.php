<?
IncludeModuleLangFile(__FILE__);

// !!! ПОЖАЛУЙСТА НЕ ИСПОЛЬЗУЙТЕ В ЭТОМ ФАЙЛЕ КИРРИЛИЧЕСКИЕ СИМВОЛЫ !!!
// !!! PLEASE DO NOT USE INSIDE THIS FILE CYRILLIC SYMBOLS !!!

define('RBS_BANK_NAME', 'Alfabank'); //
define('RBS_MODULE_ID', 'rbs.payment');

define('API_PROD_URL', 'https://engine.paymentgate.ru/payment/rest/');
define('API_TEST_URL', 'https://web.rbsuat.com/ab/rest/');

define('API_RETURN_PAGE', '/sale/payment/result.php');
define('API_GATE_TRY',30);

$status = COption::GetOptionString("rbs.payment", "result_order_status", "P");
if (!defined('RESULT_ORDER_STATUS'))
    define('RESULT_ORDER_STATUS', $status);

$arDefaultIso = array(
    'USD' => 840,
    'EUR' => 978,
    'RUB' => 810,
    'RUR' => 810,
);

if (!defined('DEFAULT_ISO'))
    define(DEFAULT_ISO, serialize($arDefaultIso));