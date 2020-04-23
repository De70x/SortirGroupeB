$(function(){
    $('#confirmer-suppression').on('show.bs.modal',
        function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            $(this).find('.pseudo').text($(e.relatedTarget).data('contenu'));
        });
});