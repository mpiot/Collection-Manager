var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    $('#brand-search-field').keyup(function() {
        var field = $('input#brand-search-field');

        history.replaceState('', '', Routing.generate('brand_index', { q: field.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('brand_index_ajax', { q: field.val(), p: 1 }),
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
                    $('#brand-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });
});
