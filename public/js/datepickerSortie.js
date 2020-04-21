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

    $('#form_dateHeureDebut').datetimepicker();
    $('#form_dateLimiteInscription').datetimepicker({
        useCurrent: false
    });
    $("#form_dateLimiteInscription").on("change.datetimepicker", function (e) {
        $('#form_dateHeureDebut').datetimepicker('minDate', e.date);
    });
    $("#form_dateHeureDebut").on("change.datetimepicker", function (e) {
        $('#form_dateLimiteInscription').datetimepicker('maxDate', e.date);
    });
});