import googleMaps from 'googleMaps';

require('jquery-ui');
require('jquery-ui/ui/widgets/autocomplete');

var collectionType = require('./collection-type');
var onBoxChange = require('./strain-tubes-dynamic-on-box-change');
var charMap = require('./charmap');

$( function() {
    var form = $('form[name^="strain_"]');
    var $group = $('#strain_gmo_group, #strain_wild_group');
    var name = $('#strain_gmo_name, #strain_wild_name');
    var strainDisc = form.data('strain-discriminator');

    collectionType($('div#strain_gmo_tubes, div#strain_wild_tubes'), 'Add a tube', null, true, [onBoxChange]);
    collectionType($('div#strain_gmo_strainPlasmids'), 'Add a plasmid', 'add-plasmid');
    collectionType($('div#strain_gmo_parents'), 'Add a parent', 'add-parent');
    charMap(name);
    charMap($('#strain_gmo_genotype'));
    applySelect2();

    $group.change(function () {
        // Fields
        var tubes = $('div#strain_gmo_tubes, div#strain_wild_tubes');
        var plasmids = $('div#strain_gmo_strainPlasmids');
        var parents = $('div#strain_gmo_parents');

        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected genus value.
        var data = {};
        data[$group.attr('name')] = $group.val();

        // Submit data via AJAX to the form's action path.
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                // Replace current position field ...
                tubes.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#strain_gmo_tubes, div#strain_wild_tubes')
                );
                plasmids.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#strain_gmo_strainPlasmids')
                );
                parents.replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('div#strain_gmo_parents')
                );

                collectionType($('div#strain_gmo_tubes, div#strain_wild_tubes'), 'Add a tube', null, true, [onBoxChange]);
                collectionType($('div#strain_gmo_strainPlasmids'), 'Add a plasmid', 'add-plasmid');
                collectionType($('div#strain_gmo_parents'), 'Add a parent', 'add-parent');

                applySelect2();
            }
        });

    });

    // Retrieve the urlScheme
    var urlScheme = name.data('url');
    name.autocomplete({
        minLength: 2,
        source: function (request, response) {
            var group = $('#strain_gmo_group, #strain_wild_group').val();
            var name = $('#strain_gmo_name, #strain_wild_name').val();
            var url = urlScheme
                .replace(/__group__/g, group)
                .replace(/__name__/g, name);

            $.ajax({
                url: url,
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

    function applySelect2 () {
        $('#strain_gmo_species, #strain_wild_species').select2();

        $('[id^="strain_gmo_strainPlasmids_"][id$="plasmid"]').select2();
        $('#add-plasmid').click(function() {
            $('[id^="strain_gmo_strainPlasmids_"][id$="plasmid"]').select2();
        });

        $('[id^="strain_gmo_parents_"]').select2();
        $('#add-parent').click(function() {
            $('[id^="strain_gmo_parents_"]').select2();
        });
    }

    if ('wild' === strainDisc) {
        $( function() {
            $('#map').hide();
        });


            var map = new googleMaps.Map(document.getElementById('map'), {
                zoom: 0,
                center: {lat: 0, lng: 0}
            });
            var geocoder = new googleMaps.Geocoder;
            var infowindow = new googleMaps.InfoWindow;

            $('#reverseGeocode').click(function() {
                geocodeLatLng(geocoder, map, infowindow);
                $('#map').show();
                googleMaps.event.trigger(map, "resize");
            });

            $('#geocodeAddress').click(function() {
                geocodeAddress(geocoder, map);
                $('#map').show();
                googleMaps.event.trigger(map, "resize");
            });


        function geocodeLatLng(geocoder, map, infowindow) {
            var lat = parseFloat($('[name$="[latitude]"]').val());
            var lng = parseFloat($('[name$="[longitude]"]').val());
            var latlng = {lat: lat, lng: lng};

            geocoder.geocode({'location': latlng}, function(results, status) {
                if (status === googleMaps.GeocoderStatus.OK) {
                    if (results[1]) {
                        map.setZoom(11);
                        map.setCenter(latlng);
                        var marker = new googleMaps.Marker({
                            position: latlng,
                            map: map
                        });
                        infowindow.setContent(results[0].formatted_address);
                        infowindow.open(map, marker);

                        $('input[name$="[address]"]').val(results[0].formatted_address);
                        $('[name$="[country]"]').val(results[results.length - 1]['address_components'][0]['short_name']);
                    } else {
                        window.alert('No results found');
                    }
                } else {
                    window.alert('Geocoder failed due to: ' + status);
                }
            });
        }

        function geocodeAddress(geocoder, map) {
            var address = $('[name$="[address]"]').val();

            geocoder.geocode({'address': address}, function(results, status) {
                if (status === googleMaps.GeocoderStatus.OK) {
                    map.setZoom(11);
                    map.setCenter(results[0].geometry.location);
                    var marker = new googleMaps.Marker({
                        position: results[0].geometry.location,
                        map: map
                    });

                    $('[name$="[address]"]').val(results[0].formatted_address);
                    $('[name$="[latitude]"]').val(results[0].geometry.location.lat());
                    $('[name$="[longitude]"]').val(results[0].geometry.location.lng());
                    $('[name$="[country]"]').val(results[0].address_components[results[0].address_components.length - 2].short_name);
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        }
    }
});
