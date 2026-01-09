<?php

	if ( !defined( 'ABSPATH' ) ) {
		exit;
	}

	global $post;

	$chapter_type = get_post_meta( $post->ID, '_wp_manga_chapter_type', true );
	$chapter_type = apply_filters( 'madara_manga_modal_chapter_type', $chapter_type, $post->ID );

?>

<div class="wp-manga-modal" id="wp-manga-chapter-modal" tabindex="-1" role="dialog">

    <div class="wp-manga-modal-dialog wp-manga-modal-lg unique-modal-lg wp-manga-imgur-modal-lg" role="document">

        <div class="wp-manga-modal-dismiss"></div>

        <div id="wp-manga-modal-content" class="<?php echo !empty( $chapter_type ) ? $chapter_type : ''; ?>">

            <div class="wp-manga-popup-loading">
                <div class="wp-manga-popup-loading-wrapper">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>
            </div>

            <div class="wp-manga-modal-header">
                <input type="text" id="wp-manga-modal-chapter-name" value="" name="wp-manga-modal-chapter-name"
                       placeholder="<?php esc_html_e('Chapter Name', WP_MANGA_TEXTDOMAIN);?>">
                <span>-</span>
                <input type="text" id="wp-manga-modal-chapter-extend-name" value=""
                       name="wp-manga-modal-chapter-extend-name" placeholder="<?php esc_html_e('Chapter Extend Name', WP_MANGA_TEXTDOMAIN);?>">
				<span>-</span>
				<input type="text" id="wp-manga-modal-chapter-index" style="width:70px" value=""
                       name="wp-manga-modal-chapter-index" placeholder="<?php esc_html_e('Custom Index', WP_MANGA_TEXTDOMAIN);?>">

				<?php do_action( 'madara_chapter_modal_header', $chapter_type, $post->ID ); ?>

            </div>

            <div class="wp-manga-modal-body">

				<?php do_action( 'madara_chapter_modal_before_content', $chapter_type, $post->ID ); ?>

				<?php do_action( 'madara_chapter_modal_before_chapter_meta', $chapter_type, $post->ID ); ?>
				
				<div class="wp-manga-modal-status block">
                    <label class="input-label"><strong><?php esc_html_e( 'Status: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>

					<select name="chapter_status" id="chapter_status">
						<option value="1"><?php esc_html_e('Completed', WP_MANGA_TEXTDOMAIN);?></option>
						<option value="2"><?php esc_html_e('Uploading', WP_MANGA_TEXTDOMAIN);?></option>
						<?php do_action('wp_manga_chapter_status_options');?>
					</select>

                </div>
				
				<div class="wp-manga-modal-seo-desc block">
                    <label class="input-label"><strong><?php esc_html_e( 'Chapter SEO Description: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>

					<textarea cols="50" id="manga-seo-desc"></textarea>

                </div>
				
				<div class="wp-manga-modal-warning-text block">
                    <label class="input-label"><strong><?php esc_html_e( 'Chapter warning text: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>

					<textarea cols="50" id="manga-warning-text"></textarea>

                </div>
				
				<?php if($chapter_type != 'manga'){?>
				<div class="wp-manga-modal-volume block">
					<label class="input-label"><strong><?php esc_html_e( 'Volume: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>

					<?php $GLOBALS[ 'wp_manga_functions' ]->volume_dropdown( get_the_ID(), 'manga-volume-dropdown' ) ?>

				</div>
				<?php }?>
				
				<table>
					<tr>
						<td>
							<div class="wp-manga-modal-storage block">
								<label class="input-label"><strong><?php esc_html_e( 'Storage: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>
								<select id="manga-storage-dropdown">

								</select>
								<a href="#" id="remove-storage-btn" style="display:none"> <?php esc_html_e( 'Remove this storage', WP_MANGA_TEXTDOMAIN ); ?></a>
							</div>
							<?php if($chapter_type == 'manga'){?>
							<div class="wp-manga-modal-volume block">
								<label class="input-label"><strong><?php esc_html_e( 'Volume: ', WP_MANGA_TEXTDOMAIN ); ?></strong></label>

								<?php $GLOBALS[ 'wp_manga_functions' ]->volume_dropdown( get_the_ID(), 'manga-volume-dropdown' ) ?>

							</div>
							<?php }?>
						</td>
						<td>
							<div id="chapter-upload-more">								
								<div class="gphotos-import" style="display: none">
									<h3><?php esc_html_e('Import more images from Google Photos', WP_MANGA_TEXTDOMAIN);?></h3>
									<div class="wp-manga-form-group">
										<h2>
											<label>
												<?php esc_attr_e( 'Search Album by Name', WP_MANGA_TEXTDOMAIN ); ?>
											</label>
										</h2>
										<input type="text" name="gphotos-album-name" placeholder="<?php esc_html_e( "Album Name", WP_MANGA_TEXTDOMAIN ); ?>">
										<button type="button" id="gphotos-search-album-modal" class="button button-primary">
											<span class="fas fa-search"></span>
										</button>

										<p class="description">
											<?php esc_html_e( 'Album needs to be exactly with the album you want to import in Google Photos (case sensitive). In addition, albums must be created from your site in advance', WP_MANGA_TEXTDOMAIN ); ?>
										</p>
									</div>

									<div class="wp-manga-form-group" style="display: none">
										<h2>
											<label>
												<?php esc_html_e( 'Select Album to Import', WP_MANGA_TEXTDOMAIN ); ?>
											</label>
										</h2>
										<select id="gphotos-albums-modal">
										</select>
									</div>

									<p>
										<button id="btn_upload_chapter_images_gphotos">
											<?php esc_html_e('Upload', WP_MANGA_TEXTDOMAIN);?> <span class="loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
										</button>
									</p>
								</div>
								<h3><?php esc_html_e('Upload images', WP_MANGA_TEXTDOMAIN);?></h3>
								<input type="file" id="chapter_upload_images"/> 
								<p>
									<label for="chapter_upload_images"><?php esc_html_e('Upload more images to this chapter. A single image or a zip file is accepted',WP_MANGA_TEXTDOMAIN);?>
								</p>
								<div class="border-bottom">
									<button id="btn_upload_chapter_images">
										<?php esc_html_e('Upload', WP_MANGA_TEXTDOMAIN);?> <span class="loading" style="display:none"><i class="fas fa-spinner fa-spin"></i></span>
									</button>
								</div>
								<p><label><input type="checkbox" id="chapter_update_time" /> <?php esc_html_e('Update Latest Modified Time for this manga?', WP_MANGA_TEXTDOMAIN);?></label></p>
								
								<span id="upload_chapter_images_message" class="message error"></span>
							</div>
						</td>
					</tr>
				</table>
				
				<?php do_action( 'madara_chapter_modal_after_chapter_meta', $chapter_type, $post->ID ); ?>

                <div class="description">
                    <h4>
						<?php esc_html_e( 'You can sort the page by draging picture', WP_MANGA_TEXTDOMAIN ); ?>
                    </h4>
                </div>

                <ul id="manga-sortable"></ul>

                <div class="wp-manga-chapter-content-editor">
					<?php if($chapter_type == 'video') {?>
					<h4><?php esc_html_e('To enable multiple servers, use this format in the content',WP_MANGA_TEXTDOMAIN);?></h4>
					<h5><?php esc_html_e('SERVER 1 NAME :: CONTENT 1 || SERVER 2 NAME :: CONTENT',WP_MANGA_TEXTDOMAIN);?></h5>
					<?php }?>
                    <!-- content editor for text -->
					<?php wp_editor( '', 'wp-manga-chapter-content-wp-editor', array( 'editor_height' => 350 ) ); ?>
                </div>

				<?php do_action( 'madara_chapter_modal_after_content', $chapter_type, $post->ID ); ?>

            </div>

            <div class="wp-manga-modal-footer">

                <input type="hidden" id="wp-manga-modal-post-id" value="">
                <input type="hidden" id="wp-manga-modal-chapter" value="">
                <div class="duplicate-chapter" style="display:none;">
                    <span> <?php echo esc_html__( 'Duplicate to : ', WP_MANGA_TEXTDOMAIN ) ?> </span>
                    <select name="duplicate-server" id="duplicate-server"> </select>
                    <button id="duplicate-btn" type="button"
                            class="button"> <?php esc_html_e( 'Duplicate', WP_MANGA_TEXTDOMAIN ); ?></button>
                </div>
                <button id="wp-manga-save-paging-button" type="button"
                        class="button button-primary"><?php esc_html_e( 'Save', WP_MANGA_TEXTDOMAIN ); ?></button>
                <button id="wp-manga-delete-chapter-button" type="button"
                        class="button"><?php esc_html_e( 'Delete Chapter', WP_MANGA_TEXTDOMAIN ); ?></button>
                
                <?php if ( !( $chapter_type == 'text' || $chapter_type == 'video' ) ) { ?>
                    <button id="wp-manga-empty-chapter-button" type="button"
                            class="button "><?php esc_html_e( 'Empty Images', WP_MANGA_TEXTDOMAIN ); ?></button>
				<?php } ?>

				<?php if ( !( $chapter_type == 'text' || $chapter_type == 'video' ) ) { ?>
                    <button id="wp-manga-download-chapter-button" type="button"
                            class="button "><?php esc_html_e( 'Download Chapter', WP_MANGA_TEXTDOMAIN ); ?></button>
				<?php } ?>

                <button type="button" id="wp-manga-dismiss-modal" class="button"
                        data-dismiss="modal"><?php esc_html_e( 'Close', WP_MANGA_TEXTDOMAIN ); ?></button>

				<?php do_action( 'madara_chapter_modal_footer', $chapter_type, $post->ID ); ?>

            </div>

        </div>

    </div>

</div>

<div class="wp-manga-disable hidden"></div>
