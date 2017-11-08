var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    $('#species-search-field').keyup(function() {
        var field = $('input#species-search-field');

        history.replaceState('', '', Routing.generate('species_index', { q: field.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('species_index_ajax', { q: field.val(), p: 1 }),
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
                    $('#species-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });
});
