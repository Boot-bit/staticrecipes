<?php
/**
 * NewsPlus Minifolio Widget
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */


class NewsPlus_Mini_Folio extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname'		=> 'newsplus_mini_folio',
			'description'	=> __( 'Show a mini portfolio from post featured images.', 'newsplus' )
		);
		parent::__construct( 'newsplus-mini-folio', __( 'NewsPlus Mini Folio', 'newsplus' ), $widget_ops );
		$this->alt_option_name = 'newsplus_mini_folio';
		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_minifolio', 'widget' );
		if ( ! is_array( $cache ) )
			$cache = array();
		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}
		ob_start();
		extract( $args );
		$output = '';
		$cats = empty( $instance['cats'] ) ? null : $instance['cats'];
		$col = empty( $instance['col'] ) ? 2 : $instance['col'];
		$enable_masonry = isset( $instance['enable_masonry'] ) ? $instance['enable_masonry'] : '';
		$lightbox = isset( $instance['lightbox'] ) ? $instance['lightbox'] : '';
		$imgcrop = isset( $instance['imgcrop'] ) ? $instance['imgcrop'] : '';
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Posts', 'newsplus' ) : $instance['title'] );

		if ( ! $number = (int) $instance['number'] )
			$number = 10;
		elseif ( $number < 1 )
			$number = 1;
			
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
		$format = apply_filters( 'widget_minifolio_output', '[insert_posts display_style="gallery" cats="%s" num="%s" col="%s" enable_masonry="%s" lightbox="%s" imgwidth="%s" imgheight="%s" imgcrop="%s" imgquality="%s"]' );
		echo do_shortcode( sprintf( $format, $cats, $number, $col, $enable_masonry, $lightbox, $imgwidth, $imgheight, $imgcrop, $imgquality ) );
		
		echo $after_widget;

		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_add( 'widget_minifolio', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['imgwidth'] = (int) $new_instance['imgwidth'];
		$instance['imgheight'] = (int) $new_instance['imgheight'];
		$instance['imgquality'] = (int) $new_instance['imgquality'];
		$instance['col'] = (int) $new_instance['col'];
		$instance['cats'] = strip_tags( $new_instance['cats'] );
		$instance['enable_masonry'] = isset( $new_instance['enable_masonry'] ) ? true : false;
		$instance['lightbox'] = isset( $new_instance['lightbox'] ) ? true : false;
		$instance['imgcrop'] = isset( $new_instance['imgcrop'] ) ? true : false;
		
		$this->flush_widget_cache();
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['newsplus_mini_folio'] ) )
		delete_option( 'newsplus_mini_folio' );
		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_minifolio', 'widget' );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'cats' => '', 'enable_masonry'	=> false, 'lightbox'	=> false, 'imgcrop'	=> false ) );
		$title = esc_attr( $instance['title'] );
		$cats = esc_attr( $instance['cats'] );
		if ( ! isset( $instance['number'] ) || ! $number = (int) $instance['number'] ) {
			$number = 12;
		}
		
		if ( ! isset( $instance['imgwidth'] ) || ! $imgwidth = (int) $instance['imgwidth'] ) {
			$imgwidth = '';
		}
		
		if ( ! isset( $instance['imgheight'] ) || ! $imgheight = (int) $instance['imgheight'] ) {
			$imgheight = '';
		}
		
		if ( ! isset( $instance['imgquality'] ) || ! $imgquality = (int) $instance['imgquality'] ) {
			$imgquality = '';
		}		
		
		if ( ! isset( $instance['col'] ) || ! $col = (int) $instance['col'] ) {
			$col = 2;
		} ?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'newsplus' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        
		<p><label for="<?php echo $this->get_field_id( 'col' ); ?>"><?php _e( 'Columns:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'col' ); ?>" name="<?php echo $this->get_field_name( 'col' ); ?>" type="number" value="<?php echo $col; ?>" min="1" /><br />
		<small><?php _e( 'Enter number of columns per row', 'newsplus' ); ?></small>
        </p>        
        
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'newsplus' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" value="<?php echo $number; ?>" min="1" /><br />
		<small><?php _e( 'Enter number of posts to show', 'newsplus' ); ?></small>
        </p>
        
		<p><label for="<?php echo $this->get_field_id( 'cats' ); ?>"><?php _e( 'Cat IDs to exclude or include:', 'newsplus' ); ?></label>
		<input type="text" value="<?php echo $cats; ?>" name="<?php echo $this->get_field_name('cats'); ?>" id="<?php echo $this->get_field_id( 'cats' ); ?>" class="widefat" />
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
        
		<p><label for="<?php echo $this->get_field_id( 'enable_masonry' ); ?>"><?php _e( 'Enable Masonry', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['enable_masonry'], true ) ?> id="<?php echo $this->get_field_id( 'enable_masonry' ); ?>" name="<?php echo $this->get_field_name( 'enable_masonry' ); ?>" /><br />
		<small><?php _e( 'Check to enable masonry layout', 'newsplus' ); ?></small></p>
		</p>
        
		<p><label for="<?php echo $this->get_field_id( 'lightbox' ); ?>"><?php _e( 'Enable Lightbox', 'newsplus' ); ?></label>
        <input class="checkbox" type="checkbox" <?php checked( $instance['lightbox'], true ) ?> id="<?php echo $this->get_field_id( 'lightbox' ); ?>" name="<?php echo $this->get_field_name( 'lightbox' ); ?>" /><br />
		<small><?php _e( 'Check to enable lightbox on images', 'newsplus' ); ?></small></p>
		</p>        
        
	<?php }
} ?>