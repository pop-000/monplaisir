        
<?php
/**
 * ДАННЫЕ ДЛЯ ПОДКЛЮЧЕНИЯ К ПЛАТЕЖНОМУ ШЛЮЗУ
 *
 * USERNAME         Логин магазина, полученный при подключении.
 * PASSWORD         Пароль магазина, полученный при подключении.
 * WSDL             Адрес описания веб-сервиса.
 * RETURN_URL       Адрес, на который надо перенаправить пользователя
 *                  в случае успешной оплаты.
 */
define('USERNAME', 'USERNAME');
define('PASSWORD', 'PASSWORD');
define('WSDL', 'https://server/payment/webservices/merchant-ws?wsdl');
define('RETURN_URL', 'http://your.site/ws.php');
  
/**
 * КЛАСС ДЛЯ ВЗАИМОДЕЙСТВИЯ С ПЛАТЕЖНЫМ ШЛЮЗОМ
 * Класс наследуется от стандартного класса SoapClient.
 */
class Gateway extends SoapClient {
     
    /**
     * АВТОРИЗАЦИЯ В ПЛАТЕЖНОМ ШЛЮЗЕ
     * Генерация SOAP-заголовка для WS_Security.
     *
     * ОТВЕТ
     *      SoapHeader      SOAP-заголовок для авторизации
     */
    private function generateWSSecurityHeader() {
        $xml = '
            <wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <wsse:UsernameToken>
                    <wsse:Username>' . USERNAME . '</wsse:Username>
                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . PASSWORD . '</wsse:Password>
                    <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . sha1(mt_rand()) . '</wsse:Nonce>
                </wsse:UsernameToken>
            </wsse:Security>';
         
        return new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', new SoapVar($xml, XSD_ANYXML), true);
    }
 
    /**
     * ВЫЗОВ МЕТОДА ПЛАТЕЖНОГО ШЛЮЗА
     * Переопределение функции SoapClient::__call().
     *
     * ПАРАМЕТРЫ
     *      method      Метод из API.
     *      data        Массив данных.
     *       
     * ОТВЕТ
     *      response    Ответ.
     */   
    public function __call($method, $data) {
        $this->__setSoapHeaders($this->generateWSSecurityHeader()); // Устанавливаем заголовок для авторизации
        return parent::__call($method, $data); // Возвращаем результат метода SoapClient::__call()
    }
}
 
/**
 * ВЫВОД ФОРМЫ НА ЭКРАН
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['orderId'])) {
    echo '
        <form method="post" action="/ws.php">
            <label>Order number</label><br />
            <input type="text" name="orderNumber" /><br />
            <label>Amount</label><br />
            <input type="text" name="amount" /><br />
            <button type="submit">Submit</button>
        </form>
    ';
}
 
/**
 * ОБРАБОТКА ДАННЫХ ИЗ ФОРМЫ
 */
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client = new Gateway(WSDL);
    $data = array('orderParams' => array(
        'returnUrl' => RETURN_URL,
        'merchantOrderNumber' => urlencode($_POST['orderNumber']),
        'amount' => urlencode($_POST['amount'])
    ));
     
    /**
     * РЕГИСТРАЦИЯ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      registerOrder
     *
     * ПАРАМЕТРЫ
     *      merchantOrderNumber     Уникальный идентификатор заказа в магазине.
     *      amount                  Сумма заказа.
     *      returnUrl               Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
     *
     * ОТВЕТ
     *      В случае ошибки:
     *          errorCode           Код ошибки. Список возможных значений приведен в таблице ниже.
     *          errorMessage        Описание ошибки.
     *
     *      В случае успешной регистрации:
     *          orderId             Номер заказа в платежной системе. Уникален в пределах системы.
     *          formUrl             URL платежной формы, на который надо перенаправить браузер клиента.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      1           Заказ с таким номером уже зарегистрирован в системе;
     *                  Неверный номер заказа.
     *      3           Неизвестная (запрещенная) валюта.
     *      4           Отсутствует обязательный параметр запроса.
     *      5           Ошибка значения параметра запроса.
     *      7           Системная ошибка.
     */
    $response = $client->__call('registerOrder', $data);
 
    /**
     * РЕГИСТРАЦИЯ ДВУХСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      registerOrderPreAuth
     *
     * Параметры и ответ точно такие же, как и в предыдущем методе.
     * Необходимо вызывать либо registerOrder, либо registerOrderPreAuth.
     */
