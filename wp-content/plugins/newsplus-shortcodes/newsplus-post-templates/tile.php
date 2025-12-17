<?php
/**
 * Template part for the [newsplus_grid_list] shortcode
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

$protocol = is_ssl() ? 'https' : 'http';
$count = 0;
$out = sprintf( '<ul%s class="grid-list newsplus%s%s%s">',
	$enable_schema ? ' itemscope="itemscope" itemtype="' . $protocol . '://schema.org/Blog"' : '',
	' ' . esc_attr( $display_style ),
	isset( $master_class ) && '' !== $master_class ? ' ' . implode( ' ', $master_class ) : '',
	$xclass ? ' ' . esc_attr( $xclass ) : ''
);

while ( $custom_query->have_posts() ) :
	$custom_query->the_post();
	global $post, $multipage;
	$multipage = 0;
	$permalink = get_permalink();
	$title = get_the_title();

	$short_title = get_post_meta( $post->ID, 'np_short_title', true );
	$custom_link = get_post_meta( $post->ID, 'np_custom_link', true );
	$post_opts = get_post_meta( get_the_id(), 'post_options', true );
    $ad_class = isset( $post_opts['sp_post'] ) ? ' sp-post' : '';
	$sub = (int)$count > 0 ? '_sub' : '';

	// Generate post meta
	$meta_args = array(
		'date_format' => $date_format,
		'enable_schema' => $enable_schema,
		'hide_cats' => ${ 'hide_cats' . $sub},
		'hide_reviews' => ${ 'hide_reviews' . $sub},
		'hide_date' => ${ 'hide_date' . $sub},
		'hide_author' => ${ 'hide_author' . $sub},
		'hide_excerpt' => ${ 'hide_excerpt' . $sub},
		'show_avatar' => ${ 'show_avatar' . $sub},
		'hide_views' => ${ 'hide_views' . $sub},
		'hide_comments' => ${ 'hide_comments' . $sub},
		'readmore' => ${ 'readmore' . $sub},
		'readmore_text' => $readmore_text,
		'publisher_logo' => $publisher_logo,
		'use_word_length' => $use_word_length,
		'excerpt_length' => $excerpt_length,
		'sharing'	=> $sharing,
		'share_btns' => $share_btns
	);

	$rows = NewsPlus_Shortcodes::newsplus_meta( $meta_args );

	if ( has_post_thumbnail() ) {
		$img_src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		$img = $img_src[0];
		$imgwidth_big = $imgheight_big = $imgwidth_small = $imgheight_small = '';
		$imgwidth_big = (int)( ( (int)$viewport_width - (int)$gutter ) / 2 );
		$imgheight_big = (int)( $imgwidth_big * $aspect_ratio );
		
		// Round off to even value if the height is odd
		// Needed for preventing pixel fraction value like .5px
		$imgheight_big = ( $imgheight_big % 2 == 0 ) ? $imgheight_big : $imgheight_big - 1;
		$imgwidth_small = (int)( ( (int)$imgwidth_big - (int)$gutter ) / 2 );				

		if ( $display_style == 's2' ) {
			$imgheight_small = (int)( ( $imgheight_big - 2 * (int)$gutter ) / 3 );
		}

		else {
			$imgheight_small = (int)( ( $imgheight_big - (int)$gutter ) / 2 );
		}

		$thumbnail_big = NewsPlus_Shortcodes::newsplus_image_resize( $img, $imgwidth_big, $imgheight_big, 'true', $imgquality, '', '' );
		$thumbnail_small = NewsPlus_Shortcodes::newsplus_image_resize( $img, $imgwidth_small, $imgheight_small, 'true', $imgquality, '', '' );

		$thumbnail_big = ! empty( $thumbnail_big ) ? $thumbnail_big : $img;
		$thumbnail_small = ! empty( $thumbnail_small ) ? $thumbnail_small : $img;


		$format_icon = '<div class="hover-overlay"></div>';

		if ( 'video' == get_post_format() ) {
			$format_icon = '<div class="video-overlay"></div>';
		}

		if ( 'gallery' == get_post_format() ) {
			$format_icon = '<div class="gallery-overlay"></div>';
		}

		$schema_big =  NewsPlus_Shortcodes::newsplus_schema( $enable_schema, $imgwidth_big, $imgheight_big );
		$schema_small =  NewsPlus_Shortcodes::newsplus_schema( $enable_schema, $imgwidth_small, $imgheight_small );

		$thumblink_big = sprintf( apply_filters( 'featured_grid_thumbnail_big','<div%1$s class="post-thumb"><a href="%2$s" title="%3$s"><img%4$s src="%5$s" alt="%3$s" title="%3$s"/>%6$s%7$s%8$s</a></div>' ),
			$schema_big['img_cont'],
			$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( $permalink ),
			( $use_short_title && $short_title ) ? $short_title : esc_attr( $title ),
			$schema_big['img'],
			$thumbnail_big,
			$format_icon,
			$schema_big['img_size'],
			$featured_label ? '<span class="featured-title">' . esc_attr( $featured_label ) . '</span>' : ''
		);

		$thumblink_small = sprintf( apply_filters( 'featured_grid_thumbnail_small','<div%1$s class="post-thumb"><a href="%2$s" title="%3$s"><img%4$s src="%5$s" alt="%3$s" title="%3$s"/>%6$s%7$s</a></div>' ),
			$schema_small['img_cont'],
			$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( $permalink ),
			( $use_short_title && $short_title ) ? $short_title : esc_attr( $title ),
			$schema_small['img'],
			$thumbnail_small,
			$format_icon,
			$schema_small['img_size']
		);

		$excerpt = '';

		if ( 'true' != $hide_excerpt ) {
			$excerpt = sprintf( '<%1$s%2$s class="post-excerpt">%3$s</%1$s>',
			$ptag,
			$schema_big['text'],
			'true' == $use_word_length ? NewsPlus_Shortcodes::newsplus_short_by_word( get_the_excerpt(), $excerpt_length ) : NewsPlus_Shortcodes::newsplus_short( get_the_excerpt(), $excerpt_length )
			);
		}

		if ( $display_style == 's1' || $display_style == 's2' ) {
			if ( $count == 0 ) {
				$format = apply_filters( 'featured_grid_first_item_output', '<li%1$s class="grid-2x2">%2$s<div class="grid-content"><div class="grid-overlay dark-bg">%3$s<%4$s%5$s class="entry-title%6$s"><a href="%7$s" title="%8$s">%8$s</a></%4$s>%9$s%10$s%11$s</div></div></li>' );
				$out .= sprintf ( $format,
					$schema_big['container'],
					$thumblink_big,
					$rows['row_1'],
					$htag ? esc_html( $htag ) : 'h2',
					$schema_big['heading'],
					$hsize ? ' fs-' . esc_attr( $hsize ) : '',
					$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( $permalink ),
					( $use_short_title && $short_title ) ? $short_title : esc_attr( $title ),
					$rows['row_2'],
					$excerpt,
					$rows['row_3']
				);
			}
			else {
				$format = apply_filters( 'featured_grid_first_item_output', '<li%1$s class="grid-1x1">%2$s<div class="grid-content"><div class="grid-overlay dark-bg">%3$s<%4$s%5$s class="entry-title%6$s"><a href="%7$s" title="%8$s">%8$s</a></%4$s>%9$s%10$s</div></div></li>' );
				$out .= sprintf ( $format,
					$schema_small['container'],
					$thumblink_small,
					$rows['row_1'],
					$htag_sub ? esc_html( $htag_sub ) : 'h2',
					$schema_small['heading'],
					$hsize_sub ? ' fs-' . esc_attr( $hsize_sub ) : '',
					$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( $permalink ),
					( $use_short_title && $short_title ) ? $short_title : esc_attr( $title ),
					$rows['row_2'],
					$rows['row_3']
				);
			}
			$count++;
		} // s1 || s2
		else {
			$li_class = 'grid-1x1';
			if ( $display_style == 's3' ) {
				$li_class = 'grid-1x1';
			}
			elseif ( $display_style == 's4' ) {
				$li_class = 'grid-2x2';
			}
			elseif ( $display_style == 's5' ) {
				$li_class = 'grid-4x4';
			}
			elseif ( $display_style == 's6' ) {
				$li_class = 'grid-3x3';
			}
			$format = apply_filters( 'featured_grid_first_item_output', '<li%1$s class="%12$s">%2$s<div class="grid-content"><div class="grid-overlay dark-bg">%3$s<%4$s%5$s class="entry-title%6$s"><a href="%7$s" title="%8$s">%8$s</a></%4$s>%9$s%10$s%11$s</div></div></li>' );
			$out .= sprintf ( $format,
				$schema_small['container'],
				$thumblink_small,
				$rows['row_1'],
				$htag_sub ? esc_html( $htag_sub ) : 'h2',
				$schema_small['heading'],
				$hsize_sub ? ' fs-' . esc_attr( $hsize_sub ) : '',
				$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( $permalink ),
				( $use_short_title && $short_title ) ? $short_title : esc_attr( $title ),
				$rows['row_2'],
				$excerpt,
				$rows['row_3'],
				$li_class
			);
		}
	} // has_post_thumbnail

endwhile;
$out .= '</ul>';

echo $out;