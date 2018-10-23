$(document).on('ready', function(){
	
	$(".el").mouseenter( function(){
		
		$( this ).find(".el_photo").slick({
		dots: false,
		arrows: false,
		infinite: true,
		slidesToShow: 1,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 200,
		pauseOnHover: false,
		adaptiveHeight: false,
		centerMode: true,
		variableWidth: true
		});

	});
	
	$(".el").mouseleave( function(){
		$( this ).find(".el_photo").slick('unslick');
	});
});