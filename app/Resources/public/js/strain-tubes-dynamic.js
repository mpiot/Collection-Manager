// When project change
function onProjectChange(container) {
    // Transmit Type and Species, because it correct a bug. The FormType bug if Type isn't send.
    var $type = $('[name$="[type]"]');
    var $species = $('[name$="[species]"]');

    var $project = $(container).find('[name$="[project]"]');
    var $box = $(container).find('[name$="[box]"]');

    // When project gets selected ...
    $project.change(function () {
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected project value.
        var data = {};
        data[$type.attr('name')] = $type.val();
        data[$species.attr('name')] = $species.val();
        data[$project.attr('name')] = $project.val();
        data[$box.attr('name')] = $box.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                $(container).find('[name$="[box]"]').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('[name$="[box]"]')
                );

                $(container).find('[name$="[cell]"]').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('[name$="[cell]"]')
                );
                // Position field now displays the appropriate positions.

                // Add the boxChange function on the field
                onBoxChange(container);
            }
        });
    });
}

// When box change
function onBoxChange(container) {
    // Transmit Type and Species, because it correct a bug. The FormType bug if Type isn't send.
    var $type = $('[name$="[type]"]');
    var $species = $('[name$="[species]"]');

    var $project = $(container).find('[name$="[project]"]');
    var $box = $(container).find('[name$="[box]"]');

    // When box gets selected ...
    $box.change(function () {
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected box value.
        var data = {};
        data[$type.attr('name')] = $type.val();
        data[$species.attr('name')] = $species.val();
        data[$project.attr('name')] = $project.val();
        data[$box.attr('name')] = $box.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                $(container).find('[name$="[cell]"]').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('[name$="[cell]"]')
                );
                // Position field now displays the appropriate positions.
            }
        });
    });
}
