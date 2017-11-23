module.exports = function(form, callback) {
    // Get all form values
    var values = {};
    $.each( form[0].elements, function(i, field) {
        if (field.type !== 'checkbox' || (field.type === 'checkbox' && field.checked)) {
            values[field.name] = field.value;
        }
    });

    // Post form
    $.ajax({
        type        : form.attr( 'method' ),
        url         : form.attr( 'action' ),
        data        : values,
        success     : function(result) { callback( result ); }
    });
};
