$(document).ready(function() {
    var membersCheckBoxesContainer = $( "div[data-filtered-name='members']" );
    var administratorsCheckBoxesContainer = $( "div[data-filtered-name='administrators']" );

    var teamFilterSelect = $( "select[data-filter-name='team-filter']" );

    teamFilter(administratorsCheckBoxesContainer, teamFilterSelect);
    teamFilter(membersCheckBoxesContainer, teamFilterSelect);

    function teamFilter(userCheckBoxesContainer, teamFilterSelect) {
        // Define var that contains fields
        var userCheckboxes = userCheckBoxesContainer.find( "div.checkbox" );

        // Define checkAll/uncheckAll links
        var checkAllLink = $('<a href="#" class="check_all_users" > Check all</a>');
        var uncheckAllLink = $('<a href="#" class="uncheck_all_users" > Uncheck all</a>');

        // Insert the check/uncheck links
        userCheckBoxesContainer.prepend(uncheckAllLink);
        userCheckBoxesContainer.prepend(' / ');
        userCheckBoxesContainer.prepend(checkAllLink);

        // Create onClick envent on Team filter
        teamFilterSelect.change(function () {
            var teamId = parseInt($(this).val());
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
                // We want show all users
                userCheckboxes.show();
            } else {
                // Hide all Users
                userCheckboxes.hide();

                // Show team users
                userCheckboxes.each(function () {
                    var userTeams = $( this ).find( "input:checkbox" ).data('teams');

                    if (-1 != $.inArray(teamId, userTeams)) {
                        $(this).show();
                    }
                });
            }
        }

        function checkAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).find( "input:checkbox" ).data('teams');

                if (-1 != $.inArray(teamId, userTeams)) {
                    $(this).find("input:checkbox").prop('checked', true);
                }
            });
        }

        function uncheckAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).find( "input:checkbox" ).data('teams');

                if (-1 != $.inArray(teamId, userTeams)) {
                    $(this).find("input:checkbox").prop('checked', false);
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
