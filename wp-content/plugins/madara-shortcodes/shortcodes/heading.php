<?php

	/**
	 * MadaraShortcodeHeading
	 */
	class MadaraShortcodeHeading extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_heading', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content = '' ) {
			$id     = isset( $atts['id'] ) ? $atts['id'] : 'c-heading-' . rand( 0, 999 );
			$icon   = isset( $atts['icon'] ) ? $atts['icon'] : '';
			$style  = isset( $atts['style'] ) ? $atts['style'] : 1;
			$border = isset( $atts['border'] ) ? $atts['border'] : 1;
            $heading = isset( $atts['heading'] ) ? $atts['heading'] : 'h4';

			$heading_class = 'c-blog__heading font-heading';

			if ( $style == 1 ) {
				$heading_class = 'widget-heading font-nav';
			}
			if ( $border == 0 ) {
				$heading_class .= ' no-border ';
			}

			if ( $icon == '' ) {
				$heading_class .= ' no-icon ';
			}

			ob_start();
			if ( $content != '' ):
				?>
                <div <?php echo( $id != '' ? ( 'id="' . $id . '"' ) : '' ); ?> class="c-shortcode-heading <?php echo $icon != '' ? 'style-2' : 'style-1'; ?> <?php echo esc_attr( $heading_class ) ?>">

                    <<?php echo $heading;?>>
						<?php if ( $icon != '' ) { ?>
                            <i class="<?php echo esc_attr( $icon ); ?>"></i>
						<?php } ?>
						<?php echo esc_html( $content ); ?>
                    </<?php echo $heading;?>>

                </div>
				<?php
			endif;
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

		/**
		 * @param array $attrs
		 *
		 * @return string
		 */
		public function inlineCSSGenerator( $attrs = array() ) {
			$css                   = '';
			$icon_color            = '';
			$heading_bg            = '';
			$heading_bg_after      = '';
			$margin                = '';
			$heading_bg_border_top = '';


			if ( count( $attrs ) == 0 ) {
				$attrs = $this->attributes;
			}

			if ( is_array( $attrs ) ) {

				foreach ( $attrs as $att => $val ) {
					switch ( $att ) {
						case 'icon_color':
							if ( $attrs['icon_color'] != '' ) {
								$icon_color .= 'color: ' . $val . ' ;';
							}
							break;
						case 'heading_bg':
							if ( $attrs['heading_bg'] != '' ) {
								$heading_bg            .= 'background-color: ' . $val . ' ;';
								$heading_bg            .= App\Helpers\Color::madara_background_gradient( $val, '40%' );
								$heading_bg_after      = App\Helpers\Color::madara_adjust_Brightness( $val, '20' );
								$heading_bg_after      = 'border-left-color: ' . $heading_bg_after . ' ;';
								$heading_bg_border_top = 'border-top-color: ' . $val . ' ;';
							}
							break;
						case 'margin':
							if ( $attrs['margin'] != '' ) {
								$margin .= 'margin: ' . $val . ' ;';
							}
							break;
						case 'id':
							$this->id = $val;
							break;
						default:
							break;
					}
				}

				if ( $this->id == '' ) {
					$this->generate_id();
				}

				if ( $icon_color != '' ) {
					$css .= '#' . $this->id . '.c-shortcode-heading i{' . $icon_color . '}';
				}
				if ( $heading_bg != '' ) {
					$css .= '#' . $this->id . '.c-shortcode-heading:not(.c-blog__heading),'. '#' . $this->id . '.c-shortcode-heading.c-blog__heading i {' . $heading_bg . '}';
					$css .= '#' . $this->id . '.c-shortcode-heading.widget-heading:not(.c-blog__heading):after{' . $heading_bg_border_top . '}';
					$css .= '#' . $this->id . '.c-shortcode-heading.c-blog__heading i:after{' . $heading_bg_after . '}';
				}
				if ( $margin != '' ) {
					$css .= '#' . $this->id . '.c-shortcode-heading{ ' . $margin . '}';
				}

				return $css;

			}
		}
	}

	$madara_button = new MadaraShortcodeHeading();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_heading' );

	function reg_manga_heading() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(
				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Style", 'madara-shortcode' ),
					"param_name"  => "style",
					"std"         => 1,
					"value"       => array(
						esc_html__( "Style 1", 'madara-shortcode' ) => 1,
						esc_html__( "Style 2", 'madara-shortcode' ) => 2,
					),
					"description" => esc_html__( "Select heading style", 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Heading Content", 'madara-shortcode' ),
					"param_name"  => "content",
					"value"       => "",
				),
				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Heading Icon", 'madara-shortcode' ),
					"param_name"  => "icon",
					"value"       => "",
					"description" => esc_html__( "Icon class, for example 'fa fa-star'", 'madara-shortcode' ) . '</br><a href="http://fontawesome.io/icons/" target="_blank">' . esc_html__( "Font Awesome", 'madara-shortcode' ) . '</a>, <a href="http://ionicons.com/" target="_blank">' . esc_html__( "Ionicons", 'madara-shortcode' ) . '</a>',
				),

				array(
					"type"        => "colorpicker",
					"heading"     => esc_html__( "Icon Color", 'madara-shortcode' ),
					"param_name"  => "icon_color",
					"value"       => "",
					"description" => esc_html__( "Hexa color of Icon Color", 'madara-shortcode' ),
				),
				array(
					"type"        => "colorpicker",
					"heading"     => esc_html__( "Color of Heading Background", 'madara-shortcode' ),
					"param_name"  => "heading_bg",
					"value"       => "",
					"description" => esc_html__( "Hexa color of Heading Background", 'madara-shortcode' ),
				),
				array(
					"type"        => "textfield",
					"heading"     => esc_html__( "Margin", 'madara-shortcode' ),
					"param_name"  => "margin",
					"value"       => '',
					"description" => esc_html__( "Margin of Button. Default is '0px 0px 0px 0px' (TOP RIGHT BOTTOM LEFT). Leave blank to use default style.", 'madara-shortcode' ),
				),
				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Border Bottom", 'madara-shortcode' ),
					"param_name"  => "border",
					"value"       => array(
						esc_html__( 'Disable', 'madara-shortcode' ) => 0,
						esc_html__( 'Enable', 'madara-shortcode' )  => 1,
					),
					"description" => esc_html__( "Enable/ Disable border bottom in Header Style 1", 'madara-shortcode' ),
					"std"         => 1,
					'dependency'  => array(
						'element' => 'style',
						'value'   => array( '2' ),
					),
				),

			);
			vc_map( array(
				'name'     => esc_html__( 'Madara Heading', 'madara-shortcode' ),
				'base'     => 'manga_heading',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_heading.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
		
	function wp_manga_gutenberg_heading_block() {
		wp_register_script(
			'wp_manga_gutenberg_heading_block',
			plugins_url( 'gutenberg/heading.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element' )
		);

		if(function_exists('register_block_type')){
		register_block_type( 'wp-manga/gutenberg-heading-block', array(
			'editor_script' => 'wp_manga_gutenberg_heading_block',
		) );
		}
	}
	add_action( 'init', 'wp_manga_gutenberg_heading_block' );