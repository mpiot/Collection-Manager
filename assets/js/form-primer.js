var charMap = require('./charmap');
var collectionType = require('./collection-type');
var onBoxChange = require('./strain-tubes-dynamic-on-box-change');

$( function() {
    charMap($('#primer_name, #primer_edit_name'));
    collectionType($('div#primer_tubes, div#primer_edit_tubes'), 'Add a tube', null, true, [onBoxChange]);

    var $group = $('#primer_group');
    $group.change(function () {
        // Fields
        var tubes = $('div#primer_tubes');

        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected genus value.
        var data = {};
        data[$group.attr('name')] = $group.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                tubes.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#primer_tubes')
                );

                collectionType($('div#primer_tubes'), 'Add a tube', null, true, [onBoxChange]);
            }
        });
    });
} );
