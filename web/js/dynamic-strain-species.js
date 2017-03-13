$(document).ready(function() {
    // Transmit Type and Species, because it correct a bug. The FormType bug if Type isn't send.
    var $type = $('[name$="[type]"]');
    var $genus = $('[name$="[species][genus]"]');
    var $species = $('[name$="[species][name]"]');

    // When genus gets selected ...
    $genus.change(function () {
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected genus value.
        var data = {};
        data[$genus.attr('name')] = $genus.val();
        data[$species.attr('name')] = $species.val();
        data[$type.attr('name')] = $type.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                $('[name$="[species][name]"]').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('[name$="[species][name]"]')
                );
                // Position field now displays the appropriate positions.
            }
        });
    });
});
