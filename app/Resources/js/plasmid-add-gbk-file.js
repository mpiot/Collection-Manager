$(document).ready(function(){
    if($('input[name$="[addGenBankFile]"][value=0]').prop('checked'))
    {
        $("#genBank-file-field").hide();
    }

    $('input[name$="[addGenBankFile]"]').change(function(){
        $("#genBank-file-field").toggle("slow");
    });
});
