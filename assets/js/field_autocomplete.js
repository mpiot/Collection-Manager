$( function() {
    $('#strain_gmo_name, #strain_wild_name').autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('strain_search', { name: $('#strain_gmo_name, #strain_wild_name').val() }),
                dataType: 'json',
                success: function (data) {
                    var items = [];
                    $.each(data, function (key, val) {
                        items.push(val);
                    });
                    response(items);
                }
            });
        }
    });
});
