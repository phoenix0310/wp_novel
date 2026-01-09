function attach_event_multi_select_chapters() {
	jQuery('.vol_select_all').each(function () {
		jQuery(this).on('change', function () {
			var is_check = jQuery(this).is(":checked");
			var vol_id = jQuery(this).val();
			jQuery('.chapter_vol_' + vol_id).prop('checked', is_check);
		});
	});

	jQuery('#select_multi_action').on('change', function () {
		if (jQuery(this).val() == 'move') {
			jQuery('#admin-wp-manga-move-chapters').show();
		} else {
			jQuery('#admin-wp-manga-move-chapters').hide();
		}
	})

	jQuery('#btn_do_multi_action').on('click', function (e) { e.stopPropagation(); return admin_manga_do_multi_action(jQuery); });
}

function ajaxGetChapterModalContent(postID, chapterID) {

	jQuery.ajax({
		url: wpManga.ajax_url,
		type: 'GET',
		dataType: 'json',
		data: {
			action: 'wp-manga-get-chapter',
			postID: postID,
			chapterID: chapterID,
			type: jQuery('input[name="wp-manga-chapter-type"][type="hidden"]').val(),
			nonce: wpManga.nonce
		},
		success: function (resp) {
			hideModalLoading();

			if (resp.success == true) {
				modalGetChapter(resp.data);
			} else {
				hideModal();
				alert(resp.data);
			}
		},
	});
}

function admin_manga_do_multi_action($) {
	var selected_items = $('.chapter_select_item');
	if (selected_items.length > 0) {
		var ids = [];
		selected_items.each(function (key, item) {
			if ($(item).is(':checked')) {
				ids.push($(item).val());
			}
		})

		if (ids.length > 0) {
			var action = $('#select_multi_action').val();
			switch (action) {
				case 'delete':
					if (confirm(wpManga.strings.confirmation)) {
						$.ajax({
							url: wpManga.ajax_url,
							type: 'POST',
							data: {
								action: 'wp-manga-delete-chapter',
								postID: $('#btn_do_multi_action').val(),
								chapterID: 0,
								chapterIDs: ids,
								nonce: wpManga.nonce
							},
							success: function (resp) {
								if (resp.success) {
									for (var i = 0; i < ids.length; i++) {
										var id = ids[i];
										$('#chapter_select_' + id).parent().remove();
									}
								}
							},
						});
					}
					break;
				case 'move':
					if ($('#admin-wp-manga-move-chapters-select-vols').val() != '') {
						var vol_id = $('#admin-wp-manga-move-chapters-select-vols').val();

						if (confirm(wpManga.strings.confirmation)) {
							$.ajax({
								url: wpManga.ajax_url,
								type: 'POST',
								data: {
									action: 'wp-manga-move-chapter',
									postID: $('#btn_do_multi_action').val(),
									chapterID: 0,
									chapterIDs: ids,
									volID: vol_id
								},
								success: function (resp) {
									if (resp.success) {
										updateChaptersList();
									}
								},
							});
						}
					}
					break;
				default:
					break;
			}
		}
	}

	return false;
}

function cleanTempFolder(postID) {

	jQuery.ajax({
		url: wpManga.ajax_url,
		type: 'POST',
		data: {
			action: 'wp_manga_clean_temp_folder',
			postID: postID,
		},
	});

}

