<?php
/** Post Format - Standard
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

if ( has_post_thumbnail() ) {
	$schema_width = ( '' == $imgwidth  ) ? '600' : $imgwidth;
	$schema_height = ( '' == $imgheight  ) ? '400' : $imgheight;
	$schema =  NewsPlus_Shortcodes::newsplus_schema( $enable_schema, $schema_width, $schema_height );

	if ( $template == 'list-small' || $template == 'list-big' ) {
		printf( '<div class="post-img%s">',
			'true' == $hide_image ? ' schema-only' : ''
		);
	}

	printf( '<div%s class="post-thumb%s">',
			$schema['img_cont'],
			$ptclass ? ' ' . esc_attr( $ptclass ) : ''
		);
		
		$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		$img = $img_src[0];
		$img = NewsPlus_Shortcodes::newsplus_image_resize( $img, $imgwidth, $imgheight, $imgcrop, $imgquality, '', '' );

	 	$alt_text = basename( get_attached_file( get_post_thumbnail_id( get_the_ID() ) ) );

		$video_icon = '';

		if ( 'video' == get_post_format() || 'gallery' == get_post_format() ) {
			$video_icon = '<div class="' . get_post_format() . '-overlay"></div>';
		}

		$link = ( $ext_link && $custom_link ) ? esc_url( $custom_link) : esc_url( get_permalink() );
		
		if ( 'true' !== $hide_image ) {
			echo '<a href="' . esc_url( $link ) . '" title="' . get_the_title() . '"><img' . $schema['img'] . ' src="' . $img . '" class="attachment-post-thumbnail wp-post-image" alt="' . $alt_text . '">' . $video_icon .'</a>';
			echo $schema['img_size'];
		}
		
		else {
			if ( $enable_schema ) {
				echo '<meta itemprop="url" content="' . esc_url( $img ) . '" />' . $schema['img_size'];			
			}
		}
	echo '</div>';

	if ( $template == 'list-small' || $template == 'list-big' ) {
		echo '</div>';
	}
}