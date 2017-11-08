var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    $('#project-search-field').keyup(function() {
        var field = $('#project-search-field');

        history.replaceState('', '', Routing.generate('project_index', { q: field.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('project_index_ajax', { q: field.val(), p: 1 }),
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
                    $('#project-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });
});
