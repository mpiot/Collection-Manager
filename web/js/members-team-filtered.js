$(document).ready(function() {
    var membersCheckBoxesContainer = $( "div[data-filtered-name='members']" );
    var administratorsCheckBoxesContainer = $( "div[data-filtered-name='administrators']" );

    var teamFilterSelect = $( "select[data-filter-name='team-filter']" );

    teamFilter(membersCheckBoxesContainer, teamFilterSelect);
    teamFilter(administratorsCheckBoxesContainer, teamFilterSelect);

    function teamFilter(userCheckBoxesContainer, teamFilterSelect) {
        // Define var that contains fields
        //var userCheckBoxesContainer = $( "div[data-filtered-name='members'] );
        var userCheckboxes = userCheckBoxesContainer.find( "div.checkbox" );

        //var teamFilterSelect = $( "select[data-filter-name='team-filter']" );

        // Define checkAll/uncheckAll links
        var checkAllLink = $('<a href="#" class="check_all_users" > Check all</a>');
        var uncheckAllLink = $('<a href="#" class="uncheck_all_users" > Uncheck all</a>');

        // Insert the check/uncheck links
        userCheckBoxesContainer.prepend(uncheckAllLink);
        userCheckBoxesContainer.prepend(' / ');
        userCheckBoxesContainer.prepend(checkAllLink);

        // Extract, store and hide teams for each user
        // Then by default: hide all checkboxes
        userCheckboxes.each(function () {
            var teams;

            // Explode the label to extract teams
            teams = $(this).find('label').text().split('Teams(');
            teams = teams[1].split(')');
            teams = teams[0].split(',');

            // Store teams in .data for each user
            $(this).data('teams', teams);

            // Remove teams from the label
            var pattern = /^(.+) - Teams\((?:\d+,?)*\)$/;
            var oldLabel = $(this).find("label").text();
            var newLabel = oldLabel.match(pattern)[1];
            var newHTML = $(this).html().split(oldLabel)[0] + newLabel;

            $(this).html(newHTML);
        });

        // Create onClick envent on Team filter
        teamFilterSelect.change(function () {
            var teamId = $(this).val();
            showHideUsers(teamId);
        });

        // Create onClick event on checkAllLink
        checkAllLink.click(function (e) {
            e.preventDefault();
            var teamFiltered = teamFilterSelect.val();

            if ('' == teamFiltered) {
                checkAll();
            } else {
                checkAllTeam(teamFiltered);
            }

        });

        // Create onClick event on uncheckAllLink
        uncheckAllLink.click(function (e) {
            e.preventDefault();
            var teamFiltered = teamFilterSelect.val();

            if ('' == teamFiltered) {
                uncheckAll();
            } else {
                uncheckAllTeam(teamFiltered);
            }
        });

        function showHideUsers(teamId) {
            if ('' == teamId) {
                // We want show all users
                userCheckboxes.show();
            } else {
                // Hide all Users
                userCheckboxes.hide();

                // Show team users
                userCheckboxes.each(function () {
                    var userTeams = $(this).data('teams');

                    if (-1 != $.inArray(teamId, userTeams)) {
                        $(this).show();
                    }
                });
            }
        }

        function checkAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).data('teams');

                if (-1 != $.inArray(teamId, userTeams)) {
                    $(this).find("input:checkbox").prop('checked', true);
                }
            });
        }

        function uncheckAllTeam(teamId) {
            userCheckboxes.each(function () {
                var userTeams = $(this).data('teams');

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
