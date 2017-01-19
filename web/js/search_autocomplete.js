$( function() {
    $('[name="advanced_search[search]"]').autocomplete({
        minLength: 3,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('suggest-search'),
                dataType: 'json',
                data: 'search=' + $('[name="advanced_search[search]"]').val(),
                success: function (data) {
                    var items = [];
                    $.each(data, function (key, val) {
                        items.push(val['suggest']);
                    });
                    response(items);
                }
            });
        }
    });

    $('[name="quick_search[search]"]').autocomplete({
        minLength: 3,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('suggest-search'),
                dataType: 'json',
                data: 'search=' + $('[name="quick_search[search]"]').val(),
                success: function (data) {
                    var items = [];
                    $.each(data, function (key, val) {
                        items.push(val['suggest']);
                    });
                    response(items);
                }
            });
        }
    });
});
