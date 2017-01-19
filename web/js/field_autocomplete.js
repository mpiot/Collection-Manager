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

    $('[name="gmo_strain[name]"], [name$="wild_strain[name]"]').autocomplete({
        minLength: 2,
        source: function (request, response) {
            $.ajax({
                url: Routing.generate('strain-name-suggest', { keyword: $('[name="gmo_strain[name]"], [name$="wild_strain[name]"]').val() }),
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
