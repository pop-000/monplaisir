<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Наши координаты");
$APPLICATION->AddHeadScript('https://www.google.com/recaptcha/api.js', 'async defer');
?> 
<h1>Контакты</h1>
 
<p><b>Телефон:</b> +7 (812) 207 10 49       
  <br />
 	<b>Адрес:</b> Санкт-Петербург, МЦ &laquo;Гранд Каньон&raquo;, 
  <br />
 ул Шостаковича, д 8 , лит А, 2 этаж, секция 203.</p>
 
<p>Режим работы: 11.00-21.00</p>

<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9472.703710664779!2d30.327087256992744!3d60.05832709847555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x469634c4d7ffd6b3%3A0x9d9f15586e5d7e5c!2z0YPQuy4g0KjQvtGB0YLQsNC60L7QstC40YfQsCwgOCwg0KHQsNC90LrRgi3Qn9C10YLQtdGA0LHRg9GA0LMsIDE5NDM1OA!5e0!3m2!1sru!2sru!4v1499695715057" width="600" height="450" frameborder="0" style="border: 0px;" allowfullscreen=""></iframe></p>
 
<h2>Задать вопрос</h2>
<form name="feedback" method="post" action="send.php">
	<p>*Как к вам обращаться:<br />
		<input type="text" name="fio" class="field-big" required /></p>
	
	<p>Телефон:<br />
	<input type="text" name="phone" class="field-big" /></p>
	
<p>*E-mail:<br />
	<input type="email" name="email" class="field-big" required /></p>
	
	<p>*Ваш вопрос:</p>
	<textarea name="question" rows="4" cols="50" required></textarea>
	<hr />
	<div class="g-recaptcha" data-sitekey="6Ld68E4UAAAAAP2hVp2Ue52Rt18infytE0J8zYGT"></div>
	<input type="submit" name="submit" value="Отправить" />
	
</form><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>