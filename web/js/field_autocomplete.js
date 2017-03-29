$( function() {
    $('#advanced_search_search').autocomplete({
        minLength: 3,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('suggest-search'),
                dataType: 'json',
                data: 'search=' + $('#advanced_search_search').val(),
                success: function (data) {
                    console.log(data);
                    var items = [];
                    $.each(data, function (key, val) {
                        items.push(val['suggest']);
                    });
                    response(items);
                }
            });
        }
    });

    $('#strain_gmo_name, #strain_wild_name').autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('strain-name-suggest', { keyword: $('#strain_gmo_name, #strain_wild_name').val() }),
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
