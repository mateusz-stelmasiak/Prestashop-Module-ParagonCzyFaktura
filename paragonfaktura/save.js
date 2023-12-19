//update in db when type of document is changed
$(document).ready(function () {
    $('#invoice_bill input[type="radio"]').click(function () {
        var value = $('#invoice_bill input:checked').val();
        var id_cart = $('#invoice_bill #invoice_bill_id').val();
        $.post("/modules/paragonfaktura/ajax.php", {
            value: value,
            id_cart: id_cart,
        }, function (data) {
            // console.log(data);
        });
    })

});

//update in db when additional info is submited
$(document).ready(function () {
    $('#invoice_bill').submit(function () {
        var nip = $('#invoice_bill #nip').val();
        var id_address = $('#invoice_bill #id_address').val();
        var companyName = $('#invoice_bill #companyName').val();
        $.post("/modules/paragonfaktura/ajax.php", {
            id_address: id_address,
            nip: nip,
            companyName: companyName
        }, function (data) {
            // console.log(data);
        });
    })

});