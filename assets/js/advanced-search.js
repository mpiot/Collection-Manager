$(document).ready(function(){
    var $gmoStrainCheckbox = $('#advanced_search_category_0');
    var $wildStrainCheckbox = $('#advanced_search_category_1');

    showHideStrainField();

    $gmoStrainCheckbox.change(function() {
        showHideStrainField();
    });

    $wildStrainCheckbox.change(function() {
        showHideStrainField();
    });

    function showHideStrainField() {
        // Set selectors
        $countryField = $('#advanced_search_country').closest('.form-group');

        // First display all
        $countryField.show();

        if (!$wildStrainCheckbox.is(':checked')) {
            $countryField.hide();
        }
    }
});
