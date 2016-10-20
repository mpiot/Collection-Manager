$(document).ready(function() {
    var project = $('[name$="[project]"]');

    // When project gets selected ...
    project.change(function () {
        // ... retrieve the corresponding form.
        var form = $(this).closest('form');
        // Simulate form data, but only include the selected genus value.
        var data = {};
        data[project.attr('name')] = project.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                $('[name$="[type]"]').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('[name$="[type]"]')
                );
                // Position field now displays the appropriate positions.
            }
        });
    });
});
