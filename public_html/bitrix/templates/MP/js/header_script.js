/**
/* Для мобильных устройств. Перемещение меню каталога (левое с иконками) после меню для мобильного. 
*/
function reposition_left_menu()
{
	if($(window).width() < 426)
	{
		$(".menu-mobile").after($("#main_menu_container"));
	}
	else
	{
		$("header").before($("#main_menu_container"));
	}
}
jQuery(document).ready(function($){
	if($(window).width() < 426)
	{
		/* кнопка меню каталога для мобильных */
		
		$(".mobile-menu-cat").click(function(){
			$(this).toggleClass('opened');
			$("#main_menu_container").toggle();
		});
		reposition_left_menu();
	}
	
	$( window ).resize(function() {
		reposition_left_menu();
	});
});

/*
* Убираем переносы строк в адресе
*/
jQuery(document).ready(function($){
	var w = $( window ).width();
	if( w > 767 ){
		if( w < 1245 ){
			$('.address > br').each( function( key, val ){
				if( key != 1 ){
					$(this).remove();
				}	
			});
		}
		else{
			var txt = $('.address').text();
			$('.address').html(txt);
		}
	}
});