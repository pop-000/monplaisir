function update_item(id){
	var quantity = $(id).val();
	var id_basket = $(id).attr("rel");
	
	$.ajax({
			type: "POST",
			url: "/personal/cart/update_item.php",
			data: { 		
				"QUANTITY": quantity,				
				"ID_BASKET": id_basket				
				},
			success: function(){
				location.reload();
			},
		});
		
}

function separator(str)
{
	var sep = str.replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
	return sep;
}