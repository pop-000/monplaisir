<?php
function monplaisir_setting()
{
	$setting = array (
	  'utf_mode' => 
	  array (
		'value' => true,
		'readonly' => true,
	  ),
	  'cache_flags' => 
	  array (
		'value' => 
		array (
		  'config_options' => 3600,
		  'site_domain' => 3600,
		),
		'readonly' => false,
	  ),
	  'cookies' => 
	  array (
		'value' => 
		array (
		  'secure' => false,
		  'http_only' => true,
		),
		'readonly' => false,
	  ),
	  'exception_handling' => 
	  array (
		'value' => 
		array (
		  'debug' => true,
		  'handled_errors_types' => 4437,
		  'exception_errors_types' => 4437,
		  'ignore_silence' => false,
		  'assertion_throws_exception' => true,
		  'assertion_error_type' => 256,
		  'log' => NULL,
		),
		'readonly' => false,
		'connections' => 
		  array (
			'value' => 
			array (
			  'default' => 
			  array (
				'className' => '\\Bitrix\\Main\\DB\\MysqlConnection',
				'host' => 'localhost',
				'database' => 'host1461238_mp',
				'login' => 'host1461238_mp',
				'password' => 'hHzcVbcE',
				'options' => 2,
			  ),
			),
			'readonly' => true,
		  ),	
	  )
	); 
	
	if( $_SERVER["SERVER_NAME"] == 'monplaisir.local' )
	  {
		  $setting['connections']['value']['default']['database'] = 'host1461238_mp';
		  $setting['connections']['value']['default']['login'] = 'root';
		  $setting['connections']['value']['default']['password'] = 'easy';
	  }
	return $setting;
}
monplaisir_setting();
