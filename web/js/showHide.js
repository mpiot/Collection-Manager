function showHide(linkContainer, container) {

    var $lessLink=$('<a><span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span></a>');
    var $moreLink=$('<a><span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span></a>');

    linkContainer.append(' ').append($lessLink, $moreLink);
    linkContainer.find($moreLink).hide();

    $lessLink.click(function(e) {
        e.preventDefault();
        container.slideUp();
        $lessLink.hide();
        $moreLink.show();
    });

    $moreLink.click(function(e) {
        e.preventDefault();
        container.slideDown();
        $moreLink.hide();
        $lessLink.show();
    });
}
