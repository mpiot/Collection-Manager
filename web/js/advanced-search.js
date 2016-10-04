$(document).ready(function(){
    var $checkbox = $('#advanced_search_strainCategory_1');

    if (!$checkbox.is(':checked')) {
        $('label[for="advanced_search_country"]').parent().hide();
    }

    $checkbox.change(function() {
        $('label[for="advanced_search_country"]').parent().toggle();
    });
});
