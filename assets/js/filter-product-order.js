var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var group = $('#product-order-group-field');

    group.change(function() {
        history.replaceState('', '', Routing.generate('product_order', { group: group.val() }));
        $.ajax({
            type: 'GET',
            url: Routing.generate('product_order_ajax', { group: group.val() }),
            dataType: 'html',
            delay: 400,
            beforeSend: function() {
                if (processing) {
                    return false;
                } else {
                    processing = true;
                }
            },
            success: function (html) {
                $('#product-order-list').replaceWith(html);
                processing = false;
            }
        });
    });
});
