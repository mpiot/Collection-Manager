var collectionType = require('./collection-type');
var charMap = require('./charmap');
var onBoxChange = require('./strain-tubes-dynamic-on-box-change');

$( function() {
    charMap($('#plasmid_name, #plasmid_edit_name'));
    collectionType($('div#plasmid_primers, div#plasmid_edit_primers'), 'Add a primer', 'add-primer');
    collectionType($('div#plasmid_tubes, div#plasmid_edit_tubes'), 'Add a tube', null, true, [onBoxChange]);

    $('[id^="plasmid_primers_"], [id^="plasmid_edit_primers_"]').select2();
    $('#add-primer').click(function() {
        $('[id^="plasmid_primers_"], [id^="plasmid_edit_primers_"]').select2();
    });

    var $group = $('#plasmid_group');
    $group.change(function () {
        // Fields
        var primers = $('div#plasmid_primers');
        var tubes = $('div#plasmid_tubes');

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
                primers.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#plasmid_primers')
                );

                tubes.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#plasmid_tubes')
                );

                collectionType($('div#plasmid_primers'), 'Add a primer', 'add-primer');
                $('#add-primer').click(function() {
                    $('[id^="plasmid_primers_"]').select2();
                });

                collectionType($('div#plasmid_tubes'), 'Add a tube', null, true, [onBoxChange]);
            }
        });
    });
} );
