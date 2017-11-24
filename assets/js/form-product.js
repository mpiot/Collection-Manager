var formHandle = require('./form-handle');

$( function() {
    formHandle($('select[name="product[location]"]'), $('#add-location'), $('form[name="location"]'), 'form[name="location"]');
    formHandle($('select[name="product[seller]"]'), $('#add-seller'), $('form[name="seller"]'), 'form[name="seller"]');
    formHandle($('select[name="product[brand]"]'), $('#add-brand'), $('form[name="brand"]'), 'form[name="brand"]');
} );
