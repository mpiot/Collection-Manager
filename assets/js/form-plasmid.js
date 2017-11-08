var collectionType = require('./collection-type');
var charMap = require('./charmap');

$( function() {
    charMap($('#plasmid_name, #plasmid_edit_name'));
    collectionType($('div#plasmid_primers, div#plasmid_edit_primers'), 'Add a primer', 'add-primer');

    $('[id^="plasmid_primers_"], [id^="plasmid_edit_primers_"]').select2();
    $('#add-primer').click(function() {
        $('[id^="plasmid_primers_"], [id^="plasmid_edit_primers_"]').select2();
    });

    var $team = $('[name$="[team]"]');
    // When genus gets selected ...
    $team.change(function () {
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected genus value.
        var data = {};
        data[$team.attr('name')] = $team.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                $('div#plasmid_primers').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('#plasmid_primers')
                );
                collectionType($('div#plasmid_primers'), 'Add a primer', 'add-primer');
                $('#add-primer').click(function() {
                    $('[id^="plasmid_primers_"]').select2();
                });
            }
        });
    });
} );
