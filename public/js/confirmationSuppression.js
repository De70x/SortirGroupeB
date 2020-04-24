$(function(){
    $('#confirmer-suppression').on('show.bs.modal',
        function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            $(this).find('.pseudo').text($(e.relatedTarget).data('contenu'));
        });
});

$(function(){
    $('#confirmer-suppression-ville').on('show.bs.modal',
        function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            $(this).find('.ville').text($(e.relatedTarget).data('contenu'));
        });
});

$(function(){
    $('#confirmer-suppression-lieu').on('show.bs.modal',
        function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            $(this).find('.lieu').text($(e.relatedTarget).data('contenu'));
        });
});