<?php
/**
 * Post Format - Video
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

global $post;
$post_opts = get_post_meta( $post->ID, 'post_options', true );
$pf_video = '';

if ( ! empty( $post_opts[ 'pf_video' ] ) ) {
	$pf_video = $post_opts[ 'pf_video' ];
}

else {
	$cust_video = get_post_meta( $post->ID, $video_custom_field, true );
	if ( isset( $cust_video ) ) {
		$pf_video = $cust_video;
	}
}

$schema_width = '' == $imgwidth ? '600' : $imgwidth;
$schema_height = '' == $imgheight ? '400' : $imgheight;
$schema =  newsplus_schema( $enable_schema, $schema_width, $schema_height );

$img = array( 'url' => '', 'title' => '' );

if ( $pf_video && 'true' != $hide_video ) {
	global $wp_embed;
	if ( $template == 'list-small' || $template == 'list-big' ) {
		echo '<div class="post-img">';
	}
	echo '<div' . $schema['img_cont'] . ' class="embed-wrap">';
	$post_embed = $wp_embed->run_shortcode( '[embed]' . $pf_video . '[/embed]' );
	echo $post_embed;
	if ( $enable_schema ) {
		echo '<meta itemprop="url" content="' . esc_url( $img ) . '" />' . $schema['img_size'];
	}
	echo '</div>';
	if ( $template == 'list-small' || $template == 'list-big' ) {
		echo '</div>';
	}
}

else {
	if ( 'true' == $hide_video && 'true' != $hide_image ) {
		if ( has_post_thumbnail() ) {
			$thumb_path = apply_filters( 'newsplus_thumb_path',  '/format.php' );
			if ( locate_template( $thumb_path ) ) {
				require( get_stylesheet_directory() . $thumb_path );
			}
			else {
				require( dirname( __FILE__ ) . $thumb_path );
			}
		}

		else {
			if ( function_exists( 'newsplus_video_thumb' ) && $pf_video != '' ) {
				$img = newsplus_video_thumb( $pf_video );
				if ( $template == 'list-small' || $template == 'list-big' ) {
					echo '<div class="post-img">';
				}
				$video_icon = '<div class="video-overlay"></div>';
				$link = ( $ext_link && $custom_link ) ? esc_url( $custom_link) : esc_url( get_permalink() );
				echo '<div' . $schema['img_cont'] . ' class="post-thumb"><a href="' . esc_url( $link ) . '" title="' . get_the_title() . '"><img' . $schema['img'] . ' src="' . $img['url'] . '" class="attachment-post-thumbnail wp-post-image" alt="' . $img['title'] . '">' . $video_icon .'</a>';
				echo $schema['img_size'];
				echo '</div>';
				if ( $template == 'list-small' || $template == 'list-big' ) {
					echo '</div>';
				}
			}
		}
	}
}