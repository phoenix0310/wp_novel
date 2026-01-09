jQuery(document).ready(function ($) {

	$(document).on('click', 'a.wp-manga-skip', function (e) {
		e.preventDefault();

		$('.wp-manga-first-install-notice').fadeOut();
		$('.wp-manga-first-install-notice').remove();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wp_manga_skip_first_install',
			},
		});
	});

	// setting page

	$(document).on('click', '.dashicons.dashicons-arrow-down', function (e) {
		e.preventDefault();
		var t = $(this);
		var image = t.parent().find('.eg-detail img');
		image.toggleClass('show');
	})

	//replace blogspot images progress in storage page
	$(document).on('click', '#replace-blogspot-url', function (e) {
		e.preventDefault();

		var replaceWrapper = $('.replace-blogspot-wrapper');

		$('.replace-blogspot-wrapper .spinner').css('visibility', 'unset');

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'replace_blogspot_url'
			},
			success: function (response) {
				if (response.success) {
					replaceWrapper.empty();
					replaceWrapper.html('<span class="dashicons dashicons-yes"></span> Replaced successfully!');
				}
			}
		});
	});

	// imgur
	$(document).on('click', '#imgur-authorize', function (e) {
		e.preventDefault();
		var imgurClientID = $('input[name="wp_manga[imgur_client_id]"]').val();
		var imgurClientSecret = $('input[name="wp_manga[imgur_client_secret]"]').val();
		if ('' == imgurClientID || '' == imgurClientSecret) {
			if ('' == imgurClientID && '' == imgurClientSecret) {
				alert('You need to input Imgur Client ID and Client Secret');
			} else if ('' == imgurClientID) {
				alert('You need to input Imgur Client ID');
			} else if ('' == imgurClientSecret) {
				alert('You need to input Imgur Client Secret');
			}
		} else {
			jQuery.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'wp-manga-imgur-save-credential',
					imgurClientID: imgurClientID,
					imgurClientSecret: imgurClientSecret,
				},
				success: function (response) {
					if (response.success) {
						window.location = "https://api.imgur.com/oauth2/authorize?response_type=token&state=imgur&client_id=" + imgurClientID;
					}
				},
				complete: function (xhr, textStatus) {
				}
			});
		}
	});

	// flickr
	$(document).on('click', '#flickr-authorize', function (e) {
		e.preventDefault();
		var flickr_api_key = $('input[name="wp_manga[flickr_api_key]"]').val();
		var flickr_api_secret = $('input[name="wp_manga[flickr_api_secret]"]').val();
		if ('' == flickr_api_key || '' == flickr_api_secret) {
			if ('' == flickr_api_key && '' == flickr_api_secret) {
				alert('You need to input Flickr API Key and API Secret');
			} else if ('' == flickr_api_key) {
				alert('You need to input Flickr API Key');
			} else if ('' == flickr_api_secret) {
				alert('You need to input Flickr API Secret');
			}
		} else {
			jQuery.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'wp-manga-flickr-save-credential',
					flickr_api_key: flickr_api_key,
					flickr_api_secret: flickr_api_secret
				},
				success: function (response) {
					if (response.success) {
						window.location = response.data[0] + '&action=authorize-flickr';
					}
				},
				complete: function (xhr, textStatus) {
				}
			});
		}
	});

	// google
	$(document).on('click', '#google-authorize', function (e) {
		e.preventDefault();
		var googleClientID = $('input[name="wp_manga[google_client_id]"]').val();
		var googleClientSecret = $('input[name="wp_manga[google_client_secret]"]').val();
		var googleRedirect = $('input[name="wp_manga[google_redirect]"]').val();
		if ('' == googleClientID || '' == googleRedirect || '' == googleClientSecret) {
			if ('' == googleClientID) {
				alert('You need to input Google Client ID');
			} else if ('' == googleClientSecret) {
				alert('You need to input Google Client Secret');
			} else if ('' == googleRedirect) {
				alert('You need to input Google Redicret URL');
			}

		} else {
			jQuery.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'wp-manga-google-save-credential',
					googleClientID: googleClientID,
					googleClientSecret: googleClientSecret,
					googleRedirect: googleRedirect,
				},
				success: function (response) {
					if (response.success) {
						window.location = "https://accounts.google.com/o/oauth2/v2/auth?scope=https://picasaweb.google.com/data/&client_id=" + googleClientID + "&redirect_uri=" + googleRedirect + "&response_type=code&access_type=offline&state=picasa&include_granted_scopes=true&prompt=consent";
					}
				},
				complete: function (xhr, textStatus) {
				}
			});
		}
	});

	var wp_manga_search_rebuild_search_text = function (obj, offset = 0) {
		jQuery(obj).attr('disabled', 'disabled');
		jQuery(obj).find('.fa-spinner').show();

		jQuery.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp_manga_rebuild_search_text',
				offset: offset
			},
			success: function (response) {
				if (response.data.end) {
					alert(response.data.message);

					jQuery(obj).removeAttr('disabled');
					jQuery(obj).find('.fa-spinner').hide();
				} else {
					console.log('Continue next 100 mangas');
					wp_manga_search_rebuild_search_text(obj, offset + 1);
				}
			},
			complete: function (xhr, textStatus) {

			}
		});
	}

	// rebuild search text
	$(document).on('click', '#manga_rebuild_search_text', function (e) {
		e.preventDefault();
		if (confirm('Have you backed up your database?')) {
			wp_manga_search_rebuild_search_text(this, 0);
		}
	});

	// on upload button click
	$('body').on('click', '.wp-manga-image-upload', function (event) {
		event.preventDefault(); // prevent default link click and page refresh

		const button = $(this)
		const imageId = button.next().next().val();

		const customUploader = wp.media({
			title: 'Insert image', // modal window title
			library: {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type: 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: false
		}).on('select', function () { // it also has "open" and "close" events
			const attachment = customUploader.state().get('selection').first().toJSON();
			button.removeClass('button').html('<img src="' + attachment.url + '">'); // add image instead of "Upload Image"
			button.next().show(); // show "Remove image" link
			button.next().next().val(attachment.id); // Populate the hidden field with image ID
		})

		// already selected images
		customUploader.on('open', function () {

			if (imageId) {
				const selection = customUploader.state().get('selection')
				attachment = wp.media.attachment(imageId);
				attachment.fetch();
				selection.add(attachment ? [attachment] : []);
			}

		})

		customUploader.open()

	});
	// on remove button click
	$('body').on('click', '.wp-manga-image-remove', function (event) {
		event.preventDefault();
		const button = $(this);
		button.next().val(''); // emptying the hidden field
		button.hide().prev().addClass('button').html('Upload image'); // replace the image with text
	});
});