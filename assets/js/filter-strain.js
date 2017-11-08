var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var search = $('#strain-search-field');
    var project = $('#strain-project-field');

    search.keyup(function() {
        var field = $('#strain-search-field');

        history.replaceState('', '', Routing.generate('strain_index', { q: field.val(), project: project.val(), p: 1 }));

        delay(function(){
            $.ajax({
                type: 'GET',
                url: Routing.generate('strain_index_ajax', { q: field.val(), project: project.val(), p: 1 }),
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
                    $('#strain-list').replaceWith(html);
                    processing = false;
                }
            });
        }, 400 );
    });

    project.change(function() {
        history.replaceState('', '', Routing.generate('strain_index', { q: search.val(), project: project.val(), p: 1 }));
        $.ajax({
            type: 'GET',
            url: Routing.generate('strain_index_ajax', { q: search.val(), project: project.val(), p: 1 }),
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
                $('#strain-list').replaceWith(html);
                processing = false;
            }
        });
    });
});
