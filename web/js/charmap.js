function charMap(fieldHandler)
{
    // Create the chars array
    let chars = ['αβγδεζ', 'ηθικλμ', 'νξοπρσ', 'τυφχψω'];
    let charsArray = [];

    for (let i = 0; i < chars.length; i++) {
        charsArray[i] = chars[i].split('');
    }

    // Create the table object
    let table = $('<table class="table table-bordered text-center">');

    for (let i = 0; i < charsArray.length; i++) {
        let tr = '<tr>';
        for (let j = 0; j < charsArray[i].length; j++) {
            let td = '<td>';
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
