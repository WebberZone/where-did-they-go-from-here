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
			__( 'WZ Followed Posts', 'where-did-they-go-from-here' ),
			array(
				'description' => __( 'Selective refreshable widget.', 'where-did-they-go-from-here' ),
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
		global $wherego_settings;

		if ( ! is_singular() ) {
			return;
		}

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? strip_tags( $wherego_settings['title'] ) : $instance['title'] );

		$limit = isset( $instance['limit'] ) ? $instance['limit'] : $wherego_settings['limit'];
		if ( empty( $limit ) ) {
			$limit = $wherego_settings['limit'];
		}

		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? $instance['post_thumb_op'] : 'text_only';

		$thumb_height = ( isset( $instance['thumb_height'] ) && '' !== $instance['thumb_height'] ) ? intval( $instance['thumb_height'] ) : $wherego_settings['thumb_height'];
		$thumb_width = ( isset( $instance['thumb_width'] ) && '' !== $instance['thumb_width'] ) ? intval( $instance['thumb_width'] ) : $wherego_settings['thumb_width'];

		// Start building the output now.
		$output = $args['before_widget'];
		$output .= $args['before_title'] . $title . $args['after_title'];

		$arguments = array(
			'is_widget' => 1,
			'instance_id' => $this->number,
			'heading' => 0,
			'limit' => $limit,
			'post_thumb_op' => $post_thumb_op,
			'thumb_height' => $thumb_height,
			'thumb_width' => $thumb_width,
		);

		/**
		 * Filters arguments passed to get_wherego for the widget.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $arguments  Widget options array
		 */
		$arguments = apply_filters( 'wherego_widget_options' , $arguments );

		$output .= get_wherego( $arguments );

		$output .= $args['after_widget'];

		echo $output;   // WPCS: XSS OK.

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$limit = isset( $instance['limit'] ) ? $instance['limit'] : '';
		$post_thumb_op = isset( $instance['post_thumb_op'] ) ? $instance['post_thumb_op'] : 'text_only';
		$thumb_width = isset( $instance['thumb_width'] ) ? $instance['thumb_width'] : '';
		$thumb_height = isset( $instance['thumb_height'] ) ? $instance['thumb_height'] : '';

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
			<?php esc_html_e( 'Thumbnail options', 'where-did-they-go-from-here' ); ?>: <br />
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_thumb_op' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_thumb_op' ) ); ?>">
				<option value="inline" <?php selected( $post_thumb_op, 'inline', 0 ); ?>><?php esc_html_e( 'Thumbnails before title', 'where-did-they-go-from-here' ); ?></option>
				<option value="after" <?php selected( $post_thumb_op, 'after', 0 ); ?>><?php esc_html_e( 'Thumbnails after title', 'where-did-they-go-from-here' ); ?></option>
				<option value="thumbs_only" <?php selected( $post_thumb_op, 'thumbs_only', 0 ); ?>><?php esc_html_e( 'Thumbnails only', 'where-did-they-go-from-here' ); ?></option>
				<option value="text_only" <?php selected( $post_thumb_op, 'text_only', 0 ); ?>><?php esc_html_e( 'Text only', 'where-did-they-go-from-here' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>"><?php esc_html_e( 'Thumb width', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_width ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>"><?php esc_html_e( 'Thumb height', 'where-did-they-go-from-here' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_height' ) ); ?>" type="text" value="<?php echo esc_attr( $thumb_height ); ?>">
		</p>

		<?php

		/**
		 * Fires after Where did they go from here widget options.
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? intval( $new_instance['limit'] ) : '';
		$instance['post_thumb_op'] = $new_instance['post_thumb_op'];
		$instance['thumb_width'] = ( ! empty( $new_instance['thumb_width'] ) ) ? intval( $new_instance['thumb_width'] ) : '';
		$instance['thumb_height'] = ( ! empty( $new_instance['thumb_height'] ) ) ? intval( $new_instance['thumb_height'] ) : '';

		/**
		 * Filters Update widget options array.
		 *
		 * @since 2.0.0
		 *
		 * @param   array   $instance   Widget options array
		 */
		return apply_filters( 'wherego_widget_options_update' , $instance );
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

