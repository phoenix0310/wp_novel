(function($){
    $(document).ready(function(){

        $(document).on('click', '#wp-manga-content-chapter-create', function(e){

            e.preventDefault();

            tinyMCE.triggerSave();

            var postID            = $('input[name="postID"]').val(),
                chapterName       = $('#chapter-content #wp-manga-chapter-name').val(),
                chapterNameExtend = $('#chapter-content #wp-manga-chapter-name-extend').val(),
				chapterIndex = $('#chapter-content #wp-manga-chapter-index').val(),
                chapterVolume     = $('#chapter-content #wp-manga-volume').val(),
                chapterContent    = $('#chapter-content #wp-manga-chapter-content').val();

            if ( !chapterName || '' == chapterName ) {
                mangaSingleMessage( 'You need to input Chapter\'s name', '#chapter-create-msg', false );
            	return;
            }

            if ( !chapterContent || '' == chapterContent ) {
                mangaSingleMessage( 'Chapter Content cannot be empty', '#chapter-create-msg', false );
            	return;
            }
			
			var chapter_data = {
                action:            'wp_manga_create_content_chapter',
                postID:            postID,
                chapterName:       chapterName,
                chapterNameExtend: chapterNameExtend,
                chapterIndex: chapterIndex,
                chapterVolume:     chapterVolume,
                chapterContent:    chapterContent,
				nonce: wpManga.nonce
            };

            window.mangaContentChapterCreateTrigger = chapter_data;
            $(document).trigger('manga_content_chapter_create', [window.mangaContentChapterCreateTrigger]);
            chapter_data = window.mangaContentChapterCreateTrigger;

            showLoading();

            $.ajax({
                url : wpManga.ajax_url,
                type : 'POST',
                data : chapter_data,
                success : function( response ) {
                    if( response.success ){
                        mangaSingleMessage( response.data.message, '#chapter-create-msg', true );
                        clearFormFields( '#chapter-content' );
                        
                        //reset content in editor
                        if( typeof tinyMCE !== 'undefined' && tinyMCE.get('wp-manga-chapter-content') !== null ){
                            tinyMCE.get('wp-manga-chapter-content').setContent('');
                            tinyMCE.triggerSave();
                        }
                    }else if ( typeof response.data !== 'undefined' ){
                        mangaSingleMessage( response.data.message, '#chapter-create-msg', false );
                    }
                    updateChaptersList();
                    hideLoading();
                }
            });

        });

    });
})(jQuery);
