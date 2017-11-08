module.exports = function(fieldHandler) {
    // Create the chars array
    var chars = ['αβΔδε', 'θλμπρ', 'σφχψω', '©®™'];
    var charsArray = [];

    for (var i = 0; i < chars.length; i++) {
        charsArray[i] = chars[i].split('');
    }

    // Create the table object
    var table = $('<table class="table table-bordered text-center">');

    for (var j = 0; j < charsArray.length; j++) {
        var tr = '<tr>';
        for (var k = 0; k < charsArray[j].length; k++) {
            var td = '<td>';
            td += charsArray[j][k];
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
        if(e.target !== fieldHandler[0] && 0 === table.find(e.target)['length']) {
            table.hide();
        }
    });
};
