var delay = require('./delay');

$(document).ready(function(){
    var processing = false;
    var form = $('form#list-filter-form');

    // We can filtered results by: query and/or/not group
    var query = form.find('input#query');
    var group = form.find('select#group-filter');

    // Get the URL's to process ajax
    var url = form.data('url');
    var ajaxUrl = form.data('ajaxUrl');

    // Add a keyup listener on query
    query.keyup(function() {
        history.replaceState('', '', generateUrl(url));
        delay(function(){
            performRequest();
        }, 400 );
    });

    // Add a change listener on group select
    group.change(function() {
        history.replaceState('', '', generateUrl(url));
        performRequest();
    });

    // Create a function to generate the correct URL
    function generateUrl(urlScheme) {
        var url = urlScheme;

        // If query exist
        if (query.length > 0) {
            url = url.replace(/__query__/g, query.val());
        }

        // If group exist
        if (group.length > 0) {
            url = url.replace(/__group__/g, group.val());
        }

        url = url.replace(/__page__/g, 1);

        return url;
    }

    // Create a function that perform the ajax request
    function performRequest() {
        $.ajax({
            type: 'GET',
            url: generateUrl(ajaxUrl),
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
                $('#filtered-list').replaceWith(html);
                processing = false;
            }
        });
    }
});
