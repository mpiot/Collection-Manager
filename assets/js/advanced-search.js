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
        $projectField = $('#advanced_search_project').closest('.form-group');
        $typeField = $('#advanced_search_type').closest('.form-group');
        $deletedField = $('#advanced_search_deleted').closest('.form-group');

        // First display all
        $countryField.show();
        $projectField.show();
        $typeField.show();
        $deletedField.show();

        if (!$gmoStrainCheckbox.is(':checked') && !$wildStrainCheckbox.is(':checked')) {
            $countryField.hide();
            $projectField.hide();
            $typeField.hide();
            $deletedField.hide();
        } else if (!$wildStrainCheckbox.is(':checked')) {
            $countryField.hide();
        }
    }
});
