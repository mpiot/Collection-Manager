var collectionType = require('./collection-type');

$(document).ready(function() {
    collectionType($('div#species_synonyms'), 'Add a synonym', 'add_synonym');

    // Set the taxid div
    var taxidDiv = $('#species_taxId').closest(".form-group");

    // Resize the input
    $('.col-sm-10', taxidDiv).removeClass('col-sm-10').addClass('col-sm-9');

    // Add a button and a hidden loader
    var button = $ ('<div class="col-sm-1"><a href="#" id="taxid-send" class="btn btn-info btn-sm"><span class="fa fa-refresh"></span></a><span style="display:none;" id="taxid-send-loader" class="btn btn-info btn-sm" disabled="true"><span class="fa fa-refresh fa-spin fa-fw" title="Ajax loader"></span></span></div>');
    taxidDiv.append(button);

    // When someone click on the button
    button.click(function (e) {
        // To prevent a # in the URL
        e.preventDefault();

        // Take the taxid value
        var taxid = $("#species_taxId").val();

        // If the visitor click on the button with something written in the input field
        if (taxid.length > 0) {
            $.ajax({
                url: Routing.generate('species_getjson', { taxid: taxid }),
                dataType: 'json',
                beforeSend: function () {
                    // Replace the button by a loader
                    $('#taxid-send').hide();
                    $('#taxid-send-loader').show();

                    // Remove previous error messages
                    taxidDiv.removeClass("has-error");
                    $(".col-sm-9  > .help-block", taxidDiv).remove();
                },
                success: function (data) {
                    // If the result is an error, display it
                    if ("error" in data) {
                        taxidDiv.addClass("has-error");
                        $(".col-sm-9", taxidDiv).append("<span class='help-block'><ul class='list-unstyled'><li><span class='glyphicon glyphicon-exclamation-sign'></span> " + data.error + "</li> </ul></span>");
                    // Else call populate function
                    } else {
                        populate(data);
                    }
                },
                complete: function () {
                    // Replace the loader by a button
                    $('#taxid-send').show();
                    $('#taxid-send-loader').hide();
                }
            });
        }
    });

    function populate(data) {
        // A while on each value of the json
        $.each(data, function(key, value){
            // If this is a synonyms array, create fields before if needed
            if (key === 'synonyms') {
                var synonyms = $('#species_synonyms');

                // Count how many existant field there are
                var existingSynonymFields = synonyms.children('.form-group');
                var nbExistingSynonymFields = existingSynonymFields.length;
                var nbNeededSynonymFields = value.length;

                // If the needed number of field is > to the existing, create fields
                if (nbNeededSynonymFields > nbExistingSynonymFields) {
                    // Create fields
                    for(i = 0; i < (nbNeededSynonymFields - nbExistingSynonymFields); i++) {
                        $('#add_synonym').trigger('click');
                    }
                }
                // Else if, the needed number of field is < to the existing, remove fields
                else if(nbNeededSynonymFields < nbExistingSynonymFields) {
                    // Remove fields
                    for(i = 0; i < (nbExistingSynonymFields - nbNeededSynonymFields); i++) {
                        synonyms.children('.form-group').last().remove();
                    }
                }

                // Hydrate the fields
                $.each(value, function(subkey, subvalue){
                    // Fill elements
                    $("input[name^='species[synonyms]'][name$='[genus]']").eq(subkey).val(subvalue['genus']);
                    $("input[name^='species[synonyms]'][name$='[name]']").eq(subkey).val(subvalue['name']);
                });
                // Else just fill the input
            } else {
                $(' #species_'+key).val(value);
            }
        });
    }
});
