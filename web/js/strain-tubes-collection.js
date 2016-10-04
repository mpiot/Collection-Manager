$(document).ready(function() {
    // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
    var $container = $("div[id$='_strain_tubes'], div[id$='_strain_edit_tubes']");

    if (0 !== $container.length) {
        // On supprime les labels
        $('label[for^="strain_tubes_"]').text('');

        // On ajoute un lien pour ajouter une nouvelle catégorie
        var $addLink = $('<a href="#" id="add_tube" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus"></span> Add a tube</a>');
        $container.append($addLink);

        // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
        $addLink.click(function(e) {
            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            addTube($container);
            return false;
        });

        // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
        var index = $container.find("div[id*='_strain_tubes_'], div[id*='_strain_edit_tubes_']").length;

        // On ajoute un premier champ automatiquement s'il n'en existe pas déjà un (cas d'une nouvelle annonce par exemple).
        if (index == 0) {
            addTube($container);
        } else {
            // Pour chaque catégorie déjà existante, on ajoute les actions onChange
            $container.children('div').each(function() {
                onProjectChange($(this));
                onBoxChange($(this));
            });
        }

        // La fonction qui ajoute un formulaire Categorie
        function addTube(container) {
            // Dans le contenu de l'attribut « data-prototype », on remplace :
            // - le texte "__name__label__" qu'il contient par le label du champ
            // - le texte "__name__" qu'il contient par le numéro du champ
            var $prototype = $(container.attr('data-prototype')
                .replace(/__name__label__/g, index)
                .replace(/__name__/g, index));

            // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
            addDeleteLink($prototype);

            // On ajoute la gestion du onChange sur Project
            onProjectChange($prototype);

            // On ajoute le prototype modifié à la fin de la balise <div>
            $('#add_tube').before($prototype);

            // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
            index++;
        }

        // La fonction qui ajoute un lien de suppression d'une catégorie
        function addDeleteLink(prototype) {
            // Création du lien
            $deleteLink = $('<div class="col-sm-1"><a href="#" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-trash"></span></a></div>');

            // Ajout du lien
            $('.col-sm-10', prototype).removeClass('col-sm-10').addClass('col-sm-9');
            prototype.append($deleteLink);

            // Ajout du listener sur le clic du lien
            $deleteLink.click(function(e) {
                e.preventDefault(); // évite qu'un # apparaisse dans l'URL
                prototype.remove();
                return false;
            });
        }

        // When project change
        function onProjectChange(container) {
            // Transmit Type and Species, because it correct a bug. The FormType bug if Type isn't send.
            var $type = $('[name$="[type]"]');
            var $species = $('[name$="[species]"]');

            var $project = $(container).find('[name$="[project]"]');
            var $box = $(container).find('[name$="[box]"]');

            // When project gets selected ...
            $project.change(function () {
                // ... retrieve the corresponding form.
                var $form = $(this).closest('form');
                // Simulate form data, but only include the selected project value.
                var data = {};
                data[$type.attr('name')] = $type.val();
                data[$species.attr('name')] = $species.val();
                data[$project.attr('name')] = $project.val();
                data[$box.attr('name')] = $box.val();

                // Submit data via AJAX to the form's action path.
                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: data,
                    success: function (html) {
                        // Replace current position field ...
                        $(container).find('[name$="[box]"]').replaceWith(
                            // ... with the returned one from the AJAX response.
                            $(html).find('[name$="[box]"]')
                        );

                        $(container).find('[name$="[cell]"]').replaceWith(
                            // ... with the returned one from the AJAX response.
                            $(html).find('[name$="[cell]"]')
                        );
                        // Position field now displays the appropriate positions.

                        // Add the boxChange function on the field
                        onBoxChange(container);
                    }
                });
            });
        }

        // When box change
        function onBoxChange(container) {
            // Transmit Type and Species, because it correct a bug. The FormType bug if Type isn't send.
            var $type = $('[name$="[type]"]');
            var $species = $('[name$="[species]"]');

            var $box = $(container).find('[name$="[box]"]');

            // When box gets selected ...
            $box.change(function () {
                // ... retrieve the corresponding form.
                var $form = $(this).closest('form');
                // Simulate form data, but only include the selected box value.
                var data = {};
                data[$type.attr('name')] = $type.val();
                data[$species.attr('name')] = $species.val();
                data[$box.attr('name')] = $box.val();

                // Submit data via AJAX to the form's action path.
                $.ajax({
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: data,
                    success: function (html) {
                        // Replace current position field ...
                        $(container).find('[name$="[cell]"]').replaceWith(
                            // ... with the returned one from the AJAX response.
                            $(html).find('[name$="[cell]"]')
                        );
                        // Position field now displays the appropriate positions.
                    }
                });
            });
        }
    }
});
