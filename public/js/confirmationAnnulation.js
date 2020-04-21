$(function () {
    $('#confirmer-annulation').on('show.bs.modal',
        function (e) {
            action = $(e.relatedTarget).data('action');
            $.get(action, function(data){
                $('.modal-body').html(data);
            });
            $(this).find('.nomSortieAnnulation').text($(e.relatedTarget).data('contenu'));
        });
});