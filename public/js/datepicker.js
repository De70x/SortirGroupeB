$(function () {
    moment.locale('fr');
    $.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
        locale: 'fr',
        icons: {
            time: 'far fa-clock dark',
            date: 'far fa-calendar',
            up: 'far fa-arrow-up',
            down: 'far fa-arrow-down',
            previous: 'far fa-chevron-left',
            next: 'far fa-chevron-right',
            today: 'far fa-calendar-check-o',
            clear: 'far fa-trash',
            close: 'far fa-times'
        },
    });

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