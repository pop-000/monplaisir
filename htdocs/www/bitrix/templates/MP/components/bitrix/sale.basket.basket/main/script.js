$( document ).ready( function(){
	$( '.del_item' ).click( function(){
		var id = $( this ).attr( "rel" );
		var user_id = $( "#FUSER_ID" ).text();
		$.ajax({
			type: "POST",
			url: "del.php",
			dataType: "text",
			data: { 
				"del": id				
				},
			success: function(){
				$( "#" + id ).remove();
				update_basket( user_id );
			},
		});
	});
});

function update_basket( user_id ){
	$.ajax({
			type: "POST",
			url: "/personal/cart/update.php",
			dataType: "text",
			data: { 
				"user_id": user_id				
				},
			success: function( data ){
				var arr = data.split(',');
				if( arr[1] > 0 )
				{
					$( "#basket_line_count" ).text( arr[1] );
					$( "#basket_line_sum" ).text( separator( arr[0] ) + " руб." );
					$( "#sum_basket" ).text( "Итого: " + separator( arr[0] ) + " руб." );
				}
				else
				{
					$( "#basket_line_text" ).text( "Корзина пуста" );
					$( "#basket_items" ).text( "Корзина пуста." );
				}

			},
		});
}

function separator(str)
{
	var sep = str.replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
	return sep;
}