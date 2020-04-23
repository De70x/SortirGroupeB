$(function () {
    $('#confirmer-suppression').on('show.bs.modal',
        function (e) {
            action = $(e.relatedTarget).data('action');
            $.get(action, function(data){
                $('.modal-body').html(data);
            });
            $(this).find('.nomUserSuppr').text($(e.relatedTarget).data('contenu'));
        });
});