jQuery(document).ready(function ($) {

	//bring to tabs
	var status = $('#status-section'),
		release = $('#release-year-section'),
		author = $('#author-section'),
		artist = $('#artist-section'),
		genre = $('#genre-section'),
		tags = $('#tags-section'),
		views = $('#views-section');

	$('#tagsdiv-wp-manga-release').appendTo(release);
	$('#manga_status_settings').appendTo(status);
	$('#tagsdiv-wp-manga-author').appendTo(author);
	$('#tagsdiv-wp-manga-artist').appendTo(artist);
	$('#wp-manga-genrediv').appendTo(genre);
	$('#manga_views').appendTo(views);
	$('#tagsdiv-wp-manga-tag').appendTo(tags);

	$('.wp-manga-content h2').removeClass('ui-sortable-handle');
	$('.wp-manga-content h2').removeClass('hndle');

	$('#volumes-list').sortable({
		stop: function (evt, ui) {
			// save volumes order
			var vols = [];
			$('#volumes-list > li').each(function (idx) {
				var vol_id = $(this).data('volume-id');
				vols.push({ 'id': vol_id, 'index': ($('#volumes-list > li').length - idx - 1) });
			});

			$.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'wp_manga_save_volumes_order',
					vols: vols,
					nonce: wpManga.nonce
				},
				complete: function (res) {
					// do nothing
				}
			});
		},
		tolerance: "intersect"
	});

	//save manga type
	$(document).on('click', 'input[name="wp-manga-chapter-type"]', function () {

		$('.wp-manga-info.wp-manga-tabs-wrapper').removeClass('choosing-manga-type');

		var postID = $('input[name="postID"]').val(),
			chapterType = $(this).val();

		//append manga chapter type label
		$('<span class="wp-manga-chapter-type-label"></span>').insertAfter('#manga-information-metabox > h2 > span');

		//set value for input wp-manga-chapter-type
		$('input[name="wp-manga-chapter-type"]').val(chapterType);

		var label = $('span.wp-manga-chapter-type-label');
		label.addClass(chapterType);

		$('#manga-information-metabox').addClass(chapterType);

		chapterTypeLabel = chapterType == 'manga' ? 'image' : chapterType;
		label.text(chapterTypeLabel);

		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp_manga_save_chapter_type',
				postID: postID,
				chapterType: chapterType,
				nonce: wpManga.nonce
			},
			success: function () {
				$('.choose-manga-type').remove();
			}
		});

	});

	//tabs
	$(document).on('click', '.wp-manga-tabs > ul > li', function (e) {
		e.preventDefault();
		$('.wp-manga-tabs > ul > li').removeClass('tab-active');
		$(this).addClass('tab-active');

		$('.tab-content').hide();
		var content = $(this).children().attr('href');
		$(content).show();
	});

	$(document).on('click', 'input[name="wp-manga-chapter-type"]', function () {
		showChapterTypeTabs();
	});

	//show chapter type compatible tabs
	function showChapterTypeTabs() {

		if ($('input[name="wp-manga-chapter-type"]:checked').length !== 0) {
			var chapterType = $('input[name="wp-manga-chapter-type"]:checked').val();
			$('input[name="wp-manga-chapter-type"][type="hidden"]').val(chapterType);
		} else if ($('input[name="wp-manga-chapter-type"]').length !== 0) {
			var chapterType = $('input[name="wp-manga-chapter-type"][type="hidden"]').val();
		}

		if (chapterType == 'manga') {
			$('.manga-tab-select').show();
			$('.text-tab-select').hide();
			$('.video-tab-select').hide();
		} else if (chapterType == 'text') {
			$('.manga-tab-select').hide();
			$('.text-tab-select').show();
			$('.video-tab-select').hide();
		} else if (chapterType == 'video') {
			$('.manga-tab-select').hide();
			$('.text-tab-select').hide();
			$('.video-tab-select').show();
		} else {
			$('.manga-tab-select').show();
			$('.text-tab-select').hide();
			$('.video-tab-select').hide();
		}

		$('.wp-manga-content .chapter-content-tab').each(function (i, e) {
			if (chapterType == 'manga' && !$(this).hasClass('manga-chapter-tab')) {
				$(this).remove();
			} else if ((chapterType == 'text' || chapterType == 'video') && !$(this).hasClass('chapter-content-tab')) {
				$(this).remove();
			}
		});
	}

	$(document).on('click', '.wp-manga-modal-dismiss', function () {
		hideModal();
	});

	$("#manga-sortable").sortable();

	window.doingAjax = false;
	var response = false;

	//edit chapter
	$(document).on('click', '.wp-manga-edit-chapter', function (e) {

		e.preventDefault();

		var t = $(this);
		var postID = $('input[name="postID"]').val();
		var chapterID = t.attr('data-chapter');

		if (chapterID == $('#wp-manga-modal-chapter').val()) {
			showModal(false);
		} else {

			if (doingAjax == false) {

				doingAjax = true;

				showModal();
				showModalLoading();

				$('#wp-manga-modal-post-id').val(postID);
				$('#wp-manga-modal-chapter').val(chapterID);

				ajaxGetChapterModalContent(postID, chapterID);

				doingAjax = false;
			}

		}

	});

	//save chapter
	$(document).on('click', '#wp-manga-save-paging-button', function (e) {

		e.preventDefault();

		showModalLoading();

		var postID = $('#wp-manga-modal-post-id').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		var chapterNewName = $('#wp-manga-modal-chapter-name').val();
		var chapterNameExtend = $('#wp-manga-modal-chapter-extend-name').val();
		var chapterIndex = $('#wp-manga-modal-chapter-index').val();
		var volume = $('#wp-manga-modal-content #wp-manga-volume').val();
		var chapterType = $('input[name="wp-manga-chapter-type"]').val();
		var chapterSEO = $('#manga-seo-desc').val();
		var chapterWarning = $('#manga-warning-text').val();
		var chapterStatus = $('#chapter_status').val();
		var chapterAMPHeight = $('#chapter-amp-height').length > 0 ? $('#chapter-amp-height').val() : '';

		var chapter_data = {
			action: 'wp-manga-save-chapter-paging',
			postID: postID,
			chapterID: chapterID,
			chapterNewName: chapterNewName,
			chapterNameExtend: chapterNameExtend,
			chapterIndex: chapterIndex,
			volume: volume,
			chapterType: chapterType,
			chapterSEO: chapterSEO,
			chapterWarning: chapterWarning,
			chapterStatus: chapterStatus,
			chapterAMPHeight: chapterAMPHeight,
			nonce: wpManga.nonce
		};

		$(document).trigger('wp_manga_before_admin_save_chapter', [postID, chapterID, chapter_data]);

		if (chapterType == 'text' || chapterType == 'video') {

			tinyMCE.triggerSave();

			var chapterContent = $('textarea#wp-manga-chapter-content-wp-editor').val();

			chapter_data.chapterContent = chapterContent;

			window.mangaChapterSaveTrigger = chapter_data;
			$(document).trigger('wp_manga_content_chapter_save', [window.mangaChapterSaveTrigger]);
			chapter_data = window.mangaChapterSaveTrigger;

			$.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: chapter_data,
				success: function (resp) {

					if (resp.success == true) {
						hideModal();
						updateChaptersList();
					} else {
						hideModal();
						alert(resp.data);
					}
					doingAjax = false;
				},
				complete: function () {
					hideModalLoading();
				}
			});

		} else {
			var images = $('.wp-paging');
			var paging = [];

			$.each(images, function (i, e) {
				paging.push($(e).data('src'));
			})

			var deletedImages = $('#manga-sortable input[name="deleted-images[]').map(function () {
				return $(this).val();
			}).get();

			var storage = $('#manga-storage-dropdown').val();

			chapter_data.storage = storage;
			chapter_data.deletedImages = deletedImages;
			chapter_data.paging = paging;

			window.mangaChapterSaveTrigger = chapter_data;
			$(document).trigger('wp_manga_content_chapter_save', [window.mangaChapterSaveTrigger]);
			chapter_data = window.mangaChapterSaveTrigger;

			$.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: chapter_data,
				success: function (resp) {

					hideModalLoading();

					if (resp.success == true) {
						hideModal();
						updateChaptersList();
					} else {
						hideModal();
						alert('Can\'t Process.');
					}
					doingAjax = false;
				},
				complete: function (err) {
					hideModalLoading();
				}
			});
		}
	})

	$(document).on('click', '#wp-manga-dismiss-modal', function (e) {
		hideModal();
	});

	//Download Chapter
	$(document).on('click', '#wp-manga-download-chapter-button', function (e) {

		e.preventDefault();

		var confirmDownload = confirm('Are you sure you want to download this chapter?');

		if (confirmDownload == false) {
			return;
		}

		showModalLoading();

		var postID = $('#wp-manga-modal-post-id').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		var storage = $('#manga-storage-dropdown').val();

		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp-manga-download-chapter',
				postID: postID,
				chapterID: chapterID,
				storage: storage,
				nonce: wpManga.nonce
			},
			success: function (response) {

				hideModalLoading();

				if (response.success == true) {
					window.location = response.data.zip_path;
				} else {
					alert(' Something wrong happened. Please try again later ');
				}
			}
		});
	});

	//Delete Chapter from list
	$(document).on('click', "#wp-manga-delete-chapter", function (e) {

		e.preventDefault();

		var confirmDelete = confirm(wpManga.strings.delChapter);

		if (confirmDelete == false) {
			return;
		}

		$(this).parent().fadeOut();

		var postID = $('#post_ID').val();
		var chapterID = $(this).data('chapter');

		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp-manga-delete-chapter',
				postID: postID,
				chapterID: chapterID,
				nonce: wpManga.nonce
			},
			success: function (resp) {
				if (resp.success) {
					$(this).parent().remove();
				}
			},
		});
	});

	// Delete Chapter from Modal
	$(document).on('click', "#wp-manga-delete-chapter-button", function (e) {
		e.preventDefault();

		var confirmDelete = confirm(wpManga.strings.delChapter);

		if (confirmDelete == false) {
			return;
		}

		showModalLoading();

		var postID = $('#wp-manga-modal-post-id').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		var storage = $('#manga-storage-dropdown').val();
		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wp-manga-delete-chapter',
				postID: postID,
				chapterID: chapterID,
				storage: storage,
				nonce: wpManga.nonce
			},
			success: function (resp) {
				hideModalLoading();
				if (resp.success) {
					updateChaptersList();
					hideModal();
				} else {
					alert('Can\'t Process.');
					hideModal();
				}
				doingAjax = false;
			},
		});
	});

	// Empty images from Modal
	$(document).on('click', "#wp-manga-empty-chapter-button", function (e) {
		e.preventDefault();

		var confirmDelete = confirm(wpManga.strings.delChapter);

		if (confirmDelete == false) {
			return;
		}

		showModalLoading();

		var postID = $('#wp-manga-modal-post-id').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wp-manga-empty-chapter',
				postID: postID,
				chapterID: chapterID,
				nonce: wpManga.nonce,
				storage: $('#manga-storage-dropdown').val()
			},
			success: function (resp) {
				hideModalLoading();
				if (resp.success) {
					$('#manga-sortable li').remove();
				} else {
					alert('Can\'t Process.');
					hideModal();
				}
				doingAjax = false;
			},
		});
	});

	attach_event_multi_select_chapters();

	// storage change in modal
	$(document).on('change', '#manga-storage-dropdown', function (e) {
		e.preventDefault();

		showModalLoading();

		var current = $(this).val();
		var postID = $('#wp-manga-modal-post-id').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		$.ajax({
			url: wpManga.ajax_url,
			type: 'GET',
			dataType: 'json',
			data: {
				action: 'wp-manga-get-chapter',
				postID: postID,
				chapterID: chapterID,
				nonce: wpManga.nonce
			},
			success: function (resp) {
				hideModalLoading();

				if (resp.success) {
					modalGetChapter(resp.data, current);
				} else {
					alert(resp.data);
				}
				doingAjax = false;
			},
		});
	})

	//add new volume
	$(document).on('click', 'button#new-volume', function (e) {
		e.preventDefault();
		$(this).next('.new-volume').show();
	});

	$(document).on('click', 'button#add-new-volume', function (e) {
		e.preventDefault();
		var volumeDropdown = $('.wp-manga-volume');
		var volumeName = $(this).parent().children('input[name="volume-name"]').val();
		var thisVolumeSelect = $(this).parents().children('.wp-manga-volume');
		var postID = $('input[name="postID"]').val();

		if (volumeName == '') {
			alert('Volume name cannot be empty');
			return;
		}

		$('.new-volume').addClass('loading');
		jQuery.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp-manga-create-volume',
				postID: postID,
				volumeName: volumeName,
				nonce: wpManga.nonce
			},
			success: function (response) {
				if (response.success == true) {
					$('.new-volume').fadeOut();
					$('.add-new-volume').show();
					$('input#volume-name').val('');
					$('.new-volume').removeClass('loading');

					//add new volume to dropdown
					var volumeItem = '<option value="' + response.data + '">' + volumeName + '</option>';

					volumeDropdown.each(function (i, e) {
						$(this).append(volumeItem);
					});

					thisVolumeSelect.val(response.data);

					updateChaptersList();
				}
			}
		});
	});

	//volume toggle
	$(document).on('click', 'h3.volume-title', function () {
		var volume = $(this).parent();

		volume.toggleClass('expanded');

		if (volume.hasClass('expanded')) {
			$(this).next().css('height', $(this).next().prop("scrollHeight"));
		} else {
			$(this).next().css('height', '0');
		}
	});

	//duplicate chapter
	$(document).on('click', '#duplicate-btn', function (e) {

		e.preventDefault();

		showModalLoading();

		var postID = $('input[name="postID"]').val();
		var chapterID = $('#wp-manga-modal-chapter').val();
		var duplicateServer = $('#duplicate-server').val();

		jQuery.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp-manga-duplicate-server',
				postID: postID,
				chapterID: chapterID,
				duplicateServer: duplicateServer,
				nonce: wpManga.nonce
			},
			success: function (response) {

				if (response.success == true) {
					modalContentRefresh();
					ajaxGetChapterModalContent(postID, chapterID);
					alert(response.data);
				}

			},
			error: function (response, textStatus) {
				alert(textStatus);
			},
			complete: function (jqXHR, textStatus) {

				hideModalLoading();

				cleanTempFolder();
			}
		});
	});

	$(document).on('click', '#remove-storage-btn', function (e) {
		e.preventDefault();
		if (confirm('Are you sure want to delete this chapter storage?')) {
			showModalLoading();

			var postID = $('input[name="postID"]').val();
			var chapterID = $('#wp-manga-modal-chapter').val();
			var storage = $('#manga-storage-dropdown').val();

			jQuery.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'wp-manga-remove-storage',
					postID: postID,
					chapterID: chapterID,
					storage: storage,
					nonce: wpManga.nonce
				},
				success: function (response) {

					if (response.success == true) {
						modalContentRefresh();
						ajaxGetChapterModalContent(postID, chapterID);
						alert(response.data);
					}

				},
				error: function (response, textStatus) {
					alert(textStatus);
				},
				complete: function (jqXHR, textStatus) {

					hideModalLoading();
				}
			});
		}
	});

	//delete image in chapter
	$(document).on('click', '.delete-page', function (e) {
		e.preventDefault();
		var thisPage = $(this).closest('li');

		// add it to remove images input
		var sortable = $('#manga-sortable');

		sortable.append('<input type="hidden" name="deleted-images[]" value="' + thisPage.data('src') + '">');

		thisPage.remove();
	});

	//show blogspot/gphotos albums to choose
	$(document).on('change', 'select[name="manga-storage"]', function () {
		if ($(this).val() == 'picasa') {
			$('#manga-upload #wp-manga-blogspot-albums').show();
		} else {
			$('#manga-upload #wp-manga-blogspot-albums').hide();
		}

		if ($(this).val() == 'gphotos') {
			$('#manga-upload #wp-manga-gphotos-albums').show();
		} else {
			$('#manga-upload #wp-manga-gphotos-albums').hide();
		}
	});

	$(document).on('change', 'select[name="wp-manga-chapter-storage"]', function () {
		if ($(this).val() == 'picasa') {
			$('#chapter-upload #wp-manga-blogspot-albums').show();
		} else {
			$('#chapter-upload #wp-manga-blogspot-albums').hide();
		}

		if ($(this).val() == 'gphotos') {
			$('#chapter-upload #wp-manga-gphotos-albums').show();
		} else {
			$('#chapter-upload #wp-manga-gphotos-albums').hide();
		}
	});

	//show volume name input
	$(document).on('click', '#edit-volume-name', function (e) {
		var volumeInputField = $(this).parents('.volume-title').children('.volume-input-field'),
			volumeTitle = $(this).parents('.volume-title').children('span');

		volumeInputField.show();
		volumeInputField.focus();
		volumeTitle.css('opacity', 0);

	});

	//turn off volume toggle when click on child element
	$(document).on('click', 'h3.volume-title > *', function (e) {
		e.stopPropagation();
	});

	$(document).on('focusout', '.volume-title > input.volume-input-field', function () {

		//hide volume name field
		$(this).hide();

		var oldName = $(this).parent('h3.volume-title').children('span'),
			newName = $(this).val(),
			volumeID = $(this).parents('.manga-single-volume').data('volume-id'),
			postID = $('input[name="post_ID"]').val();

		//if volume name is different, then ajax updating
		if (oldName.text() !== newName) {

			jQuery.ajax({
				url: wpManga.ajax_url,
				type: 'POST',
				data: {
					action: 'update_volume_name',
					postID: postID,
					volumeID: volumeID,
					volumeName: newName,
					nonce: wpManga.nonce
				},
			});
		}

		//assign new volume name for volume title
		oldName.text(newName);
		oldName.css('opacity', 1);
	});

	$(document).on('click', '#wp-manga-delete-volume', function (e) {

		var question = wpManga.strings.delVolume;

		var thisVolume = $(this).parents('li.manga-single-volume'),
			volumeID = thisVolume.data('volume-id'),
			postID = $('input[name="post_ID"]').val();

		if (volumeID == 0) {
			question += ' ' + wpManga.strings.delNoVolChap;
		}

		var confirmDelete = confirm(question);

		if (confirmDelete !== true) {
			return;
		}

		if (volumeID == 0) {
			thisVolume.children('ul').hide();
			$(this).hide();
		} else {
			thisVolume.hide();
		}

		$.ajax({
			url: wpManga.ajax_url,
			type: 'POST',
			data: {
				action: 'wp_manga_delete_volume',
				volumeID: volumeID,
				postID: postID,
				nonce: wpManga.nonce
			},
			success: function () {
				if (volumeID == 0) {
					thisVolume.children('ul').hide();
					$(this).remove();
				} else {
					thisVolume.remove();
				}
			}

		});
	});

	//prevent submit when focusing on manga fields
	$(document).on('submit', 'form', function (e) {

		if ($('#manga-information-metabox input[type="text"].disable-submit').is(':focus')) {
			$('.button').each(function (i, e) {
				if ($(this).hasClass('disabled')) {
					$(this).removeClass('disabled');
				}
			});

			if ($('input[type="text"][name="volume-name"]').is(':focus')) {
				$('button#add-new-volume').trigger('click');
			}

			if ($('.volume-title > input.volume-input-field').is(':focus')) {
				$('.volume-title > input.volume-input-field').each(function (i, e) {
					if ($(this).is(':focus')) {
						$(this).focusout();
					}
				});
			}

			return false;
		}

	});

	//append chapter type label into metabox
	$(document).ready(function () {

		//if it's new chapter
		if ($('.choose-manga-type').length > 0) {
			return;
		}

		var chapterType = $('input[name="wp-manga-chapter-type"]').val();

		$('<span class="wp-manga-chapter-type-label"></span>').insertAfter('#manga-information-metabox > h2 > span');

		var label = $('span.wp-manga-chapter-type-label');
		label.addClass(chapterType);
		label.text(chapterType);

		$('#manga-information-metabox').addClass(chapterType);

	});

	$('input[name="wp-manga-upload-type"]').on('change', function () {
		var type = $(this).val();
		$(this).parents('.tab-content').find('.upload-type').each(function (i, e) {
			if ($(e).hasClass(type)) {
				$(e).show();
			} else {
				$(e).hide();
			}
		});
	});


	$('#wp-manga-cloud-storage').on('change', function (e) {

		var storage = $(this).val();

		$(this).parents('.select-album').children().each(function () {
			if ($(this).hasClass(storage + '-import')) {
				$(this).show();
			} else if (!$(this).hasClass('wp-manga-form-group')) {
				$(this).hide();
			}
		});
	});

	$('#blogspot-search-album').on('click', function (e) {

		e.preventDefault();

		var thisIcon = $(this).find('span');
		var album = $(this).prev('input[name="blogspot-album-name"]').val();

		$.ajax({
			url: wpManga.ajax_url,
			method: 'GET',
			data: {
				action: 'blogspot_search_album',
				album: album,
				nonce: wpManga.nonce
			},
			beforeSend: function () {
				thisIcon.removeClass('fa-search');
				thisIcon.addClass('fa-spinner fa-spin');
			},
			success: function (response) {
				if (response.success && typeof response.data.data !== 'undefined') {

					var albumSelect = $('#blogspot-select-album');

					albumSelect.empty();

					$(response.data.data).each(function (i, e) {
						albumSelect.append('<option value="' + e.id + '">' + e.title + '(having ' + e.numphotos + ' items)' + '</option>');
					});

					albumSelect.parents('.wp-manga-form-group').show();
				} else if (!response.success && typeof response.data.message !== 'undefined') {
					mangaSingleMessage(response.data.message, '#chapter-upload-msg', false);
				}
			},
			complete: function () {
				thisIcon.addClass('fa-search');
				thisIcon.removeClass('fa-spinner fa-spin');
			}
		});

	});

	$('#gphotos-search-album').on('click', function (e) {

		e.preventDefault();

		var thisIcon = $(this).find('span');
		var album = $(this).prev('input[name="gphotos-album-name"]').val();

		$.ajax({
			url: wpManga.ajax_url,
			method: 'GET',
			data: {
				action: 'gphotos_search_album',
				album: album,
				nonce: wpManga.nonce
			},
			beforeSend: function () {
				thisIcon.removeClass('fa-search');
				thisIcon.addClass('fa-spinner fa-spin');
			},
			success: function (response) {
				if (response.success && typeof response.data.data !== 'undefined') {

					var albumSelect = $('#gphotos-albums');

					albumSelect.empty();

					$(response.data.data).each(function (i, e) {
						albumSelect.append('<option value="' + e.id + '">' + e.title + '(having ' + e.numphotos + ' items)' + '</option>');
					});

					albumSelect.parents('.wp-manga-form-group').show();

					$('#chapter-upload-msg').hide();
				} else if (!response.success && typeof response.data.message !== 'undefined') {
					mangaSingleMessage(response.data.message, '#chapter-upload-msg', false);
				}
			},
			complete: function () {
				thisIcon.addClass('fa-search');
				thisIcon.removeClass('fa-spinner fa-spin');
			}
		});

		
	});

	$('#gphotos-create-album').on('click', function (e) {

		e.preventDefault();

		var thisIcon = $(this).find('span i.fa-spinner');
		var album = $(this).parent().children('input[name="gphotos-album-name"]').val();

		$.ajax({
			url: wpManga.ajax_url,
			method: 'GET',
			data: {
				action: 'gphotos_create_album',
				album: album,
				nonce: wpManga.nonce
			},
			beforeSend: function () {
				thisIcon.show();
			},
			success: function (response) {
				if (response.success) {

					alert(response.data);
					$('#gphotos-search-album').trigger('click');
				} else if (!response.success && typeof response.data.message !== 'undefined') {
					mangaSingleMessage(response.data.message, '#chapter-upload-msg', false);
				}
			},
			complete: function () {
				thisIcon.hide();
			}
		});

		return false;
	});

	jQuery(document).on('click', '#btn_upload_chapter_images', function (e) {
		if (jQuery('#chapter_upload_images').prop('files').length > 0) {
			var file_data = jQuery('#chapter_upload_images').prop('files')[0];

			var form_data = new FormData;

			var chapter_id = $('#wp-manga-modal-chapter').val();
			var chapter_update_time = $('#chapter_update_time').is(':checked');

			form_data.append('file', file_data);
			form_data.append('chapter_id', chapter_id);
			form_data.append('storage', $('#manga-storage-dropdown').val());
			form_data.append('chapter_update_time', chapter_update_time ? 1 : 0);
			form_data.append('nonce', wpManga.nonce);

			$('#btn_upload_chapter_images .loading').show();
			$('#upload_chapter_images_message').html('');
			if (!jQuery('#btn_upload_chapter_images').attr('disabled')) {
				jQuery('#btn_upload_chapter_images').attr('disabled', true);
				jQuery.ajax({
					url: wpManga.ajax_url + '?action=wp-manga-chapter-upload-images',
					processData: false,
					contentType: false,
					enctype: 'multipart/form-data',
					type: 'POST',
					data: form_data,
					success: function (response) {
						$('#btn_upload_chapter_images .loading').hide();
						jQuery('#btn_upload_chapter_images').attr('disabled', false);
						if (response.success) {
							$('#upload_chapter_images_message').removeClass('error').addClass('success');

							// clear form
							$('#chapter_upload_images').val('');
							$('#manga-sortable').html('');
							// refresh content
							ajaxGetChapterModalContent($('#wp-manga-modal-post-id').val(), chapter_id);
							$('#upload_chapter_images_message').html(response.data.message);
						} else {
							$('#upload_chapter_images_message').removeClass('success').addClass('error');
							$('#upload_chapter_images_message').html(response.data);
						}


					},
					error: function (response) {
						jQuery('#chapter_upload_images').attr('disabled', false);
						$('#btn_upload_chapter_images .loading').hide();
						$('#upload_chapter_images_message').removeClass('success').addClass('error').html(response);
					}

				});
			}
		}

		e.stopPropagation();
		return false;
	});

	$('#gphotos-search-album-modal').on('click', function (e) {

		e.preventDefault();

		var thisIcon = $(this).find('span');
		var album = $(this).prev('input[name="gphotos-album-name"]').val();

		$.ajax({
			url: wpManga.ajax_url,
			method: 'GET',
			data: {
				action: 'gphotos_search_album',
				album: album,
				nonce: wpManga.nonce
			},
			context: $(this).parent(),
			beforeSend: function () {
				thisIcon.removeClass('fa-search');
				thisIcon.addClass('fa-spinner fa-spin');
			},
			success: function (response) {
				if (response.success && typeof response.data.data !== 'undefined') {

					var albumSelect = $('#gphotos-albums-modal');

					albumSelect.empty();

					$(response.data.data).each(function (i, e) {
						albumSelect.append('<option value="' + e.id + '">' + e.title + '(having ' + e.numphotos + ' items)' + '</option>');
					});

					albumSelect.parents('.wp-manga-form-group').show();
					$(this).find('.description').removeClass('error').text('');
					
				} else if (!response.success && typeof response.data.message !== 'undefined') {
					$(this).find('.description').addClass('error').text(response.data.message);
				}
			},
			complete: function () {
				thisIcon.addClass('fa-search');
				thisIcon.removeClass('fa-spinner fa-spin');
			}
		});

		
	});

	jQuery(document).on('click', '#btn_upload_chapter_images_gphotos', function (e) {
		e.preventDefault();

        var album = $('#gphotos-albums-modal').val();

        if( album == null || album == '' ){
			$(this).closest('.gphotos-import').find('.description').addClass('error').text('Album cannot be empty');

			return;
        }

		var chapter_id = $('#wp-manga-modal-chapter').val();
        var post       = $('input[name="postID"]').val();
    	var storage    = 'gphotos';

    	var volume = $('#chapter-upload #wp-manga-volume').val();
		var chapter_update_time = $('#chapter_update_time').is(':checked');

		$('#btn_upload_chapter_images_gphotos .loading').show();
		$('#upload_chapter_images_message').html('');
		if (!jQuery('#btn_upload_chapter_images_gphotos').attr('disabled')) {
			jQuery('#btn_upload_chapter_images_gphotos').attr('disabled', true);

			$.ajax({
				url : wpManga.ajax_url,
				method : 'POST',
				data : {
					action:     'manga_update_chapter_gphotos_album',
					post:       post,
					chapter_id: chapter_id,
					volume:     volume,
					storage:    storage,
					album:      album,
					nonce: wpManga.nonce,
					chapter_update_time: chapter_update_time ? 1 : 0
				},
				success : function( response ){
					if( response.success ){
						// clear form
						$('#chapter_upload_images').val('');
						$('#manga-sortable').html('');

						ajaxGetChapterModalContent($('#wp-manga-modal-post-id').val(), chapter_id);
						$('#upload_chapter_images_message').html(response.data.message);
					} else {
						$('#upload_chapter_images_message').removeClass('success').addClass('error');
						$('#upload_chapter_images_message').html(response.data.message);
					}
				},
				complete : function(){
					jQuery('#btn_upload_chapter_images_gphotos').attr('disabled', false);
					$('#btn_upload_chapter_images_gphotos .loading').hide();
				}
			});
		}
		
		e.stopPropagation();
		return false;
	});
});
