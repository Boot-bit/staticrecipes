<?php
/**
 * NewsPlus Popular Posts Widget
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

class NewsPlus_Popular_Posts extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname'		=> 'newsplus_popular_posts',
			'description'	=> __( 'List popular posts based on most commented.', 'newsplus' )
		);
		parent::__construct( 'newsplus-popular-posts', __( 'NewsPlus Popular Posts', 'newsplus' ), $widget_ops );
		$this->alt_option_name = 'newsplus_popular_entries';
		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_popular_posts', 'widget' );
		$hide_thumb= isset( $instance['hide_thumb'] ) ? $instance['hide_thumb'] : false;
		if ( ! is_array( $cache ) )
			$cache = array();
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}
		ob_start();
		extract( $args );
		$cats = empty( $instance['cats'] ) ? '1' : $instance['cats'];
		$offset = empty( $instance['offset'] ) ? '0' : $instance['offset'];
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Posts', 'newsplus' ) : $instance['title'] );
		if ( ! $number = (int) $instance['number'] )
			$number = 10;
		elseif ( $number < 1 )
			$number = 1;
		elseif ( $number > 15 )
			$number = 15;
		
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}		
		$format = apply_filters( 'widget_popular_posts_output', '[insert_posts display_style="list-small" cats="%s" offset="%s" num="%s" hide_reviews="true" hide_views="true" hide_cats="true" hide_comments="true" orderby="comment_count"]' );
		echo do_shortcode( sprintf( $format, $cats, $offset, $number ) );
		
		echo $after_widget;
		
		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_add( 'widget_popular_posts', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['cats'] = strip_tags( $new_instance['cats'] );
		$instance['offset'] = strip_tags( $new_instance['offset'] );
		$instance['hide_thumb'] = isset( $new_instance['hide_thumb'] ) ? true : false;
		$this->flush_widget_cache();
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['newsplus_popular_entries'] ) )
		delete_option( 'newsplus_popular_entries' );
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_popular_posts', 'widget' );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'hide_thumb' => false, 'cats' => '', 'offset' => '0' ) );
		$title = esc_attr( $instance['title'] );
		$cats = esc_attr( $instance['cats'] );
		$offset = esc_attr( $instance['offset'] );
		if ( ! isset( $instance['number'] ) || ! $number = (int) $instance['number'] )
			$number = 5; ?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'newsplus' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /><br />
		<small><?php _e( '(at most 15)', 'newsplus' ); ?></small>
        </p>
		<p><label for="<?php echo $this->get_field_id( 'offset' ); ?>"><?php _e( 'Posts offset', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'offset' ); ?>" name="<?php echo $this->get_field_name( 'offset' ); ?>" type="text" value="<?php echo $offset; ?>" size="3" /><br />
		<small><?php _e( 'Provide an offset number to which you wish to skip the posts.', 'newsplus' ); ?></small>
        </p>
		<p><label for="<?php echo $this->get_field_id( 'cats' ); ?>"><?php _e( 'Cat IDs to exclude or include:', 'newsplus' ); ?></label>
		<input type="text" value="<?php echo $cats; ?>" name="<?php echo $this->get_field_name( 'cats' ); ?>" id="<?php echo $this->get_field_id( 'cats' ); ?>" class="widefat" />
		<br />
		<small><?php _e( 'Category IDs, separated by commas. Eg: 3,6,7 to include. Or -3,-6,-7 to exclude.', 'newsplus' ); ?></small>
		</p>
        <p><label for="<?php echo $this->get_field_id( 'hide_thumb' ); ?>"><?php _e( 'Hide Thumbnails?', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['hide_thumb'], true ) ?> id="<?php echo $this->get_field_id( 'hide_thumb' ); ?>" name="<?php echo $this->get_field_name( 'hide_thumb' ); ?>" /><br />
        <small><?php _e( 'If unchecked, it will show post thumbnails.', 'newsplus' ); ?></small>
        </p>
	<?php }
} ?>