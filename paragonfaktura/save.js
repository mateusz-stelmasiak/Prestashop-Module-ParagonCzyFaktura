
$(document).ready(function(){
	$('#invoice_bill input').click(function(){
		var value = $('#invoice_bill input:checked').val();
		var id_cart = $('#invoice_bill #invoice_bill_id').val();
		$.post("/modules/paragonfaktura/ajax.php", { value: value, id_cart: id_cart }, function(data){
			
		});
	})
});
