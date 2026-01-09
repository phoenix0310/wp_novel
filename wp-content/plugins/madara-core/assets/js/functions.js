
    function clearMessage( sel ) {
        jQuery( sel ).empty();
        jQuery( sel ).removeClass('success-msg');
        jQuery( sel ).removeClass('error-msg');
    }

    function addMessage( text, sel ){
        jQuery( sel ).text( text );
    }

    function mangaSingleMessage( text, sel, suc  ) {
        clearMessage( sel );
        addMessage( text, sel );

        if( suc == true ) {
            jQuery( sel ).addClass('success-msg');
        }else{
            jQuery( sel ).addClass('error-msg');
        }

        jQuery( sel ).fadeIn();

    }

    function showLoading(){
        jQuery('.wp-manga-popup-loading').fadeIn();
    }

    function hideLoading(){
        jQuery('.wp-manga-popup-loading').fadeOut();
    }

    function clearFormFields( sel ){

        jQuery( sel + ' input[type="text"]').each( function( i, e ){
            jQuery(this).val('');
        } );

        jQuery( sel + ' select option:first').each( function( i, e ){
            jQuery(this).prop( 'selected', true );
        });

        jQuery( sel + ' input[type="file"]').each( function( i, e ){
            jQuery(this).val('');
        });

    }

    function validateFile( file, sel = null ){

        var message = '';

        if( file.size / 1000 >= file_size_settings.upload_max_filesize ) {
            message = wpManga.strings.max_upload_exceed;

        }else if( file.size / 1000 >= file_size_settings.post_max_size ) {
            message = wpManga.strings.max_post_file;
        }

        if( message !== '' ){
            if( sel ){
                mangaSingleMessage( message, sel, false );
            }else{
                alert( message );
            }

            return false;
        }else{
            return true;
        }

    }

    function updateChaptersList(){

        jQuery('.fetching-data').toggleClass('hidden');
        jQuery('.chapter-list').css('opacity', '0.5');

        var postID = jQuery('input[name="post_ID"]').val();

        jQuery.ajax({
            url : wpManga.ajax_url,
            type : 'POST',
            data : {
                action : 'wp-update-chapters-list',
                postID : postID,
            },
            success : function( response ) {
                if( response.success == true ) {
                    jQuery('.chapter-list span').remove();
                    jQuery('.chapter-list').html( response.data );
                }

                jQuery('.fetching-data').toggleClass('hidden');
                jQuery('.chapter-list').css('opacity', '1');
				
				attach_event_multi_select_chapters();
            }
        });
    }

    function syncTextContent( id ){
        var textTab = jQuery( 'textarea#' + id );

        if( isTinyMCEActive === false ){
            tinyMCE.get( id ).setContent( textTab.val() );
        }
    }

    function isTinyMCEActive( id ){

        if( typeof tinyMCE == 'undefined' || tinyMCE.get( id ) == null ){
            return false;
        }

        var wrapper = jQuery( '#wp-' + id + '-wrap' );

        return wrapper.hasClass('tmce-active');
    }

    // Modal Functions

    var modal = jQuery('#wp-manga-chapter-modal');

	function showModal(refresh = true) {

		if (refresh == true) {
			modalRefresh();
		}

		modal.fadeIn(200);
	}

	function hideModal() {
		modal.fadeOut(200);
	}

	function showModalLoading() {
		jQuery('#wp-manga-modal-content').addClass('loading');
		jQuery('.wp-manga-modal .wp-manga-popup-loading').show();
		jQuery('.wp-manga-disable').removeClass('hidden');
		jQuery('body').css("pointer-events", "none");
	}

	function hideModalLoading() {
		jQuery('#wp-manga-modal-content').removeClass('loading');
		jQuery('.wp-manga-modal .wp-manga-popup-loading').hide();
		jQuery('.wp-manga-disable').addClass('hidden');
		jQuery('body').css("pointer-events", "initial");
	}

    function modalRefresh() {
		jQuery('#wp-manga-modal-post-id').val('');
		jQuery('#wp-manga-modal-chapter').val('');

		modalContentRefresh();
	}

	function modalContentRefresh() {
		jQuery('#manga-sortable').html('');
		jQuery('#manga-storage-dropdown').html('');
		jQuery('#manga-volume-dropdown').val('0');
		jQuery('#wp-manga-modal-chapter-name').val();
		jQuery('.duplicate-chapter').hide();
		jQuery('#duplicate-server').empty();
	}

    function modalGetChapter(chapter, storage = '') {

		if (storage !== '') {
			modalContentRefresh();
		}

		//assign chapter name
		jQuery('#wp-manga-modal-chapter-name').val(chapter.chapter.chapter_name);

		//assign chappter extend name
		//if (chapter.chapter.chapter_name_extend !== '') {
			jQuery('#wp-manga-modal-chapter-extend-name').val(chapter.chapter.chapter_name_extend);
		//}
		
		//if (chapter.chapter.chapter_index !== '') {
			jQuery('#wp-manga-modal-chapter-index').val(chapter.chapter.chapter_index);
		//}

		//assign volume
		jQuery('#wp-manga-modal-content #wp-manga-volume').val(chapter.chapter.volume_id);
		
		jQuery('#manga-seo-desc').val(chapter.chapter.chapter_seo);
		jQuery('#manga-warning-text').val(chapter.chapter.chapter_warning);
		jQuery('#chapter_status').val(chapter.chapter.chapter_status);
		
		if(jQuery('#chapter-amp-height').length > 0){
			jQuery('#chapter-amp-height').val(chapter.chapter.AMP_Height);
		}

		if (chapter.type == 'manga') {

			//add class for manga to wp-manga-modal-content
			jQuery('#wp-manga-modal-content').addClass('manga');

			var sortZone = jQuery('#manga-sortable');

			//assign in use storage
			var inUse = storage !== '' ? storage : chapter.data.storage.inUse;
			jQuery('#manga-storage-dropdown').empty();
			jQuery.each(chapter.data.storage, function (i, e) {
				if (i != 'inUse') {
					jQuery('#manga-storage-dropdown').append('<option value=' + i + '>' + e.name + '</option>')
				}
				;

				if (i == inUse) {
					jQuery.each(e.page, function (num, value) {
						switch (value.mime) {
							case 'image/jpeg':
								var html = '<li class="wp-paging" data-src="' + value.src + '" data-page="' + num + '"><img src="' + e.host + value.src + '"><a href="javascript:void(0)" class="delete-page"></a></li>';
								break;
							default:
								var html = '<li class="wp-paging" data-src="' + value.src + '" data-page="' + num + '"><img src="' + e.host + value.src + '"><a href="javascript:void(0)" class="delete-page"></a></li>';
						}
						sortZone.append(html);
					})
				}
			});

			jQuery('#manga-storage-dropdown').val(inUse);

			if(inUse == 'gphotos'){
				jQuery('#chapter-upload-more .gphotos-import').show();
			} else {
				jQuery('#chapter-upload-more .gphotos-import').hide();
			}
			
			if(jQuery('#manga-storage-dropdown option').length > 1){
				jQuery('#remove-storage-btn').show();
			} else {
				jQuery('#remove-storage-btn').hide();
			}

			if (chapter.available_host !== undefined) {
				jQuery.each(chapter.available_host, function (i, e) {
					jQuery('select#duplicate-server').append('<option value="' + e.value + '">' + e.text + '</option>');
				});
				jQuery('.duplicate-chapter').show();
			}

		} else if (chapter.type == 'text' || chapter.type == 'video') {
			var editorID = 'wp-manga-chapter-content-wp-editor',
				isTMCEActive = isTinyMCEActive(editorID);

			//set content for editor
			if (isTMCEActive) {
				var text = chapter.data.replace(/\n/ig,"<br>");
				tinyMCE.get(editorID).setContent(text);
			} else if (isTMCEActive === false) {
				jQuery('textarea#' + editorID).val(chapter.data);
			}
		}
		
		jQuery(document).trigger('wp_manga_after_admin_fill_chapter_modal_content', [chapter, storage]);

	}