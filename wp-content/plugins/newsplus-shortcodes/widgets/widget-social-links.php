<?php
/**
 * NewsPlus Social Links Widget
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

class NewsPlus_Social_Widget extends WP_Widget {

	public $social_links;

	public function __construct() {
		$widget_ops = array( 'classname' => 'newsplus_social', 'description' => esc_html__( "Social networking icons widget." ) );
		parent::__construct( 'newsplus-social', esc_html__( 'NewsPlus Social Icons' ), $widget_ops );
		$this->social_links = apply_filters( 'newsplus_social_links_list', array( 'twitter', 'facebook', 'linkedin', 'google-plus', 'instagram', 'whatsapp', 'lastfm', 'pinterest', 'vimeo', 'yahoo', 'delicious', 'deviantart', 'dribbble', 'flickr', 'foursquare', 'github', 'renren', 'reddit', 'rss', 'skype', 'soundcloud', 'spotify', 'stumbleupon', 'behance', 'trello', 'tumblr', 'twitch', 'vine', 'vk', 'weibo', 'xing', 'youtube', 'paypal', 'get-pocket', 'email' ) );
	}

	public function widget( $args, $instance ) {

		$out = '<ul class="ss-social">';
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_attr( $title ) . $args['after_title'];
		}
		$target_check = isset( $instance['target_blank'] ) ? $instance['target_blank']  : false;

		foreach ( $this->social_links as $social_link ) {
			$link_check = isset( $instance[ $social_link . '-check' ] ) ? $instance[ $social_link . '-check' ] : false;
			$link_url = isset( $instance[ $social_link . '-url' ] ) ? $instance[ $social_link . '-url' ] : '';

			if ( $link_check ) {
				$out .= sprintf( '<li><a href="%1$s" title="%2$s" class="ss-%3$s"%5$s><i class="fa fa-%3$s"></i><span class="sr-only">%4$s</span></a></li>',
					esc_url( $link_url ),
					esc_attr( ucwords( str_replace( '-', ' ', $social_link ) ) ),
					( $social_link == 'email' ) ? 'envelope' : str_replace( ' ', '-', $social_link ),
					esc_attr( str_replace( '-', ' ', $social_link ) ),
					$target_check ? ' target="_blank"' : ''
				);
			}
		}

		echo $out . '</ul>';
		echo $args['after_widget'];

	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$defaults = array();
		foreach ( $this->social_links as $social_link ) {
			$defaults[ $social_link . '-check' ] = false;
			$defaults[ $social_link . '-url' ] = '';
		}
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => '', implode( ',' , $defaults ) ) );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['target_blank'] = isset( $new_instance['target_blank'] ) ? true : false;		

		foreach ( $this->social_links as $social_link ) {
			$instance[ $social_link . '-check' ] = isset( $new_instance[ $social_link . '-check' ] ) ? true : false;
			$instance[$social_link . '-url'] = strip_tags( $new_instance[ $social_link . '-url' ] );
		}
		return $instance;
	}

	public function form( $instance ) {
		$defaults = array();
		foreach ( $this->social_links as $social_link ) {
			$defaults[ $social_link . '-check' ] = false;
			$defaults[ $social_link . '-url' ] = '';
		}
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', implode(',' , $defaults) ) );

		$title = $instance['title'];
		$target_check = isset( $instance['target_blank'] ) ? $instance['target_blank'] : false;
		?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>

		<p><label for="<?php echo $this->get_field_id( 'target_blank' ); ?>"></label>
            <input class="checkbox" type="checkbox" <?php checked( $target_check, true ) ?> id="<?php echo $this->get_field_id( 'target_blank' ); ?>" name="<?php echo $this->get_field_name( 'target_blank' ); ?>" />
            <small><?php printf( esc_html__( 'Check to open links in new tab or window.', 'newsplus' ), $social_link ); ?>
            </small>
        </p>
        <?php
        foreach ( $this->social_links as $social_link ) {

		$link_url = isset( $instance[$social_link . '-url'] ) ? $instance[$social_link . '-url'] : '';
		$link_check = isset( $instance[$social_link . '-check'] ) ? $instance[$social_link . '-check'] : false;
		?>
        <p>
            <label for="<?php echo $this->get_field_id( $social_link ); ?>"><?php echo ucwords($social_link); ?></label>
            <input class="checkbox" type="checkbox" <?php checked( $link_check, true ) ?> id="<?php echo $this->get_field_id( $social_link . '-check' ); ?>" name="<?php echo $this->get_field_name( $social_link . '-check' ); ?>" /><br />
            <input type="text" value="<?php echo $link_url; ?>" name="<?php echo $this->get_field_name( $social_link . '-url' ); ?>" id="<?php echo $this->get_field_id( $social_link . '-url' ); ?>" class="widefat" />
            <br />
            <small><?php printf( esc_html__( 'Full URL to %s', 'newsplus' ), $social_link ); ?>
            </small>
        </p>
        <?php
		}
	}
}