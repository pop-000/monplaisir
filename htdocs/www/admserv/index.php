<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Служебное");
?> 
<h1> Служебное</h1>
<ul>
<li><a href="goods_list.php">Список товаров</a></li>
<li><a href="math_list.php">Список материалов</a></li>
<li><a href="color_list.php">Список цветов</a></li>
<li><a href="price_add.php">Наценки</a></li>
</ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>