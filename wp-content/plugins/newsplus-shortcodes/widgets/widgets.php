<?php
/**
 * init widgets
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

require_once( 'widget-categories.php' );
require_once( 'widget-recent-posts.php' );
require_once( 'widget-popular-posts.php' );
require_once( 'widget-minifolio.php' );
require_once( 'widget-flickr.php' );
require_once( 'widget-social-links.php' );

function newsplus_widgets_init() {
	register_widget( 'NewsPlus_Cat_Widget' );
	register_widget( 'NewsPlus_Recent_Posts' );
	register_widget( 'NewsPlus_Popular_Posts' );
	register_widget( 'NewsPlus_Mini_Folio' );
	register_widget( 'NewsPlus_Flickr_Widget' );
	register_widget( 'NewsPlus_Social_Widget' );
}

add_action( 'widgets_init', 'newsplus_widgets_init' );