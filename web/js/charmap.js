function charMap(fieldHandler)
{
    // Create the chars array
    var chars = ['αβγδεζ', 'ηθικλμ', 'νξοπρσ', 'τυφχψω'];
    var charsArray = [];

    for (var i = 0; i < chars.length; i++) {
        charsArray[i] = chars[i].split('');
    }

    // Create the table object
    var table = $('<table class="table table-bordered text-center">');

    for (var i = 0; i < charsArray.length; i++) {
        var tr = '<tr>';
        for (var j = 0; j < charsArray[i].length; j++) {
            var td = '<td>';
            td += charsArray[i][j];
            td += '</td>';

            tr += td;
        }
        tr += '</tr>';

        table.append(tr);
    }
    table.append('</table>');

    table.on( "click", "td", function(e) {
        fieldHandler.val(fieldHandler.val() + $( this ).text());
        fieldHandler.focus();
    });

    fieldHandler.after(table);
    table.hide();

    // Handle the table
    fieldHandler.focus(function() {
        table.show();
    });

    // Remove the table when user click elsewhere
    $('body').click(function(e) {
        if(e.target != fieldHandler[0] && 0 == table.find(e.target)['length']) {
            table.hide();
        }
    });
}
