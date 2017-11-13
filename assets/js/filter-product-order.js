var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var team = $('#product-order-team-field');

    team.change(function() {
        history.replaceState('', '', Routing.generate('product_order', { team: team.val() }));
        $.ajax({
            type: 'GET',
            url: Routing.generate('product_order_ajax', { team: team.val() }),
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
