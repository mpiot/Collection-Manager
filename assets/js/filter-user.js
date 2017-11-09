var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var search = $('#user-search-field');
    var team = $('#user-team-field');

    search.keyup(function() {
        history.replaceState('', '', Routing.generate('user_index', { q: search.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('user_index_ajax', { q: search.val(), p: 1 }),
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
                    $('#user-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });
});