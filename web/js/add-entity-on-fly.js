function sendForm(form, callback) {
    // Get all form values
    var values = {};
    $.each( form[0].elements, function(i, field) {
        if (field.type != 'checkbox' || (field.type == 'checkbox' && field.checked)) {
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
}

function handleForm(targetField, modal, form, formSelector) {
    form.find(':submit').click( function( e ){
        e.preventDefault();

        sendForm( form, function( response ) {
            if (typeof response == "object") {
                targetField
                    .append($('<option>', {value: response.id, text: response.name}))
                    .val(response.id);
                modal.modal('hide');

            }
            else {
                // Unbind the click event on the button
                form.find(':submit').unbind("click");
                // Change the form code
                modal.find('.modal-body').html(response);
                // Recall this method on a new event
                handleForm(targetField, modal, $(formSelector), formSelector);
            }
        });
    });
}
