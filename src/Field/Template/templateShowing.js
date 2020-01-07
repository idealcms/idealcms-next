$(document).ready(function () {
    $('#general_structure').change(function () {
        var templateId = $(this).val().toLowerCase();
        $('.general_template-controls input').each(function (index) {
            $(this).hide();
        });
        $('.general_template-controls a').each(function (index) {
            $(this).hide();
        });
        $('#general_template_' + templateId).siblings('.general_template_' + templateId).show();
    });
});
