<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

CModule::IncludeModule('sale');

//echo "<pre>"; print_r( $arBasketItems ); echo "</pre>";

/*---------- recaptcha ---------*/
$data = array(
	"secret" => '6Ld68E4UAAAAAIIpO4i56gMpeq183MCGYUDvd3vF',
	"response" => $_POST['g-recaptcha-response'],
	"remoteip" => $_SERVER['REMOTE_ADDR']
);

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';

$data = http_build_query($data);

$context_options = array (
        'http' => array (
            'method' => 'POST',
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
            )
        );

$context = stream_context_create($context_options);
$result = file_get_contents($recaptcha_url, false, $context);
$result = json_decode ( $result, true );
$recaptcha = $result['success'];

/* -------- end recaptcha -------- */

if( $_POST["fio"] && $_POST["question"] && $_POST["email"] )
{
	$message = '<html>';
	$message .= '<head></head>';
	$message .= '<body>';

	$message .= '<h2>Вопрос с сайта</h2>' . PHP_EOL;
	$message .= '<p><strong>Как обращаться: </strong>' . $_POST["fio"] . '</p>' . PHP_EOL;
	$message .= '<p><strong>E-mail: </strong>' . $_POST["email"] . '</p>' . PHP_EOL;

	if( $_POST["phone"] )
	{
		$message .= '<p><strong>Телефон: </strong>' . $_POST["phone"] . '</p>' . PHP_EOL;
	}
	$message .= '<p><strong>Вопрос: </strong><br />' . $_POST["question"] . '</p>' . PHP_EOL;
	$message .= '</body>';
	$message .= '</html>';	
}


function send_to( $message ){
	$message = wordwrap($message, 70, "\r\n");

	$to      = 'monplezir-mebel@mail.ru';
#	$to      = 'order@monplaisir-mebel.ru';
#	$to      = 'pop-000@mail.ru';
	$subject = 'Вопрос с сайта';
	$message = $message;
	$headers = 'From: webservice@monplaisir-mebel.ru' . "\r\n" .
		'Reply-To: webservice@monplaisir-mebel.ru' . "\r\n" .
		"Content-Type: text/html; charset=UTF-8\r\n" .
		'X-Mailer: PHP/' . phpversion();

	$send_res = mail($to, $subject, $message, $headers);
	return $send_res;
}

if( !empty( $recaptcha ) )
{
	if( !empty( $message ) )
	{
		$send_res = send_to( $message );
	}
}

if( $send_res )
{
	$msg = "Ваш вопрос отправлен";
}
else
{
	$msg = "При отправке вопроса возникла ошибка.";
}
?>

<h1>Ваш вопрос</h1>
<p><?=$msg;?></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>