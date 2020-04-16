$(function () {
    $('#form_dateDebut').datetimepicker();
    $('#form_dateFin').datetimepicker({
        useCurrent: false
    });
    $("#form_dateDebut").on("change.datetimepicker", function (e) {
        $('#form_dateFin').datetimepicker('minDate', e.date);
    });
    $("#form_dateFin").on("change.datetimepicker", function (e) {
        $('#form_dateDebut').datetimepicker('maxDate', e.date);
    });
});