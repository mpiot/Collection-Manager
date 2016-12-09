$(document).ready(function() {
    // Get the members and administrators form
    var membersCheckBoxesContainer = $( "div[data-filtered-name='members']" );
    var administratorsCheckBoxesContainer = $( "div[data-filtered-name='administrators']" );

    // Get the filter field
    var teamFilterSelect = $( "select[data-filter-name='team-filter']" );

    // If the filter field exists, Add a choice in, and call the filter function
    if (0 != teamFilterSelect.length) {
        // Add a choice on teamFilterSelect
        teamFilterSelect.find('option:first').after($('<option/>', {
            value: 0,
            text: 'User without team'
        }));

        // Then call the filter function for Administrators and Members checkboxes
        // Before control the fields exists
        if (0 != administratorsCheckBoxesContainer.length) {
            teamFilter(administratorsCheckBoxesContainer, teamFilterSelect);
        }
        if (0 != membersCheckBoxesContainer.length) {
            teamFilter(membersCheckBoxesContainer, teamFilterSelect);
        }
    }

    // The team filter function call for each checkbox input we want filtered
    function teamFilter(userCheckBoxesContainer, teamFilterSelect) {
        // Define var that contains fields
        var userCheckboxes = userCheckBoxesContainer.find( "div.checkbox" );

        //********************************//
        //  Add the links (check/uncheck)*//
        //********************************//

        // Define checkAll/uncheckAll links
        var checkAllLink = $('<a href="#" class="check_all_users" > Check all</a>');
        var uncheckAllLink = $('<a href="#" class="uncheck_all_users" > Uncheck all</a>');

        // Insert the check/uncheck links
        userCheckBoxesContainer.prepend(uncheckAllLink);
        userCheckBoxesContainer.prepend(' / ');
        userCheckBoxesContainer.prepend(checkAllLink);

        //***************************//
        // Create all onCLick events*//
        //***************************//

        // Create onClick event on Team filter
        teamFilterSelect.change(function () {
            // Get and convert the Id in Integer
            var teamId = parseInt($(this).val());

            // Call the function and give the ID (int)
            showHideUsers(teamId);
        });

        // Create onClick event on checkAllLink
        checkAllLink.click(function (e) {
            e.preventDefault();
            var teamFiltered = parseInt(teamFilterSelect.val());

            if (isNaN(teamFiltered)) {
                checkAll();
            } else {
                checkAllTeam(teamFiltered);
            }

        });

        // Create onClick event on uncheckAllLink
        uncheckAllLink.click(function (e) {
            e.preventDefault();
            var teamFiltered = parseInt(teamFilterSelect.val());

            if (isNaN(teamFiltered)) {
                uncheckAll();
            } else {
                uncheckAllTeam(teamFiltered);
            }
        });

        function showHideUsers(teamId) {
            if (isNaN(teamId)) {
                userCheckboxes.show();
            } else {
                // Hide all Users
                userCheckboxes.hide();

                // Show team users
                userCheckboxes.each(function () {
                    var userTeams = $( this ).find( "input:checkbox" ).data('teams');

                    if (0 == userTeams.length && 0 == teamId) {
                        $(this).show();
                    } else {
                        if (-1 != $.inArray(teamId, userTeams)) {
                            $(this).show();
                        }
                    }
                });
            }
        }

        //
        // Base functions: check/uncheck all checkboxes and check/uncheck specific boxes (per TeamId)
        //

        function checkAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).find( "input:checkbox" ).data('teams');

                if (0 == userTeams.length && 0 == teamId) {
                    $(this).find("input:checkbox").prop('checked', true);
                } else {
                    if (-1 != $.inArray(teamId, userTeams)) {
                        $(this).find("input:checkbox").prop('checked', true);
                    }
                }
            });
        }

        function uncheckAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).find( "input:checkbox" ).data('teams');

                if (0 == userTeams.length && 0 == teamId) {
                    $(this).find("input:checkbox").prop('checked', false);
                } else {
                    if (-1 != $.inArray(teamId, userTeams)) {
                        $(this).find("input:checkbox").prop('checked', false);
                    }
                }
            });
        }

        function checkAll() {
            userCheckboxes.each(function () {
                $(this).find("input:checkbox").prop('checked', true);
            });
        }

        function uncheckAll() {
            userCheckboxes.each(function () {
                $(this).find("input:checkbox").prop('checked', false);
            });
        }
    }
});
