<?php

	/**
	 * The Template for Reset password popup
	 *
	 * This template can be overridden by copying it to your-child-theme/madara-core/reset-password.php
	 *
	 * HOWEVER, on occasion Madara will need to update template files and you
	 * (the theme developer) will need to copy the new files to your theme to
	 * maintain compatibility. We try to do this as little as possible, but it does
	 * happen. When this occurs the version of the template file will be bumped and
	 * we will list any important changes on our theme release logs.
	 * @package Madara
	 * @version 1.7.2.2
	 */
	 
	 ?><div class="modal fade" id="form-reset-password" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="reset-password" class="login">
                    <h1>
                        <h3><?php echo esc_html__( 'Reset Password', WP_MANGA_TEXTDOMAIN ); ?></h3>
                    </h1>
                    <p class="message reset-password"><?php echo esc_html__( 'Enter your new password below.', WP_MANGA_TEXTDOMAIN ); ?></p>
                    <form name="resetpasswordform" id="resetpasswordform" method="post">
                        <p>
                            <input type="password" name="pass_1" class="input" value="" placeholder="<?php echo esc_html_e( 'New Password', WP_MANGA_TEXTDOMAIN ); ?>">
                        </p>
                        <p>
                            <input type="password" name="pass_2" class="input" value="" placeholder="<?php echo esc_html_e( 'Confirm New Password', WP_MANGA_TEXTDOMAIN ); ?>">
                        </p>
                        <p class="description indicator-hint">
                            <?php echo wp_get_password_hint(); ?>
                        </p>
                        <p class="submit">
                            <input type="hidden" name="user" value="<?php echo isset( $_GET['login'] ) ? esc_attr( $_GET['login'] ) : ''; ?>">
                            <input type="hidden" name="key" value="<?php echo isset( $_GET['key'] ) ? esc_attr( $_GET['key'] ) : ''; ?>">

                            <input type="submit" name="wp-submit" class="button button-primary button-large wp-submit" value="<?php esc_html_e( 'Set New Password', WP_MANGA_TEXTDOMAIN ); ?>">
                        </p>
                    </form>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function($){
        $('#form-reset-password').modal('show');
    });
</script>
