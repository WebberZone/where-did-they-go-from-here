<?php
/**
 * Shortcode
 *
 * @package   WHEREGO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Better Search Widget.
 *
 * @extends WP_Widget
 */
class WhereGo_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wherego_widget',
			__( 'WebberZone Followed Posts', 'where-did-they-go-from-here' ),
			array(
				'description'                 => __( 'Display followed posts', 'where-did-they-go-from-here' ),
				'customize_selective_refresh' => true,
			)
		);
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! is_singular() ) {
			return;
		}

		$title = empty( $instance['title'] ) ? wp_strip_all_tags( str_replace( '%postname%', $post->post_title, wherego_get_option( 'title' ) ) ) : $instance['title'];
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$limit = isset( $instance['limit'] ) ? $instance['limit'] : wherego_get_option( 'limit' );
		if ( empty( $limit ) ) {
			$limit = wherego_get_option( 'limit' );
		}

		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? esc_attr( $instance['post_thumb_op'] ) : 'text_only';
		$thumb_height  = isset( $instance['thumb_height'] ) && ! empty( $instance['thumb_height'] ) ? esc_attr( $instance['thumb_height'] ) : wherego_get_option( 'thumb_height' );
		$thumb_width   = isset( $instance['thumb_width'] ) && ! empty( $instance['thumb_width'] ) ? esc_attr( $instance['thumb_width'] ) : wherego_get_option( 'thumb_width' );
		$show_excerpt  = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author   = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date     = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
		$post_types    = isset( $instance['post_types'] ) && ! empty( $instance['post_types'] ) ? $instance['post_types'] : wherego_get_option( 'post_types' );

		$arguments = array(
			'is_widget'     => 1,
			'instance_id'   => $this->number,
			'heading'       => 0,
			'limit'         => $limit,
			'post_thumb_op' => $post_thumb_op,
			'thumb_height'  => $thumb_height,
			'thumb_width'   => $thumb_width,
			'show_excerpt'  => $show_excerpt,
			'show_author'   => $show_author,
			'show_date'     => $show_date,
			'post_types'    => $post_types,
		);

		/**
		 * Filters arguments passed to get_wherego for the widget.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $arguments  Widget options array
		 */
		$arguments = apply_filters( 'wherego_widget_options', $arguments );

		// Start building the output now.
		$output  = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$output .= get_wherego( $arguments );

		$output .= $args['after_widget'];

		echo $output;   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title         = isset( $instance['title'] ) ? $instance['title'] : '';
		$limit         = isset( $instance['limit'] ) ? $instance['limit'] : '';
		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? $instance['post_thumb_op'] : 'text_only';
		$thumb_width   = isset( $instance['thumb_width'] ) ? $instance['thumb_width'] : '';
		$thumb_height  = isset( $instance['thumb_height'] ) ? $instance['thumb_height'] : '';
		$show_excerpt  = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
		$show_author   = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
		$show_date     = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';

		// Parse the Post types.
		$post_types = array();
		if ( isset( $instance['post_types'] ) ) {
			$post_types = $instance['post_types'];

			// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
			if ( false === strpos( $post_types, '=' ) ) {
				$post_types = explode( ',', $post_types );
			} else {
				parse_str( $post_types, $post_types );
			}
		}
		$wp_post_types   = get_post_types(
			array(
				'public' => true,
			)
		);
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of posts', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" type="checkbox" <?php checked( true, $show_excerpt, true ); ?> /> <?php esc_html_e( 'Show excerpt?', 'where-did-they-go-from-here' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_author' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_author' ) ); ?>" type="checkbox" <?php checked( true, $show_author, true ); ?> /> <?php esc_html_e( 'Show author?', 'where-did-they-go-from-here' ); ?>
			</label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>">
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" type="checkbox" <?php checked( true, $show_date, true ); ?> /> <?php esc_html_e( 'Show date?', 'where-did-they-go-from-here' ); ?>
			</label>
		</p>

		<p>
			<?php esc_html_e( 'Thumbnail options', 'where-did-they-go-from-here' ); ?>: <br />
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_thumb_op' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_thumb_op' ) ); ?>">
				<option value="inline" <?php selected( $post_thumb_op, 'inline', 0 ); ?>><?php esc_html_e( 'Thumbnails before title', 'where-did-they-go-from-here' ); ?></option>
				<option value="after" <?php selected( $post_thumb_op, 'after', 0 ); ?>><?php esc_html_e( 'Thumbnails after title', 'where-did-they-go-from-here' ); ?></option>
				<option value="thumbs_only" <?php selected( $post_thumb_op, 'thumbs_only', 0 ); ?>><?php esc_html_e( 'Thumbnails only', 'where-did-they-go-from-here' ); ?></option>
				<option value="text_only" <?php selected( $post_thumb_op, 'text_only', 0 ); ?>><?php esc_html_e( 'Text only', 'where-did-they-go-from-here' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>"><?php esc_html_e( 'Thumbnail width', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_width ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>"><?php esc_html_e( 'Thumbnail height', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_height' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_height ); ?>">
		</p>

		<p><?php esc_html_e( 'Post types to include', 'where-did-they-go-from-here' ); ?><br />

			<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

				<label>
					<input id="<?php echo esc_attr( $this->get_field_id( 'post_types' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_types' ) ); ?>[]" type="checkbox" value="<?php echo esc_attr( $wp_post_type ); ?>" <?php checked( true, in_array( $wp_post_type, $posts_types_inc, true ), true ); ?> />
					<?php echo esc_attr( $wp_post_type ); ?>
				</label>
				<br />

			<?php } ?>
		</p>

		<?php

		/**
		 * Fires after WebberZone Followed Posts widget options.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		do_action( 'wherego_widget_options_after', $instance );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                  = $old_instance;
		$instance                  = array();
		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['limit']         = ( ! empty( $new_instance['limit'] ) ) ? intval( $new_instance['limit'] ) : '';
		$instance['post_thumb_op'] = $new_instance['post_thumb_op'];
		$instance['thumb_width']   = ( ! empty( $new_instance['thumb_width'] ) ) ? intval( $new_instance['thumb_width'] ) : '';
		$instance['thumb_height']  = ( ! empty( $new_instance['thumb_height'] ) ) ? intval( $new_instance['thumb_height'] ) : '';
		$instance['show_excerpt']  = isset( $new_instance['show_excerpt'] ) ? true : false;
		$instance['show_author']   = isset( $new_instance['show_author'] ) ? true : false;
		$instance['show_date']     = isset( $new_instance['show_date'] ) ? true : false;

		// Process post types to be selected.
		$wp_post_types          = get_post_types(
			array(
				'public' => true,
			)
		);
		$post_types             = ( isset( $new_instance['post_types'] ) ) ? $new_instance['post_types'] : array();
		$post_types             = array_intersect( $wp_post_types, $post_types );
		$instance['post_types'] = implode( ',', $post_types );

		/**
		 * Filters Update widget options array.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'wherego_widget_options_update', $instance );
	}
}


/**
 * Function to register the widget.
 *
 * @since 2.0.0
 *
 * @return void
 */
function wherego_register_widget() {

	register_widget( 'WhereGo_Widget' );

}
add_action( 'widgets_init', 'wherego_register_widget' );
