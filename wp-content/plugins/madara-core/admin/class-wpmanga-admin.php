<?php
/**
 * WP Manga Admin
 *
 * Main functions for WP Manga Admin
 *
 * @class WP_Manga_Admin
 * @since 2.0
 * @package WP_Manga
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Tracker Class
 */
class WP_Manga_Admin {
	public static function init(){
		add_action('wp_manga_system_status', array('WP_Manga_Admin', 'system_status_template_overrides'));
	}
	
	/**
	 * Show list of template overrides in the System Status page
	 **/
	public static function system_status_template_overrides(){
		global $wp_manga_template;
		?>
		<h3 class="screen-reader-text"><?php esc_html_e( 'Template overrides', WP_MANGA_TEXTDOMAIN ); ?></h3>
		<table class="widefat" cellspacing="0" id="template-overrides">
			<thead>
			<tr>
				<th colspan="3" data-export-label="<?php echo esc_attr__('Template overrides', WP_MANGA_TEXTDOMAIN); ?>"><?php esc_html_e( 'Template overrides',WP_MANGA_TEXTDOMAIN ); ?>
				</th>
			</tr>
			</thead>
			<thead>
			<tr>
				<th><?php esc_html_e( 'Template file', WP_MANGA_TEXTDOMAIN ); ?>
				</th>
				<th>Child Theme</th>
				<th><?php esc_html_e( 'Template version', WP_MANGA_TEXTDOMAIN ); ?>
				</th>
				<th><?php esc_html_e( 'Core version', WP_MANGA_TEXTDOMAIN ); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
				$templates = $wp_manga_template->get_all_template_overrides();
				
				foreach ( $templates as $template ) {
					?>
						<tr>
							<td><?php echo wp_kses_post( $template['file'] ); ?></td>
							<td class="current-child-version">
								<?php if($template['child_version']){
									if ( version_compare( $template['child_version'], $template['version'], '<' ) ) {
											echo ' <span class="outdated">' . sprintf(esc_html__('Outdated: %s', WP_MANGA_TEXTDOMAIN), $template['child_version']) . '</span>';
										} else {
											echo ' <span class="updated">'.$template['child_version'].'</span>';
										}
									}?>
							</td>
							<td class="current-parent-version">
								<?php if($template['version']){
									echo wp_kses_post( $template['version']);
									}?>
							</td>
							<td><?php 
							
							if ( $template['core_version'] && ( empty( $template['version'] ) || version_compare( $template['version'], $template['core_version'], '<' ) ) ) {
								echo ' <span class="outdated">' . sprintf(esc_html__('Outdated: %s', WP_MANGA_TEXTDOMAIN), $template['core_version']) . '</span>';
							} else {
								echo ' <span class="updated">'.$template['core_version'].'</span>';
							}
							
							?>
							</td>
						</tr>
						<?php
				}
			?>
			</tbody>
		</table>
		<?php
		
	}
}