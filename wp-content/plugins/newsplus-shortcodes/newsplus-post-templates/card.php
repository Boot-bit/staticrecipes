<?php
/**
 * Template part for the card display style
 * @used in [insert_posts] shortcode
 *
 * @package NewsPlus
 * @subpackage NewsPlus_Shortcodes
 * @version 3.6.0
 */
$out = '';
$count = 1;
$protocol = is_ssl() ? 'https' : 'http';
$schema =  NewsPlus_Shortcodes::newsplus_schema( $enable_schema );

printf( '<div%s class="grid-row%s clearfix%s%s%s">',
	$enable_schema ? ' itemscope="itemscope" itemtype="' . $protocol . '://schema.org/Blog"' : '',
	$enable_masonry ? ' masonry-enabled' : '',
	$hsize ? ' fs-' . esc_attr( $hsize ) : '',
	isset( $master_class ) && '' !== $master_class ? ' ' . implode( ' ', $master_class ) : '',
	$xclass ? ' ' . esc_attr( $xclass ) : ''
);

// Main loop
while ( $custom_query->have_posts() ) :
	$custom_query->the_post();
	global $post, $multipage;
	$multipage = 0;
	$short_title = get_post_meta( $post->ID, 'np_short_title', true );
	$custom_link = get_post_meta( $post->ID, 'np_custom_link', true );
	$post_opts = get_post_meta( get_the_id(), 'post_options', true );
    $ad_class = isset( $post_opts['sp_post'] ) ? ' sp-post' : '';

	// Generate post meta
	$meta_args = array(
		'template'	=> 'card',
		'date_format' => $date_format,
		'enable_schema' => $enable_schema,
		'hide_cats' => $hide_cats,
		'hide_reviews' => $hide_reviews,
		'hide_date' => $hide_date,
		'hide_author' => $hide_author,
		'hide_excerpt' => $hide_excerpt,
		'show_avatar' => $show_avatar,
		'hide_views' => $hide_views,
		'hide_comments' => $hide_comments,
		'readmore' => $readmore,
		'readmore_text' => $readmore_text,
		'publisher_logo' => $publisher_logo,
		'use_word_length' => $use_word_length,
		'excerpt_length' => $excerpt_length,
		'ext_link' => $ext_link,
		'readmore_text' => $readmore_text,
		'sharing'	=> $sharing,
		'share_btns' => $share_btns
	);
	$rows = NewsPlus_Shortcodes::newsplus_meta( $meta_args );

	/* Calculate appropriate class names for first and last grids */
	$fclass = ( 0 == ( ( $count - 1 ) % (int)$col ) ) ? ' first-grid' : '';
	$lclass = ( 0 == ( $count % (int)$col ) ) ? ' last-grid' : '';
	?>
	<article <?php post_class( 'newsplus entry-grid entry-card col'. $col . $fclass . $lclass . $ad_class ); echo $schema['container']; ?>>
        <div class="card-wrap">
            <?php
			if ( $ad_class ) {
				echo '<span class="sp-label-archive">' . get_option( 'pls_sp_label', __( 'Advertisement', 'newsplus') ) . '</span>';
			}
            $thumb_path = apply_filters( 'newsplus_thumb_path',  '/format/format.php' );
    
            if ( 'video' == get_post_format() ) {
                $thumb_path = apply_filters( 'newsplus_thumb_path',  '/format/format-video.php' );
            }
    
            if ( locate_template( $thumb_path ) ) {
                require( get_stylesheet_directory() . $thumb_path );
            }
    
            else {
                require( dirname( __FILE__ ) . $thumb_path );
            }
            ?>
    
            <div class="card-content">
    
                <?php
                echo $rows['row_1'];
    
                printf( '<%1$s%2$s class="entry-title"><a href="%3$s" title="%4$s">%4$s</a></%1$s>',
                    $htag,
                    $schema['heading'],
                    $ext_link && $custom_link ? esc_url( $custom_link) : esc_url( get_permalink() ),
                    ( $use_short_title && $short_title ) ? $short_title : get_the_title()
                );
    
                echo $rows['row_2'];
    
                if ( 'true' != $hide_excerpt ) {
                    printf( '<%s%s class="post-excerpt fs-%s">',
                        $ptag,
                        $schema['text'],
                        $psize
                    );
    
                    if ( 'true' == $use_word_length ) {
                        echo NewsPlus_Shortcodes::newsplus_short_by_word( get_the_excerpt(), $excerpt_length );
                    }
    
                    else {
                        echo NewsPlus_Shortcodes::newsplus_short( get_the_excerpt(), $excerpt_length );
                    }
    
                    echo '</' . $ptag . '>';
                }
    
                echo $rows['row_3']; ?>
    
            </div><!-- /.card-content -->
        </div><!-- /.card-wrap -->

	</article><!-- #post-<?php the_ID();?> -->

	<?php $count++;

endwhile;

$out .= '</div>';

echo $out;