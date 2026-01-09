jQuery(document).ready(function($){
    //upload chapter
    $(document).on( 'click', '#wp-manga-chapter-file-upload', function(e){

    	e.preventDefault();

    	var post       = $('input[name="postID"]').val();
    	var name       = $('#wp-manga-chapter-name').val();
    	var storage    = $('#wp-manga-chapter-storage').val();
    	var nameExtend = $('#wp-manga-chapter-name-extend').val();
		var chapterIndex = $('#wp-manga-chapter-index').val();
		var directlink = $('#wp-manga-chapter-link').val();

    	if ( !name || '' == name ) {
            mangaSingleMessage( 'You need to input Chapter\'s name', '#chapter-upload-msg', false );
    		return;
    	}

    	var volume = $('#chapter-upload #wp-manga-volume').val();

    	var fd = new FormData();
    	fd.append( 'post', post );
    	fd.append( 'name' , name );
        fd.append( 'nameExtend', nameExtend );
		fd.append( 'chapterIndex', chapterIndex );
        fd.append( 'storage' , storage );
        fd.append( 'volume', volume );
		fd.append('directlink', directlink);
        fd.append('nonce', wpManga.nonce);
		
        if( storage == 'picasa' ){
            fd.append( 'picasa_album', $('#chapter-upload #wp-manga-blogspot-albums').val() );
        }
		
		if(storage == 'gphotos'){
			fd.append( 'gphotos_album', $('#chapter-upload #wp-manga-gphotos-albums').val() );
		}

        var file_data = $('#wp-manga-chapter-file')[0].files; // for multiple files

        if ( file_data.length == 0 && directlink == '') {
            mangaSingleMessage( 'You need to upload file or enter a direct link to the file', '#chapter-upload-msg', false );
    		return;
	    }
		
		if(file_data.length > 0){
			var validate = validateFile( file_data[0], '#chapter-upload-msg' );
			if( validate == false ) {
				return;
			}

			for(var i = 0; i < file_data.length; i++){
				fd.append("file_" + i, file_data[i]);
			}
		}

        if( $('input[name="chapter-overwrite"]').is(':checked') ){
            if( $('input#overwrite').is(':checked') ){
                fd.append( 'overwrite', true );

                if( $('input[name="chapter-to-overwrite"]').is(':checked') ){
                    fd.append( 'c_overwrite', $('input[name="chapter-to-overwrite"]:checked').val() );
                }else{
                    mangaSingleMessage( 'Please choose chapter to overwrite', '#chapter-upload-msg', false );
                }
            }else{
                fd.append( 'overwrite', false );
            }
        }

        showLoading();
		
        $('#chapter-overwrite').hide();
        $('#chapters-overwrite-select').hide();
        $('input[name="chapter-overwrite"]').prop( 'checked', false );
		
		window.mangaChapterFileUpload = fd;
	    $(document).trigger('wp_manga_content_chapter_file_upload_trigger', [window.mangaChapterFileUpload]);
	    fd = window.mangaChapterFileUpload;

	    $.ajax({
            url: wpManga.ajax_url + '?action=wp-manga-upload-chapter',
            type: 'POST',
            data: fd,
            enctype: 'multipart/form-data',
		    cache: false,
		    contentType: false,
		    processData: false,
	        success: function( resp ) {

                //if success
                if ( resp.success == true ) {

                    updateChaptersList();

                    //clear fields when success
                    clearFormFields( '.chapter-input' );

                    mangaSingleMessage( 'Upload Complete!', '#chapter-upload-msg', true );

	            }else if( resp.success == false && resp.data !== undefined && resp.data.error == 'chapter_existed' ) { //if false and chapter is existed

                    mangaSingleMessage( resp.data.message, '#chapter-upload-msg', false );
                    $('#chapter-overwrite').show();

                    if( resp.data.output !== undefined ) {
                        $('.chapter-overwrite-contains').html( resp.data.output );
                    }

                }else if( resp.success == false && resp.data.error == 'storage_error' ){
                    mangaSingleMessage( resp.data.message, '#chapter-upload-msg', false );
                }else if( resp.success == false && typeof resp.data.message !== 'undefined' ){
                    mangaSingleMessage( resp.data.message, '#chapter-upload-msg', false );
                }else{
                    mangaSingleMessage( resp.data, '#chapter-upload-msg', false );
                }

	        },
            complete: function( jqXHR, textStatus ){
                hideLoading();
                cleanTempFolder();
                if( storage == 'picasa' ){
                    updatePicasaAlbumDropdown( $('#chapter-upload #wp-manga-blogspot-albums').val() );
                }
				
				if(textStatus == 'error'){
					alert('Something happened. Please check server error logs'); 
				}
            }
	    });

    });

    $(document).on( 'click', 'input[name="chapter-overwrite"]', function(){
        if( $('#overwrite').is(':checked') ) {
            $('#chapters-overwrite-select').show();
        }else{
            $('#chapters-overwrite-select').hide();
        }
    });

    //upload manga
    $(document).on('click', '#wp-manga-upload', function(e){

        e.preventDefault();

        var mangaFile = $('#wp-manga-file')[0].files[0];
        var volume = $('#manga-upload #wp-manga-volume').val();
        var storage = $('select[name="manga-storage"]').val();
        var postID = $('input[name="post_ID"]').val();

        if( $('#wp-manga-file')[0].files.length == 0 && $('#wp-manga-file-path').val() == '') {
            mangaSingleMessage( 'Please choose file first or enter direct file path', '#manga-upload-msg', false );
            return false;
        }
        showLoading();
		
        var formData = new FormData;
        formData.append( 'mangaFile', mangaFile );
        formData.append( 'volume', volume );
        formData.append( 'storage', storage );
        formData.append( 'postID', postID );
		formData.append('directlink', $('#wp-manga-file-path').val());
        formData.append('nonce', wpManga.nonce);
        if( storage == 'picasa' ){
            formData.append( 'picasa_album', $('#manga-upload #wp-manga-blogspot-albums').val() );
        }
		
		if(storage == 'gphotos'){
			formData.append( 'gphotos_album', $('#manga-upload #wp-manga-gphotos-albums').val() );
		}
		
		window.mangaChapterFileUploadMulti = formData;
	    $(document).trigger('wp_manga_content_chapter_file_upload_multi_trigger', [window.mangaChapterFileUploadMulti]);
	    formData = window.mangaChapterFileUploadMulti;

        jQuery.ajax({
            url : wpManga.ajax_url+'?action=wp-manga-upload',
            processData : false,
            contentType : false,
            enctype: 'multipart/form-data',
            type : 'POST',
            data : formData,
            success : function( response ){
                if( response.success == true ) {
                    mangaSingleMessage( response.data, '#manga-upload-msg', true );
                    updateChaptersList();
                }

                if( response.success == false ) {
                    mangaSingleMessage( typeof response.data.message !== 'undefined' ? response.data.message : response.data, '#manga-upload-msg', false );
                }
            },
            complete: function( jqXHR, textStatus ){
                hideLoading();
                cleanTempFolder();
                if( storage == 'picasa' ){
                    updatePicasaAlbumDropdown( $('#manga-upload #wp-manga-blogspot-albums').val() );
                }
            }
        });
    });

    function updatePicasaAlbumDropdown( current_album ){

        $.ajax({
            url : wpManga.ajax_url,
            type: 'POST',
            data : {
                action : 'update_picasa_album_dropdown',
                current_album : current_album,
                nonce: wpManga.nonce
            },
            success : function( response ){
                if( response.success == true ){
                    $('.wp-manga-blogspot-albums').each(function( i, e ){
                        $(this).empty();
                        $(this).append( response.data );
                    });
                }else{
                    return;
                }
            }
        });

    }

    //upload content chapter
    $(document).on( 'click', '#chapter-content-upload-btn', function(e){

        e.preventDefault();

        var volume = $('#chapter-content-upload .wp-manga-volume').val(),
            file = $('#chapter-content-upload #chapter-content-file')[0].files,
            chapterType = $('input[name="wp-manga-chapter-type"]').val(),
            postID = $('input[name="postID"]').val(),
            messageID = '#chapter-content-upload-msg';

        if( file.length === 0 && $('#wp-manga-chapter-file-path').val() == '' ){
            mangaSingleMessage( 'Please upload .zip file or enter direct link to the file on your server', messageID, false );
        }

        var fd = new FormData();

		if(file.length > 0)
			fd.append( 'file', file[0] );
		fd.append('directlink', $('#wp-manga-chapter-file-path').val());
        fd.append( 'volume', volume );
        fd.append( 'chapterType', chapterType );
        fd.append( 'postID', postID );
        fd.append( 'action', 'chapter_content_upload' );
		fd.append('nonce', wpManga.nonce);
		window.mangaContentChapterUploadMulti = fd;
	    $(document).trigger('wp_manga_content_chapter_upload_multi_chap', [window.mangaContentChapterUploadMulti])
	    fd = window.mangaContentChapterUploadMulti;

        $.ajax({
            url : wpManga.ajax_url,
            type : 'POST',
            data: fd,
            enctype: 'multipart/form-data',
		    cache: false,
		    contentType: false,
		    processData: false,
            beforeSend : function(){
                showLoading();
            },
            success : function( response ){
                if( response.success ){
                    clearFormFields('#chapter-content-upload');
                    mangaSingleMessage( 'Upload Complete', messageID, true );
                } else{
                  mangaSingleMessage( typeof response.data.message !== "undefined" ? response.data.message : response.data, messageID, false );
                }
            },
            complete : function( e, xhr ){
                hideLoading();
                updateChaptersList(); 
            }
        });
    });

    // upload chapter by import cloud server album
    $('#import-album').on( 'click', function(e){
        e.preventDefault();

        var cloudStorage = $('#wp-manga-cloud-storage').val();

        if( cloudStorage == 'picasa' ){
            var album = $('#blogspot-select-album').val();
        }else if( cloudStorage == 'imgur' ){
            var album = $('#imgur-album-url').val();
        }else if( cloudStorage == 'amazon' ){
            var album = $('#amazon-folder-url').val();
        }else if( cloudStorage == 'flickr' ){
            var album = $('#flickr-album-url').val();
        } else {
			var album = $('#' + cloudStorage + '-albums').val();
            //mangaSingleMessage( 'Invalid Storage', '#chapter-upload-msg', false );
            //return;
        }

        var regex = new RegExp( cloudStorageURLRegex[ cloudStorage ] );

        if( regex.exec( album ) == null ){
            mangaSingleMessage( 'Invalid URL', '#chapter-upload-msg', false );
			return;
        }

        if( album == null || album == '' ){
            mangaSingleMessage( 'Album cannot be empty', '#chapter-upload-msg', false );
			return;
        }

        var post       = $('input[name="postID"]').val();
    	var name       = $('#wp-manga-chapter-name').val();
    	var storage    = $('#wp-manga-cloud-storage').val();
    	var nameExtend = $('#wp-manga-chapter-name-extend').val();

    	if ( !name || '' == name ) {
            mangaSingleMessage( 'You need to input Chapter\'s name', '#chapter-upload-msg', false );
    		return;
    	}

    	var volume = $('#chapter-upload #wp-manga-volume').val();

        $.ajax({
            url : wpManga.ajax_url,
            method : 'POST',
            data : {
                action:     'manga_import_album_chapter',
                post:       post,
                name:       name,
                nameExtend: nameExtend,
                volume:     volume,
                storage:    storage,
                album:      album,
                nonce: wpManga.nonce
            },
            beforeSend: function(){
                showLoading();
            },
            success : function( response ){
                if( response.success ){
                    updateChaptersList();

                    //clear fields when success
                    clearFormFields( '.chapter-input' );

                    if( storage == 'picasa' ){
                        $('#blogspot-select-album').empty();
                    }

                    mangaSingleMessage( 'Created Complete!', '#chapter-upload-msg', true );
                }else if( typeof response.data.message !== 'undefined' ){
                    mangaSingleMessage( response.data.message, '#chapter-upload-msg', false );
                }else{
                    mangaSingleMessage( 'Create failed! Please try again', '#chapter-upload-msg', false );
                }
            },
            complete : function(){
                hideLoading();
            }
        });

    } );
});
