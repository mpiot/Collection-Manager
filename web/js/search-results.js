$(document).ready(function(){
    $('.searchResults table tr').click(function(){
        window.location = $(this).data('href');
        return false;
    });
});