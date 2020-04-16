$(function () {
    $('#datetimepickerDebut').datetimepicker();
    $('#datetimepickerFin').datetimepicker({
        useCurrent: false
    });
    $("#datetimepickerDebut").on("change.datetimepicker", function (e) {
        $('#datetimepickerFin').datetimepicker('minDate', e.date);
    });
    $("#datetimepickerFin").on("change.datetimepicker", function (e) {
        $('#datetimepickerDebut').datetimepicker('maxDate', e.date);
    });
});