//    $response = $client->__call('registerOrderPreAuth', $data);
     
    if ($response->errorCode != 0) { // В случае ошибки вывести ее
        echo 'Ошибка #' . $response->errorCode . ': ' . $response->errorMessage;
    } else { // В случае успеха перенаправить пользователя на платежную форму
        header('Location: ' . $response->formUrl);
        die();
    }
     
}
 
/**
 * ОБРАБОТКА ДАННЫХ ПОСЛЕ ПЛАТЕЖНОЙ ФОРМЫ
 */
else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['orderId'])){
    $client = new Gateway(WSDL);
    $data = array('orderParams' => array('orderId' => $_GET['orderId']));
     
    /**
     * ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
     *      getOrderStatus
     *
     * ПАРАМЕТРЫ
     *      orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *
     * ОТВЕТ
     *      ErrorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *      OrderStatus     По значению этого параметра определяется состояние заказа в платежной системе.
     *                      Список возможных значений приведен в таблице ниже. Отсутствует, если заказ не был найден.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      2           Заказ отклонен по причине ошибки в реквизитах платежа.
     *      5           Доступ запрещён;
     *                  Пользователь должен сменить свой пароль;
     *                  Номер заказа не указан.
     *      6           Неизвестный номер заказа.
     *      7           Системная ошибка.
     *
     *  Статус заказа   Описание
     *      0           Заказ зарегистрирован, но не оплачен.
     *      1           Предавторизованная сумма захолдирована (для двухстадийных платежей).
     *      2           Проведена полная авторизация суммы заказа.
     *      3           Авторизация отменена.
     *      4           По транзакции была проведена операция возврата.
     *      5           Инициирована авторизация через ACS банка-эмитента.
     *      6           Авторизация отклонена.
     */
    $response = $client->__call('getOrderStatus', $data);
     
    // Вывод кода ошибки и статус заказа
    echo '
        <b>Error code:</b> ' . $response->errorCode . '<br />
        <b>Order status:</b> ' . $response->orderStatus . '<br />
    ';
}
 
?>

    
Пример кода на PHP для интеграции со шлюзом по REST: 
php
        
<?php
 
/**
 * ДАННЫЕ ДЛЯ ПОДКЛЮЧЕНИЯ К ПЛАТЕЖНОМУ ШЛЮЗУ
 *
 * USERNAME         Логин магазина, полученный при подключении.
 * PASSWORD         Пароль магазина, полученный при подключении.
 * GATEWAY_URL      Адрес платежного шлюза.
 * RETURN_URL       Адрес, на который надо перенаправить пользователя
 *                  в случае успешной оплаты.
 */
define('USERNAME', 'USERNAME');
define('PASSWORD', 'PASSWORD');
define('GATEWAY_URL', 'https://server/payment/rest/');
define('RETURN_URL', 'http://your.site/rest.php');
 
/**
 * ФУНКЦИЯ ДЛЯ ВЗАИМОДЕЙСТВИЯ С ПЛАТЕЖНЫМ ШЛЮЗОМ
 *
 * Для отправки POST запросов на платежный шлюз используется
 * стандартная библиотека cURL.
 *
 * ПАРАМЕТРЫ
 *      method      Метод из API.
 *      data        Массив данных.
 *
 * ОТВЕТ
 *      response    Ответ.
 */
function gateway($method, $data) {
    $curl = curl_init(); // Инициализируем запрос
    curl_setopt_array($curl, array(
        CURLOPT_URL => GATEWAY_URL.$method, // Полный адрес метода
        CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
        CURLOPT_POST => true, // Метод POST
        CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
    ));
    $response = curl_exec($curl); // Выполняем запрос
     
    $response = json_decode($response, true); // Декодируем из JSON в массив
    curl_close($curl); // Закрываем соединение
    return $response; // Возвращаем ответ
}
 
