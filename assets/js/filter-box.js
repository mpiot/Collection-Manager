var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var search = $('#box-search-field');
    var project = $('#box-project-field');

    search.keyup(function() {
        var field = $('#box-search-field');

        history.replaceState('', '', Routing.generate('box_index', { q: field.val(), project: project.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('box_index_ajax', { q: field.val(), project: project.val(), p: 1 }),
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

    project.change(function() {
        history.replaceState('', '', Routing.generate('box_index', { q: search.val(), project: project.val(), p: 1 }));
        $.ajax({
            type: 'GET',
            url: Routing.generate('box_index_ajax', { q: search.val(), project: project.val(), p: 1 }),
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
