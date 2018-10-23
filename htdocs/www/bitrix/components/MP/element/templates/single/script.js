function show_el( id ){
	$( "#" + id ).css( "display", "block" );
	$( '.mask' ).css( "display", "block" );
}

function hide(){
	$( '.prop_items' ).css( "display", "none" );
	$( '#contmenu' ).css( "display", "none" );
	$( '.mask' ).css( "display", "none" );
}

function select_math( id ){
	
	$( "#browse" ).css( "display", "none" );

	var el = $( "#" + id );
	var sect = id.substring( 0, 1 );
	var elem_id = id.substring( 2, id.length );
	
	var href = $( el ).children( "a" ).attr( "href" );
	var img = $( el ).find( "img" );
	var title = img.attr( "title" );
	var src = img.attr( "src" );
	var price = $( el ).find( ".prop_price_add_val" ).text();
	
	var dest = $( "#" + sect + '_prop_sel_el' );
	var dest_link = dest.find( "a" );
	var dest_img = dest.find( "img" );
	var dest_name = dest.find( ".prop_element_name" );
	var dest_price = dest.find( ".prop_price_add_val" );
	
	dest.attr( "rel", id );
	dest_link.attr( "href", href );
	dest_img.attr( "src", src );
	dest_img.attr( "alt", title );
	dest_img.attr( "title", title );
	dest_name.text( title );

	/* ============ установка цен ================= */
	var price_main = $( "#price_main" );
	var price_fin_new = Number( price_main.text() );
	var calc =  $( ".price_add" ).text();
	
	/* если есть наценка */
	if( price.length )
	{
		var insert_price = '<span class="price_add" id="price_add_' + sect + '"> + ' + price + '</span>';
		
		/* если есть калькуляция */
		if( calc )
		{
			var price_sect = $( "#price_add_" + sect );
			/* если раздел уже используется */
			
			if( price_sect.length )
			{
				price_sect.text( " + " + price );
			}
			else
			{
			/* ----------- материал или цвет? ------ */
				if( sect == "m"){
					$( "#price_calculate" ).prepend( insert_price );
				}else{
					$( "#price_add_m" ).after( insert_price );
				}
			}
		}
		else
		{
			$( "#price_main" ).after( insert_price );
		}
	}
	else
	{
		$( "#price_add_" + sect ).remove();
	}
	
	$( ".price_add" ).each( function( k, v ){

		var pr_add = $(v).text();
		pr_add = pr_add.substr( 2, pr_add.length );
		price_fin_new += Number(pr_add);
		
	});

	if( !$( ".price_add" ).length )
	{
		$( "#price_calculate" ).css( "display", "none" );
	}
	
	var price_fin_new_formatted = separate_thousand(String(price_fin_new));
	$("#price_fin").text(price_fin_new_formatted);
	$("#calculate_price_fin").text(price_fin_new);

	/* ============ конец установка цен ================= */
	
	$( "#prop_items_" + sect ).children( ".selected" ).removeClass("selected");
	$( "#" + id ).addClass("selected");

	hide();
	reset_button();
}

function browse_math( id ){
	$( '.mask' ).css( "display", "block" );

	var sect = id.substring( 0, 1 );
	var elem_id = id.substring( 2, id.length );
	var start = elem_id + '_' + sect;
	$( 'body' ).append( '<div id="browse"></div>' );
	$( "#prop_items_" + sect ).children( ".prop_element" ).each( function(){
		var el_id = $( this ).attr( "id" );
		var el_sect = el_id.substring( 0, 1 );
		var el_idN = el_id.substring( 2, el_id.length );
		var href = $( this ).children( "a" ).attr( "href" );
		var img = $( this ).find( "img" );
		var title = img.attr( "title" );
		var src = img.attr( "src" );
		
		var elem = '<div class="browse_el">';
		elem += '<a href="' + href + '">';
		elem += '<img src="' + src + '"/>';
		elem += '</a>';
		elem += '<span class="browse_el_name">';
		elem += title;
		elem += '</span>';
		elem += '<button class="browse_el_btn" onclick="select_math(' + el_id + ')">';
		elem += 'Выбрать';
		elem += '</button>';
		elem += '</div>';
		
		$( "#browse" ).append( elem );
	});
	$( '.prop_items' ).css( "display", "none" );
	$( '#contmenu' ).css( "display", "none" );
	$('#browse').slick({
		slidesToShow: 1,
		slidesToScroll: 1
	});
}