/**
 * ВЫВОД ФОРМЫ НА ЭКРАН
 */
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['orderId'])) {
    echo '
        <form method="post" action="/rest.php">
            <label>Order number</label><br />
            <input type="text" name="orderNumber" /><br />
            <label>Amount</label><br />
            <input type="text" name="amount" /><br />
            <button type="submit">Submit</button>
        </form>
    ';
}
 
/**
 * ОБРАБОТКА ДАННЫХ ИЗ ФОРМЫ
 */
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = array(
        'userName' => USERNAME,
        'password' => PASSWORD,
        'orderNumber' => urlencode($_POST['orderNumber']),
        'amount' => urlencode($_POST['amount']),
        'returnUrl' => RETURN_URL
    );
 
    /**
     * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      register.do
     *
     * ПАРАМЕТРЫ
     *      userName        Логин магазина.
     *      password        Пароль магазина.
     *      orderNumber     Уникальный идентификатор заказа в магазине.
     *      amount          Сумма заказа в копейках.
     *      returnUrl       Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
     *
     * ОТВЕТ
     *      В случае ошибки:
     *          errorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *          errorMessage    Описание ошибки.
     *
     *      В случае успешной регистрации:
     *          orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *          formUrl         URL платежной формы, на который надо перенаправить браузер клиента.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      1           Заказ с таким номером уже зарегистрирован в системе.
     *      3           Неизвестная (запрещенная) валюта.
     *      4           Отсутствует обязательный параметр запроса.
     *      5           Ошибка значения параметра запроса.
     *      7           Системная ошибка.
     */
    $response = gateway('register.do', $data);
     
    /**
     * ЗАПРОС РЕГИСТРАЦИИ ДВУХСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      registerPreAuth.do
     *
     * Параметры и ответ точно такие же, как и в предыдущем методе.
     * Необходимо вызывать либо register.do, либо registerPreAuth.do.
     */
//    $response = gateway('registerPreAuth.do', $data);
     
    if (isset($response['errorCode'])) { // В случае ошибки вывести ее
        echo 'Ошибка #' . $response['errorCode'] . ': ' . $response['errorMessage'];
    } else { // В случае успеха перенаправить пользователя на платежную форму
        header('Location: ' . $response['formUrl']);
        die();
    }
}
 
/**
 * ОБРАБОТКА ДАННЫХ ПОСЛЕ ПЛАТЕЖНОЙ ФОРМЫ
 */
else if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['orderId'])){
    $data = array(
        'userName' => USERNAME,
        'password' => PASSWORD,
        'orderId' => $_GET['orderId']
    );
     
    /**
     * ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
     *      getOrderStatus.do
     *
     * ПАРАМЕТРЫ
     *      userName        Логин магазина.
     *      password        Пароль магазина.
     *      orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *
     * ОТВЕТ
     *      ErrorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *      OrderStatus     По значению этого параметра определяется состояние заказа в платежной системе.
     *                      Список возможных значений приведен в таблице ниже. Отсутствует, если заказ не был найден.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      2           Заказ отклонен по причине ошибки в реквизитах платежа.
     *      5           Доступ запрещён;
     *                  Пользователь должен сменить свой пароль;
     *                  Номер заказа не указан.
     *      6           Неизвестный номер заказа.
     *      7           Системная ошибка.
     *
     *  Статус заказа   Описание
     *      0           Заказ зарегистрирован, но не оплачен.
     *      1           Предавторизованная сумма захолдирована (для двухстадийных платежей).
     *      2           Проведена полная авторизация суммы заказа.
     *      3           Авторизация отменена.
     *      4           По транзакции была проведена операция возврата.
     *      5           Инициирована авторизация через ACS банка-эмитента.
     *      6           Авторизация отклонена.
     */
    $response = gateway('getOrderStatus.do', $data);
     
    // Вывод кода ошибки и статус заказа
    echo '
        <b>Error code:</b> ' . $response['ErrorCode'] . '<br />
        <b>Order status:</b> ' . $response['OrderStatus'] . '<br />
    ';
}
?>

    