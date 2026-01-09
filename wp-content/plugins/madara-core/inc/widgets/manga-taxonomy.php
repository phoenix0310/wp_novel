<?php
/**
 * manga taxonomy
 */
class WP_MANGA_TAXONOMY extends WP_Widget {

  function __construct() {
	$widget_ops = array('classname' => 'manga-widget widget-manga-taxonomy', 'description' => esc_html__( 'Display Manga Taxonomy', WP_MANGA_TEXTDOMAIN) );
	parent::__construct('manga-taxonomy', esc_html__('WP Manga: Manga Taxonomy', WP_MANGA_TEXTDOMAIN), $widget_ops);
	$this->alt_option_name = 'widget_manga_taxonomy';

  }

  function widget($args, $instance) {
	if ( ! isset( $args['widget_id'] ) ) {
	  $args['widget_id'] = $this->id;
	}
	
	ob_start();
	extract($args);

	$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : 'Hot Topic';
	$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
	$taxonomy = ( ! empty( $instance['taxonomy'] ) ) ? $instance['taxonomy'] : 'wp-manga-genre';
	$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
	$show_count = isset( $instance['show_count'] ) ? $instance['show_count'] : 'true';
	$columns = isset( $instance['columns'] ) ? $instance['columns'] : 1;
        
	echo $before_widget; ?>
	<div class="c-widget-wrap">
	<?php
	if ( '' != $title ) { ?>
		<div class="widget-heading font-nav">
	        <h5><?php echo $title; ?></h5>
	    </div>
	<?php }	?>
		<div class="released-item-wrap">
			<ul class="list-released <?php echo "style-col-" . $columns;?>">
				<?php foreach ( $terms as $term ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $term, $taxonomy ) ) ?>"><?php echo esc_attr( $term->name ); ?> <?php if($show_count == 'true'){?>(<?php echo $term->count;?>)<?php }?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php echo $after_widget; ?>
	<?php
  }

  function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['taxonomy'] = strip_tags($new_instance['taxonomy']);
	$instance['show_count'] = isset( $new_instance['show_count'] ) ? $new_instance['show_count'] : 'false';
	$instance['columns'] = isset( $new_instance['columns'] ) ? $new_instance['columns'] : 1;

	return $instance;
  }

  function form( $instance ) {
	$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Hot Topic';
	$selected_tax = isset( $instance['taxonomy'] ) ? esc_attr( $instance['taxonomy'] ) : 'wp-manga-genre';
	$show_count = isset( $instance['show_count'] ) ? $instance['show_count'] : 'true';
	$columns = isset( $instance['columns'] ) ? $instance['columns'] : 1;
        
?>
	<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', WP_MANGA_TEXTDOMAIN ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

	<p>
		<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php esc_html_e( 'Taxonomy:', WP_MANGA_TEXTDOMAIN ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
	<?php 
		$taxonomies = $this->get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy ); 
			?>
			<option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $tax->name, $selected_tax, true ) ?>><?php echo esc_attr( $tax->label ); ?></option>
			<?php
		}
		?>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php esc_html_e( 'Number of Columns:', WP_MANGA_TEXTDOMAIN ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>">
			<option value="0" <?php selected( 0, $columns, true ); ?>><?php esc_html_e( 'Display Inline', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="1" <?php selected( 1, $columns, true ); ?>><?php esc_html_e( '1 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="2" <?php selected( 2, $columns, true ); ?>><?php esc_html_e( '2 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="3" <?php selected( 3, $columns, true ); ?>><?php esc_html_e( '3 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="4" <?php selected( 4, $columns, true ); ?>><?php esc_html_e( '4 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="5" <?php selected( 5, $columns, true ); ?>><?php esc_html_e( '5 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
			<option value="6" <?php selected( 6, $columns, true ); ?>><?php esc_html_e( '6 Column', WP_MANGA_TEXTDOMAIN ); ?></option>
		</select>
	</p>
	<p>
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" <?php checked( $show_count, 'true' ); ?> value="true">
		<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
			<?php esc_html_e( 'Show Count', WP_MANGA_TEXTDOMAIN ); ?>
		</label>
	</p>
	<?php
  }

  function get_taxonomies() {
  	$taxonomies = get_object_taxonomies( 'wp-manga' );
  	return $taxonomies;
  }


}
add_action( 'widgets_init', function(){register_widget('WP_MANGA_TAXONOMY');} );