function animate_basket(){
	var src = $("#main_photo").offset();
	var dest = $("#basket_mini_pict").offset();

	$("#main_photo").clone().appendTo(document.body).attr("id", "anim");
	var anim = $("#anim");
	anim.css({
		position: 'absolute',
		top : src.top,
		left : src.left,
		width: $("#main_photo").width(),
		height: $("#main_photo").height(),
	});
	var ratio = anim.width() / anim.height();
	var dest_size = 50;
	if( ratio > 1 )
	{
		var dest_width = dest_size;
		var dest_height = Math.floor( dest_size / ratio );
	}
	else
	{
		var dest_width = Math.floor( dest_size * ratio );
		var dest_height = dest_size;
	}
	
	anim.animate({ 
		left: dest.left,
		top: dest.top,
		width: dest_width,
		height: dest_height
		}, 
		750, 
		"swing", 
		function() {
			$( this ).remove();
		});
		
}

function update_basket(basket_id){

	var fuser_id = $("#fuser_id").text();
	var sel_m = $("#m_prop_sel_el").attr("rel").substr(2);
	var sel_c = $("#c_prop_sel_el").attr("rel").substr(2);
	var basket_json = $("#basket").text();

	if(basket_json.length > 0)
	{
		var basket = JSON.parse(basket_json);
	}
	else
	{
		var basket = {};
		if($("#basket").length == 0)
		{
			$("#buy_link").after('<div id="basket" style="display:none">');
		}		
	}
	
	basket[basket_id] = {"COLORS":sel_c, "MATHERIALS":sel_m};
	$("#basket").text(JSON.stringify(basket));
	
	$.ajax({
			type: "POST",
			url: "/personal/cart/update.php",
			dataType: "text",
			data: { 
				"FUSER_ID": fuser_id		
				},
			success: function(data){
				var arr = data.split(',');
				if( arr[1] > 0 )
				{
					$( "#basket_mini_count" ).text( arr[1] );
					var sum = separate_thousand( arr[0] );
					$( "#basket_mini_sum" ).html( sum + ' <span class="small-x">руб.</span>' );
					reset_button();
				}
			},
		});	
}

function separate_thousand(str){
	if(str.length > 0)
	{
		var sep = str.replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
		return sep;
	}
	
}

function reset_button()
{
	var sel_m = $("#m_prop_sel_el").attr("rel").substr(2);
	var sel_c = $("#c_prop_sel_el").attr("rel").substr(2);
	/*
	var set_m = $("#sel_math").text();
	var set_c = $("#sel_col").text();
	*/
	var id = $("#item_id").text();
	var basket_json = $("#basket").text();
	
	if(basket_json.length > 0)
	{
		var basket = JSON.parse(basket_json);

		for(var item in basket)
		{
			if(basket[item].COLORS == sel_c && basket[item].MATHERIALS == sel_m)
			{
				var in_basket = true;
			}
		}
	}
	
	if(in_basket)
	{
		if($(".buy").length > 0)
		{
			$("#button_basket").before( '<a href="/personal/cart/" id="button_basket" class="already">Уже в корзине</a>' );
			$(".buy").remove();
		}
	}
	else
	{
		if($(".already").length > 0)
		{
			var buy_link = $("#buy_link").text();
			var item_id = $("#item_id").text();
			$("#button_basket").before('<button id="button_basket" class="buy" onclick="add2basket(' + item_id + ')">В корзину</button>');
			$(".already").remove();
		}	
	}
}

function add2basket(id){
		
	var el_m = $( "#m_prop_sel_el" ).attr( "rel" );
	var el_c = $( "#c_prop_sel_el" ).attr( "rel" );

	if( el_m )
	{
		var matherial = el_m.substring( 2, el_m.length );
	}else{
		var matherial = 0; 
	}
	var color = el_c.substring( 2, el_c.length );
	var action = $("#buy_link").text();
	var price = $("#price_fin").text();
	var fuser_id = $("#fuser_id").text();

	$.ajax({
		type: "POST",
		url: action,
		dataType: "text",
		data: { 
			"FUSER_ID": fuser_id,
			"ID": id,
			"COLORS": color,
			"MATHERIALS": matherial,					
			"PRICE": price,
			},
		success: function(data){
			if(data > 0){
				animate_basket();
				update_basket(data);
			}
		},
	});
}

/*--------------------------*/

jQuery( document ).ready( function( $ ){
	
	$( '.mask' ).click( function(){
		hide();
	});
	
	$( ".prop_sel_el img" ).click( function() {
		var id = $( this ).parents().attr( "rel" );
		id = id.substr( 0, 1 );
		show_el( 'prop_items_' + id );
	})
	
	$( '.prop_element img' ).click( function( event ){
		event.preventDefault();
		var id = $( this ).parents( ".prop_element" ).attr( "id" );
		var w = $( window ).width();
		var selected = $( this ).parents( ".prop_element" ).hasClass("selected");		
		select_math( id );
	});

});