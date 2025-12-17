<?php
/**
 * Template part for the gallery display style
 * used in [insert_posts] shortcode
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */

$out = '';
$count = 1;
$protocol = is_ssl() ? 'https' : 'http';

printf( '<ul%s class="np-gallery%s clearfix%s%s">',
	$enable_schema ? ' itemscope="itemscope" itemtype="' . $protocol . '://schema.org/Blog"' : '',
	$enable_masonry ? ' masonry-enabled' : '',
	isset( $master_class ) && '' !== $master_class ? ' ' . implode( ' ', $master_class ) : '',
	$xclass ? ' ' . esc_attr( $xclass ) : ''
);

// Main loop
while ( $custom_query->have_posts() ) :
	$custom_query->the_post();
	global $post, $multipage;
	$multipage = 0;
	$custom_link = get_post_meta( $post->ID, 'np_custom_link', true );
	$post_opts = get_post_meta( get_the_id(), 'post_options', true );
	$ad_class = isset( $post_opts['sp_post'] ) ? ' sp-post' : '';

	/* Calculate appropriate class names for first and last grids */
	$fclass = ( 0 == ( ( $count - 1 ) % (int)$col ) ) ? ' first-grid' : '';
	$lclass = ( 0 == ( $count % (int)$col ) ) ? ' last-grid' : '';
	if ( has_post_thumbnail() ) {
		$thumb_id = get_post_thumbnail_id( get_the_ID() );
		?>
		<li <?php post_class( 'col'. $col . $fclass . $lclass . $ad_class ); ?>>
			<?php
			if ( $ad_class ) {
				echo '<span class="sp-label-archive">' . get_option( 'pls_sp_label', __( 'Advertisement', 'newsplus') ) . '</span>';
			}
            echo '<div class="post-thumb">';
            if ( method_exists( 'NewsPlus_Shortcodes', 'newsplus_image_resize' ) ) {
				$img_src = wp_get_attachment_image_src( $thumb_id, 'full' );
				$img = $img_src[0];
				$img = NewsPlus_Shortcodes::newsplus_image_resize( $img, $imgwidth, $imgheight, $imgcrop, $imgquality, '', '' );
            }
            else {
				$img = get_the_post_thumbnail( 'full' );
            }

            $alt_text = basename( get_attached_file( $thumb_id ) );

            $overlay_icon = sprintf( '<div class="lb-overlay">%s</div>',
								$show_title ? '<span class="entry-title">' . esc_attr( get_the_title() ) . '</span>' : ''
							);

            printf( '<a%1$s href="%2$s" title="%3$s"><img src="%4$s" class="attachment-post-thumbnail wp-post-image" title="%3$s" alt="%5$s">%6$s</a>',
				$lightbox ? ' data-rel="prettyPhoto[\'group1\']"' : '',
				$lightbox ? esc_url( $img_src[0] ) : ( $ext_link && $custom_link ? esc_url( $custom_link) : esc_url( get_permalink() ) ),
				esc_attr( get_the_title() ),
				esc_url( $img ),
				esc_attr( $alt_text ),
				$overlay_icon
            );
            ?>
		</li>
		<?php $count++;
	}

endwhile;
$out .= '</ul>';
echo $out;