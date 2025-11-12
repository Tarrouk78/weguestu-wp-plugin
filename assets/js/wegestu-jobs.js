jQuery(document).ready(function($){
    $(document).on('click', '.wegestu-load-more', function(e){
        e.preventDefault();
        var btn = $(this);
        var page = parseInt(btn.data('page')) || 2;
        var per_page = parseInt(btn.data('per_page')) || 5;

        btn.prop('disabled', true).text('Chargement...');
        $.post(WegestuJobs.ajax_url, {
            action: 'wegestu_load_more',
            nonce: WegestuJobs.nonce,
            page: page,
            per_page: per_page
        }, function(resp){
            if (resp.success && resp.data.html) {
                $('.wegestu-jobs-list').append(resp.data.html);
                btn.data('page', page + 1).prop('disabled', false).text('Charger plus');
            } else {
                btn.text('Plus de résultats').prop('disabled', true);
            }
        }).fail(function(){
            btn.text('Erreur').prop('disabled', false);
        });
    });
});
