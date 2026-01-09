jQuery(document).ready(function ($) {
    $(document).on('click', ".speaker-generate", function (e) {
        e.preventDefault();
        
        if(!$('#chapter-' + chapterID + '-voice-generator').hasClass('loading')){
            var postID = $('#post_ID').val();
            var chapterID = $(this).data('chapter');
            
            $('#chapter-' + chapterID + '-voice-generator').addClass('loading');
            
            doingAjax = true;
            $.ajax({
                url: wpManga.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp-manga-generate-voice',
                    postID: postID,
                    chapterID: chapterID,
                },
                success: function (resp) {
                    alert(resp.message);
                    
                    if (resp.success) {
                        $('#chapter-' + chapterID + '-voice-generator').addClass('generated').html('<i class="fas fa-file-audio"></i> ' + 'Regenerate?');
                    }
                },
                error: function(e, xhr){
                    alert(e.responseText);
                },
                complete: function(){
                    $('#chapter-' + chapterID + '-voice-generator').removeClass('loading');
                    doingAjax = false;
                }
            });
        }
    });
});