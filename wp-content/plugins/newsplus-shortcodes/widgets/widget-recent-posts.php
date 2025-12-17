<?php
/**
 * NewsPlus Recent Posts Widget
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

class NewsPlus_Recent_Posts extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname'		=> 'newsplus_recent_posts',
			'description'	=> __( 'List recent posts with thumbnails from custom categories.', 'newsplus' )
		);
		parent::__construct( 'newsplus-recent-posts', __( 'NewsPlus Recent Posts', 'newsplus' ), $widget_ops );
		$this->alt_option_name = 'newsplus_recent_entries';
		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_recent_posts', 'widget' );
		if ( ! is_array( $cache ) )
			$cache = array();
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}
		ob_start();
		extract( $args );
		
		$hide_thumb= isset( $instance['hide_thumb'] ) ? $instance['hide_thumb'] : false;
		$imgcrop = isset( $instance['imgcrop'] ) ? $instance['imgcrop'] : '';
		$enable_schema = isset( $instance['enable_schema'] ) ? $instance['enable_schema'] : '';			
		$cats = empty( $instance['cats'] ) ? '1' : $instance['cats'];
		$offset = empty( $instance['offset'] ) ? '0' : $instance['offset'];
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Posts', 'newsplus' ) : $instance['title'] );
		if ( ! $number = (int) $instance['number'] )
			$number = 10;
		elseif ( $number < 1 )
			$number = 1;
		elseif ( $number > 15 )
			$number = 15;
			
		if ( ! $imgwidth = (int) $instance['imgwidth'] )
			$imgwidth = '';
		elseif ( $imgwidth < 10 )
			$imgwidth = 10;	
			
		if ( ! $imgheight = (int) $instance['imgheight'] )
			$imgheight = '';
		elseif ( $imgheight < 10 )
			$imgheight = 10;
			
		if ( ! $imgquality = (int) $instance['imgquality'] )
			$imgquality = '';
		elseif ( $imgquality < 1 )
			$imgquality = 1;
		elseif ( $imgquality > 100 )
			$imgquality = 100;				
		
		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$format = apply_filters( 'widget_recent_posts_output', '[insert_posts display_style="list-small" cats="%s" offset="%s" num="%s" imgwidth="%s" imgheight="%s" imgcrop="%s" enable_schema="%s" hide_image="%s" hide_reviews="true" hide_views="true" hide_cats="true" hide_comments="true"]' );
		echo do_shortcode( sprintf( $format, $cats, $offset, $number, $imgwidth, $imgheight,
				$imgcrop ? 'true' : '',
				$enable_schema ? 'true' : '',
				$hide_thumb ? 'true' : ''
			)
		 );
		
		echo $after_widget;
		
		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_add( 'widget_recent_posts', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['cats'] = strip_tags( $new_instance['cats'] );
		$instance['offset'] = strip_tags( $new_instance['offset'] );
		$instance['imgwidth'] = (int) $new_instance['imgwidth'];
		$instance['imgheight'] = (int) $new_instance['imgheight'];
		$instance['imgquality'] = (int) $new_instance['imgquality'];		
		$instance['hide_thumb'] = isset( $new_instance['hide_thumb'] ) ? true : false;
		$instance['imgcrop'] = isset( $new_instance['imgcrop'] ) ? true : false;
		$instance['enable_schema'] = isset( $new_instance['enable_schema'] ) ? true : false;
		$this->flush_widget_cache();
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['newsplus_recent_entries'] ) )
		delete_option( 'newsplus_recent_entries' );
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_posts', 'widget' );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'hide_thumb' => false, 'cats' => '', 'offset' => '0', 'imgcrop'	=> false, 'enable_schema'	=> false ) );
		$title = esc_attr( $instance['title'] );
		$cats = esc_attr( $instance['cats'] );
		$offset = esc_attr( $instance['offset'] );
		
		if ( ! isset( $instance['imgwidth'] ) || ! $imgwidth = (int) $instance['imgwidth'] ) {
			$imgwidth = '';
		}
		
		if ( ! isset( $instance['imgheight'] ) || ! $imgheight = (int) $instance['imgheight'] ) {
			$imgheight = '';
		}
		
		if ( ! isset( $instance['imgquality'] ) || ! $imgquality = (int) $instance['imgquality'] ) {
			$imgquality = '';
		}		
				
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
		<p><label for="<?php echo $this->get_field_id( 'imgwidth' ); ?>"><?php _e( 'Image width:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'imgwidth' ); ?>" name="<?php echo $this->get_field_name( 'imgwidth' ); ?>" type="number" value="<?php echo $imgwidth; ?>" min="10" /><br />
		<small><?php _e( 'Enter a width for image in px (without unit)', 'newsplus' ); ?></small>
        </p>
        
		<p><label for="<?php echo $this->get_field_id( 'imgheight' ); ?>"><?php _e( 'Image height:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'imgheight' ); ?>" name="<?php echo $this->get_field_name( 'imgheight' ); ?>" type="number" value="<?php echo $imgheight; ?>" min="10" /><br />
		<small><?php _e( 'Enter a height for image in px (without unit)', 'newsplus' ); ?></small>
        </p>
        
		<p><label for="<?php echo $this->get_field_id( 'imgquality' ); ?>"><?php _e( 'Image quality:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'imgquality' ); ?>" name="<?php echo $this->get_field_name( 'imgquality' ); ?>" type="number" value="<?php echo $imgquality; ?>" min="1" max="100" /><br />
		<small><?php _e( 'Enter a quality number between 1 to 100. E.g. 80', 'newsplus' ); ?></small>
        </p>         
        
		<p><label for="<?php echo $this->get_field_id( 'imgcrop' ); ?>"><?php _e( 'Hard crop images:', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['imgcrop'], true ) ?> id="<?php echo $this->get_field_id( 'imgcrop' ); ?>" name="<?php echo $this->get_field_name( 'imgcrop' ); ?>" /><br />
		<small><?php _e( 'Check to enable hard cropping of images', 'newsplus' ); ?></small></p>
		</p>                          
        
		<p><label for="<?php echo $this->get_field_id( 'enable_schema' ); ?>"><?php _e( 'Enable Schema', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['enable_schema'], true ) ?> id="<?php echo $this->get_field_id( 'enable_schema' ); ?>" name="<?php echo $this->get_field_name( 'enable_schema' ); ?>" /><br />
		<small><?php _e( 'Check to enable schema markup', 'newsplus' ); ?></small></p>        
        <p><label for="<?php echo $this->get_field_id( 'hide_thumb' ); ?>"><?php _e( 'Hide Thumbnails?', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['hide_thumb'], true ) ?> id="<?php echo $this->get_field_id( 'hide_thumb' ); ?>" name="<?php echo $this->get_field_name( 'hide_thumb' ); ?>" /><br />
        <small><?php _e( 'Check to hide post thumbnails', 'newsplus' ); ?></small>
        </p>
	<?php }
} ?>