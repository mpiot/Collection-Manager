var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var search = $('#box-search-field');
    var team = $('#box-team-field');

    search.keyup(function() {
        history.replaceState('', '', Routing.generate('box_index', { q: search.val(), team: team.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('box_index_ajax', { q: search.val(), team: team.val(), p: 1 }),
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
                    $('#box-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });

    team.change(function() {
        history.replaceState('', '', Routing.generate('box_index', { q: search.val(), team: team.val(), p: 1 }));
        $.ajax({
            type: 'GET',
            url: Routing.generate('box_index_ajax', { q: search.val(), team: team.val(), p: 1 }),
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
                $('#box-list').replaceWith(html);
                processing = false;
            }
        });
    });
});
