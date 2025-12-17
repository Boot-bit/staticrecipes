<?php
/*
 Plugin Name: NewsPlus Shortcodes
 Version: 3.6.0
 Author: Saurabh Sharma
 Author URI: http://themeforest.net/user/SaurabhSharma
 Description: Shortcodes and widgets for the NewsPlus WordPress theme
*/

class NewsPlus_Shortcodes {

	function __construct() {
		require_once( 'widgets/widgets.php' );
		require_once( 'includes/BFI_Thumb.php' );
		add_action( 'init', array($this, 'init' ) );
		add_action( 'init', array($this, 'newsplus_add_shortcodes' ), 10 );
		add_action( 'init', array($this, 'newsplus_kc_map_shortcodes' ), 20 );
	}

	function init() {

		// Translation
		load_plugin_textdomain( 'newsplus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		if ( ! is_admin() ) {

				// CSS files
				if ( ! get_option( 'pls_fa', false ) ) {
					wp_enqueue_style( 'newsplus-fontawesome', plugin_dir_url( __FILE__ ) . 'assets/css/font-awesome.min.css' );
				}
				wp_enqueue_style( 'newsplus-shortcodes', plugin_dir_url( __FILE__ ) . 'assets/css/newsplus-shortcodes.css' );
				wp_enqueue_style( 'newsplus-owl-carousel', plugin_dir_url( __FILE__ ) . 'assets/css/owl.carousel.css', array(), null );
				wp_enqueue_style( 'newsplus-prettyphoto', plugin_dir_url( __FILE__ ) . 'assets/css/prettyPhoto.css', array(), null );
				if ( is_rtl() ) {
					wp_enqueue_style( 'newsplus-shortcodes-rtl', plugin_dir_url( __FILE__ ) . 'assets/css/rtl-newsplus-shortcodes.css' );
				}

				// JavaScript files
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-masonry' );
				wp_enqueue_script( 'newsplus-custom-js', plugin_dir_url( __FILE__ ) . 'assets/js/custom.js', array( 'jquery' ), '', true );
				wp_enqueue_script( 'jq-easing', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.easing.min.js', array(), '', true );
				wp_enqueue_script( 'newsplus-jq-owl-carousel', plugin_dir_url( __FILE__ ) . 'assets/js/owl.carousel.min.js', array( 'jquery' ), '', true );
				wp_enqueue_script( 'newsplus-prettyphoto-js', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.prettyPhoto.js', array(), '', true );
				wp_enqueue_script( 'jq-marquee', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.marquee.min.js', array(), '', true );
		}

		add_shortcode( 'col', array( &$this, 'col' ) );
		add_shortcode( 'row', array( &$this, 'row' ) );
		add_shortcode( 'tabs', array( &$this, 'tabs' ) );
		add_shortcode( 'tab', array( &$this, 'tab' ) );
		add_shortcode( 'toggle', array( &$this, 'toggle' ) );
		add_shortcode( 'accordion', array( &$this, 'accordion' ) );
		add_shortcode( 'acc_item', array( &$this, 'acc_item' ) );
		add_shortcode( 'box',  array( &$this, 'box' ) );
		add_shortcode( 'hr', array( &$this, 'hr' ) );
		add_shortcode( 'btn', array( &$this, 'btn' ) );
		add_shortcode( 'indicator', array( &$this, 'indicator' ) );
		add_shortcode( 'slider', array( &$this, 'slider' ) );
		add_shortcode( 'slide', array( &$this, 'slide' ) );
		add_shortcode( 'slide_video', array( &$this, 'slide_text' ) );
		add_shortcode( 'slide_text', array( &$this, 'slide_text' ) );
		add_shortcode( 'posts_slider', array( &$this, 'posts_slider' ) );
		add_shortcode( 'posts_carousel', array( &$this, 'posts_carousel' ) );
		add_shortcode( 'insert_posts', array( &$this, 'insert_posts' ) );
		add_shortcode( 'newsplus_sidebar', array( &$this, 'newsplus_sidebar' ) );
		add_shortcode( 'newsplus_grid_list', array( &$this, 'newsplus_grid_list' ) );
		add_shortcode( 'newsplus_news_ticker', array( &$this, 'newsplus_news_ticker' ) );
		add_shortcode( 'newsplus_recipe', array( &$this, 'newsplus_recipe' ) );
		add_shortcode( 'recipe_method', array( &$this, 'newsplus_recipe_method' ) );
		add_shortcode( 'newsplus_image', array( &$this, 'newsplus_image' ) );
		add_shortcode( 'newsplus_theme_url', array( &$this, 'newsplus_theme_url' ) );
		add_shortcode( 'newsplus_title', array( &$this, 'newsplus_title' ) );

	}

	/**
 * Generate thumbnail from Video embeds
 * http://wordpress.stackexchange.com/questions/40846/generating-thumbnails-for-video
 */


	public static function newsplus_video_thumb( $url = '' ) {
		require_once( ABSPATH . 'wp-includes/class-oembed.php' );
		$oembed = new WP_oEmbed;

		$provider = $oembed->discover( $url );
		//$provider = 'http://www.youtube.com/oembed';
		$video = $oembed->fetch( $provider, $url, array() );
		if ( $video ) {
			$title = $video->title;
			$thumb = $video->thumbnail_url;
			return array( 'url' => $thumb, 'title' => $title );
		}
	}



	public static function newsplus_create_list_items( $list_main = '', $list_other = '', $schema_prop = '', $link = false ) {

		$rcu = $rcuo = $rcat = $rcato = $temp = array();
		$rcu_out = $rcato = $tag = $tag_link = '';

		$rcu = explode( ',', $list_main );
		if ( '' !== $list_other ) {
			$rcuo = explode( ',', $list_other );
		}
		$temp = array_merge( $rcu, $rcuo );
		if ( is_array( $temp ) ) {
			foreach( $temp as $rcu_item ) {
				if ( '' != $rcu_item ) {
					if ( $link ) {
						$tag_link = get_term_by( 'name', $rcu_item, 'post_tag' );
						$cat_link = get_term_by( 'name', $rcu_item, 'category' );

						if ( isset( $tag_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled" itemprop="%1$s"><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
								$schema_prop,
								get_term_link( $tag_link->term_id, 'post_tag' ),
								sprintf( __( 'View all recipies tagged %s', 'newsplus' ), $rcu_item ),
								$rcu_item
							);
						}

						// Check if a category is available
						elseif ( isset( $cat_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled" itemprop="%1$s"><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
								$schema_prop,
								get_term_link( $cat_link->term_id, 'category' ),
								sprintf( __( 'View all recipies in %s', 'newsplus' ), $rcu_item ),
								$rcu_item
							);
						}

						else {
							$rcu_out .= '<li class="cm-value" itemprop="' . $schema_prop . '">' . $rcu_item . '</li>';
						}
					}
					else {
						$rcu_out .= '<li class="cm-value" itemprop="' . $schema_prop . '">' . $rcu_item . '</li>';
					}
				}
			}
		}

		return array( 'html' => $rcu_out, 'arr' => $temp );

	}


	public static function newsplus_create_diet_items( $list_main = '', $link = false ) {

		$rcu = $list_main !== '' ? explode( ',', $list_main ) : '';
		$rcu_out = '';
		$protocol = is_ssl() ? 'https' : 'http';

		if ( is_array( $rcu ) && ! empty( $rcu ) ) {
			foreach( $rcu as $rcu_item ) {
				$sfd = str_replace( ' ', '', $rcu_item );

				if ( $link ) {
					$tag_link = get_term_by( 'name', $rcu_item, 'post_tag' );
					$cat_link = get_term_by( 'name', $rcu_item, 'category' );

					// Check if a tag is available
					if ( isset( $tag_link->term_id ) ) {
						$rcu_out .= sprintf( '<li class="cm-value link-enabled"><link itemprop="suitableForDiet" href="' . $protocol . '://schema.org/%1$sDiet" /><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
							$sfd,
							get_term_link( $tag_link->term_id, 'post_tag' ),
							sprintf( __( 'View all recipies tagged %s', 'newsplus' ), $rcu_item ),
							$rcu_item
						);
					}

					// Check if a category is available
					elseif ( isset( $cat_link->term_id ) ) {
						$rcu_out .= sprintf( '<li class="cm-value link-enabled"><link itemprop="suitableForDiet" href="' . $protocol . '://schema.org/%1$sDiet" /><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
							$sfd,
							get_term_link( $cat_link->term_id, 'category' ),
							sprintf( __( 'View all recipies in %s', 'newsplus' ), $rcu_item ),
							$rcu_item
						);
					}

					// Else no link
					else {
						$rcu_out .= sprintf( '<li class="cm-value"><link itemprop="suitableForDiet" href="' . $protocol . '://schema.org/%1$sDiet" />%2$s</li>',
							$sfd,
							$rcu_item
						);
					}
				}
				else {
					$rcu_out .= sprintf( '<li class="cm-value"><link itemprop="suitableForDiet" href="' . $protocol . '://schema.org/%1$sDiet" />%2$s</li>',
						$sfd,
						$rcu_item
					);
				}
			}
		}

		return array( 'html' => $rcu_out, 'arr' => $rcu );

	}

	public static function newsplus_nutrient_items( $nutrition = array() ) {

		$nu_out = '';
		$schema_prop = '';

		if ( is_array( $nutrition ) ) {
			foreach( $nutrition as $nu ) {
				$nu_out .= '<li><span class="label">' . $nu->nutrient_label . '</span><span itemprop="' . $nu->nutrient. '">' . $nu->amount . '</span></li>';
			}
		}

		return $nu_out;

	}

	public static function newsplus_time_convert( $time_in_min = '' ) {
		$hr = $min = 0;
		$arr = array( 'schema' => '', 'readable' => '' );
		$readable = $out = '';
		if ( isset( $time_in_min ) ) {
			if ( (int)$time_in_min >= 60 ) {
				$hr = floor( $time_in_min / 60 );
				$min = $time_in_min % 60;
			}

			else {
				$min = $time_in_min % 60;
			}

			if ( (int)$hr > 0 && (int)$min <= 0 ) {
				$out = $hr . 'H';
				$readable = sprintf( _x( '%s hr', 'xx hours', 'newsplus' ), number_format_i18n( $hr ) );
			}

			elseif ( (int)$hr <= 0 && (int)$min > 0 ) {
				$out = $min . 'M';
				$readable = sprintf( _x( '%s min', 'xx minutes', 'newsplus' ), number_format_i18n( $min ) );
			}

			elseif ( (int)$hr > 0 && (int)$min > 0 ) {
				$out = $hr . 'H' . $min . 'M';
				$readable = sprintf( _x( '%1$s hr %2$s min', 'xx hour yy minutes', 'newsplus' ), number_format_i18n( $hr ), number_format_i18n( $min ) );
			}

			$arr[ 'schema' ] = 'PT' . $out;
			$arr[ 'readable' ] = $readable;
		}
		return $arr;
	}

// Return parameters for BFI image resize
	public static function newsplus_image_resize( $src, $imgwidth, $imgheight, $imgcrop, $imgquality, $imgcolor, $imggrayscale ) {
		$params = array();

		// Validate boolean params
		$crop = ( '' == $imgcrop || 'false' == $imgcrop ) ? false : true;
		$grayscale = ( '' == $imggrayscale || 'false' == $imggrayscale ) ? false : true;

		// Params array
		if ( $crop ) {
			$params['crop'] = true;
		}

		if ( $grayscale ) {
			$params['grayscale'] = true;
		}

		if ( '' != $imgquality ) {
			if ( (int)$imgquality < 1 ) {
				$quality = 1;
			} elseif ( (int)$imgquality > 100 ) {
				$quality = 100;
			} else {
				$quality = $imgquality;
			}
			$params['quality'] = (int)$quality;
		}

		if ( '' != $imgcolor ) {
			$color = preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $imgcolor ) ? $imgcolor : '';
			$params['color'] = $color;
		}

		// Validate width and height
		if ( isset( $imgwidth ) && (int)$imgwidth > 4 && '' != $imgwidth ) {
			$params['width'] = $imgwidth;
		}

		if ( isset( $imgheight ) && (int)$imgheight > 4 && '' != $imgheight ) {
			$params['height'] = $imgheight;
		}

		if ( function_exists( 'bfi_thumb' ) && ! empty( $params ) ) {
			return bfi_thumb( $src, $params );
		} else {
			return $src;
		}
	}


// Helper function for removing automatic p and br tags from nested short codes

public static function newsplus_return_clean( $content, $p_tag = false, $br_tag = false ) {
	$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content );

	if ( $br_tag )
		$content = preg_replace( '#<br \/>#', '', $content );

	if ( $p_tag )
		$content = preg_replace( '#<p>|</p>#', '', $content );

	return do_shortcode( shortcode_unautop( trim( $content ) ) );
}


/**
 * Function to shorten any text by character length
 */

	public static function newsplus_short( $text, $limit ) {
		$chars_limit = intval( $limit );
		$chars_text = strlen( $text );
		if ( $chars_text > $chars_limit ) {
			$text = strip_tags( $text );
			$text = $text . " ";
			$text = substr( $text, 0, $chars_limit );
			$text = substr( $text, 0, strrpos( $text, ' ' ) );
			return $text . "&hellip;";
		}
		else {
			return $text;
		}
	}


/**
 * Function to shorten any text by word length
 */

	public static function newsplus_short_by_word( $phrase, $max_words ) {
		if ( '' == $max_words ) {
			$max_words = 20;
		}
		$phrase_array = explode( ' ', $phrase );
		if ( count( $phrase_array ) > $max_words && $max_words > 0 ) {
			$phrase = implode( ' ', array_slice( $phrase_array, 0, $max_words ) ) . '&hellip;';
		}
		return $phrase;
	}


// Generate schema

	public static function newsplus_schema( $enable_schema, $imgwidth = 200, $imgheight = 200 ) {
		$sc_arr = array(
			'html'	=> '',
			'container' => '',
			'heading' => '',
			'heading_alt' => '',
			'text' => '',
			'img_cont' => '',
			'img' => '',
			'img_size' => '',
			'url' => ''	,
			'nav' => '',
			'bclist' => '',
			'bcelement' => '',
			'item' => '',
			'name' => '',
			'position' => ''
		);

		 // Is single post
		if ( is_single() || is_home() || is_archive() || is_category() ) {
			$html_type = "Blog";
		}
		// Is static front page
		elseif ( is_front_page() ) {
			$html_type = "Website";
		}
		// Is a general page
		 else {
			$html_type = 'WebPage';
		}

		if ( 'true' == $enable_schema ) {
			$protocol = is_ssl() ? 'https' : 'http';
			$schema = $protocol . '://schema.org/';


			$sc_arr['html'] = 'itemscope="itemscope" itemtype="' . $schema . $html_type . '" ';
			$sc_arr['container'] = ' itemscope="" itemtype="' . $schema . 'BlogPosting" itemprop="blogPost"';
			$sc_arr['heading'] = ' itemprop="headline mainEntityOfPage"';
			$sc_arr['heading_alt'] = ' itemprop="headline"';
			$sc_arr['text'] = ' itemprop="text"';

			$sc_arr['img_cont'] = ' itemprop="image" itemscope="" itemtype="' . $schema . 'ImageObject"';
			$sc_arr['img'] = ' itemprop="url"';

			$sc_arr['img_size'] = sprintf( ' <meta itemprop="width" content="%s"><meta itemprop="height" content="%s">', $imgwidth, $imgheight );
			$sc_arr['url'] = ' itemprop="url"';
			$sc_arr['nav'] = ' itemscope="itemscope" itemtype="' . $schema . 'SiteNavigationElement"';

			$sc_arr['bclist'] = ' itemscope="itemscope" itemtype="' . $schema . 'BreadcrumbList"';
			$sc_arr['bcelement'] = ' itemscope="itemscope" itemtype="' . $schema . 'ListItem" itemprop="itemListElement"';
			$sc_arr['item'] = ' itemprop="item"';
			$sc_arr['name'] = ' itemprop="name"';
			$sc_arr['position'] = ' itemprop="position"';

		}
		return $sc_arr;
	}

// Post meta for post modules
	public static function newsplus_meta( $args = array() ) {
		global $post;
		$custom_link = get_post_meta( $post->ID, 'np_custom_link', true );
			$defaults = array(
				'template'	=> 'grid',
				'date_format' => get_option( 'date_format' ),
				'enable_schema' => false,
				'hide_cats' => false,
				'hide_reviews' => false,
				'show_cats' => false,
				'show_reviews' => false,
				'hide_date' => false,
				'hide_author' => false,
				'show_avatar' => false,
				'hide_views' => false,
				'hide_comments' => false,
				'readmore' => false,
				'ext_link' => false,
				'readmore_text' => esc_attr__( 'Read more', 'newsplus' ),
				'publisher_logo' => get_template_directory_uri() . '/images/logo.png',
				'sharing'	=> false,
				'share_btns' => ''
			);

			$args = wp_parse_args( $args, $defaults );

			extract( $args );

			$protocol = is_ssl() ? 'https' : 'http';
			$schema = $protocol . '://schema.org/';
			// Date format
			$date = get_the_time( get_option( 'date_format' ) );

			if ( ! empty( $date_format ) ) {
				if ( $date_format == 'human' ) {
					$date = sprintf( _x( '%s ago', 'human time difference. E.g. 10 days ago', 'newsplus' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
				}
				else {
					$date = sprintf( _x( '%s<span class="sep time-sep"></span><span class="publish-time">%s</span>', 'date - separator - time', 'newsplus' ),
						get_the_time( esc_attr( $date_format ) ),
						get_the_time( get_option( 'time_format' ) )
					);
				}
			}

			$post_id = get_the_ID();

			$post_class_obj = get_post_class( $post_id );
			$post_classes = '';

			// Post classes
			if ( isset( $post_class_obj ) && is_array( $post_class_obj ) ) {
				$post_classes = implode( ' ', $post_class_obj );
			}

			// Category and review stars
			$review_meta = '';

			// Create category list
			$cat_list = '<ul class="post-categories">';
			$hasmore = false;
			$i = 0;
			$cats = get_the_category();
			$cat_limit = apply_filters( 'newsplus_cat_list_limit', 3 );
			$cat_count = intval( count( $cats ) - $cat_limit );
			if ( isset( $cats ) ) {
				foreach( $cats as $cat ) {
					if ( $i == $cat_limit ) {
						$hasmore = true;
						$cat_list .= '<li class="submenu-parent"><a class="cat-toggle" href="#">' . sprintf( esc_attr_x( '+ %d more', 'more count for category list', 'newsplus' ), number_format_i18n( $cat_count ) ) . '</a><ul class="cat-sub submenu">';
					}
					$cat_list .= '<li><a href="' . get_category_link( $cat->cat_ID ) . '">' . $cat->cat_name . '</a></li>';
					$i++;
				}
				$cat_list .= $hasmore ? '</ul></li></ul>' : '</ul>';
			}

			if ( 'list-small' == $template ) {
				$cat_meta = ( 'true' == $show_cats ) ? $cat_list : '';

				/**
				 * Post reviews
				 * Requires https://wordpress.org/plugins/wp-review/
				 */
				$review_meta = ( function_exists( 'wp_review_show_total' ) && 'true' == $show_reviews ) ? wp_review_show_total( $echo = false ) : '';
			} else {
				$cat_meta = ( 'true' != $hide_cats ) ? $cat_list : '';
				if ( function_exists( 'wp_review_show_total' ) && 'true' !== $hide_reviews ) {
					$review_meta = wp_review_show_total( $echo = false );
				}
			}

			// Author and date meta
			$meta_data = '';

			//if ( ! $hide_author || ! $hide_date ) {
				$author = get_the_author();
				$author_url = get_author_posts_url( get_the_author_meta( 'ID' ) );
				if ( '' == $author || ! isset( $author ) ) {
					global $post;
					$author_id = $post->post_author;
					$author = get_the_author_meta( 'display_name', $author_id );
					$author_url = get_author_posts_url( get_the_author_meta( 'ID', $author_id ) );
				}
				if ( $show_avatar ) {
					$meta_data .= sprintf( '<div%s%s class="author-avatar-32%s"><a%s href="%s" title="%s">%s%s</a></div>',
						$enable_schema ? ' itemscope itemtype="' . $schema . 'Person"' : '',
						$enable_schema ? ' itemprop="author"' : '',
						$hide_author && $hide_date ? ' avatar-only' : '',
						$enable_schema ? ' itemprop="name"' : '',
						esc_url( $author_url ),
						sprintf( esc_html__( 'More posts by %s', 'newsplus' ), esc_attr( $author ) ),
						$enable_schema ? '<span itemprop="image">' . get_avatar( get_the_author_meta( 'user_email' ), 32 ) . '</span>' : get_avatar( get_the_author_meta( 'user_email' ), 32 ),
						$enable_schema ? '<span class="hidden" itemprop="name">' . esc_attr( $author ) . '</span>' : ''

					);
				}

				$meta_data .= sprintf( '<ul class="entry-meta%s">',
					$show_avatar ? ' avatar-enabled' : ''
				);

				// Publisher Schema
				if ( $enable_schema ) {
					$meta_data .= '<li class="publisher-schema" itemscope itemtype="' . $schema . 'Organization" itemprop="publisher"><meta itemprop="name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '"/><div itemprop="logo" itemscope itemtype="' . $protocol . '://schema.org/ImageObject"><img itemprop="url" src="' . esc_url( $publisher_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '"/></div></li>';
				}

				//if ( ! $hide_date ) {
					$modified_date_format = 'human' == $date_format ? get_option( 'date_format' ) : $date_format;
					$meta_data .= sprintf( '<li class="post-time%1$s"><span class="published-label">%2$s</span><span class="posted-on"><time%3$s class="entry-date" datetime="%4$s">%5$s</time></span>%6$s</li>',
						$hide_date ? ' hidden' : '',
						_x( 'Published: ', 'post published date label', 'newsplus' ),
						$enable_schema ? ' itemprop="datePublished"' : '',
						esc_attr( get_the_date( 'c' ) ),
						$date,
						is_single() && get_the_date('H:i') != get_the_modified_date('H:i') ?
							sprintf( '<span class="sep updated-sep"></span><span class="updated-on"><meta itemprop="dateModified" content="%s">%s</span>',
							get_the_modified_date( DATE_W3C ),
							sprintf( _x( 'Updated: %s<span class="sep time-sep"></span><span class="updated-time">%s</a>', 'updated on date, time', 'newsnation' ),
								get_the_date() != get_the_modified_date() ? get_the_modified_date( get_option('date_format') ) : '',
								get_the_modified_date( get_option('time_format') )
							)
						) : ''
					);
				//}

				//if ( ! $hide_author ) {
					$meta_data .= sprintf( '<li%1$s%2$s class="post-author%3$s"><span class="screen-reader-text">%4$s </span><a href="%5$s">%6$s</a></li>',
						$enable_schema ? ' itemscope itemtype="' . $schema . 'Person"' : '',
						$enable_schema ? ' itemprop="author"' : '',
						$hide_author ? ' hidden' : '',
						esc_html_x( 'Author', 'Used before post author name.', 'newsplus' ),
						esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
						$enable_schema ? '<span itemprop="name">' . esc_attr( $author ) . '</span>' : esc_attr( $author )
					);
				//}

				$meta_data .= '</ul>';
			//}

			/**
			 * Social share buttons
			 * Uses newsplus_share_btns() function
			 */
			$share_btns_output =  $sharing ? self::newsplus_share_btns( $share_btns ) : '';

			// Comment link
			$num_comments = get_comments_number();
			$comment_meta = '';
			if ( comments_open() && ( $num_comments >= 1 ) && ! $hide_comments ) {
				$comment_meta = sprintf( '<a href="%1$s" class="post-comment" title="%2$s">%3$s%4$s</a>',
					esc_url( get_comments_link() ),
					sprintf( __( 'Comment on %s', 'newsplus' ), esc_attr( get_the_title() ) ),
					$enable_schema ? '<meta itemprop="discussionUrl" content="' . esc_url( get_comments_link() ) . '" />' : '',
					$enable_schema ? '<span itemprop="commentCount">' . $num_comments . '</span>' : $num_comments
				);
			}

			/**
			 * Post views
			 * Requires Plugin https://wordpress.org/plugins/post-views-counter/
			 */
			$views_meta = '';
			if ( function_exists( 'pvc_get_post_views' ) && ! $hide_views ) {
				$views_meta = sprintf( '<span class="post-views">%s</span>',
					pvc_get_post_views()
				);
			}

			// Generate rows of content
			$row_1 = '';
			$row_2 = '';
			$row_3 = '';
			$row_4 = '';
			if ( $review_meta != '' || $cat_meta != '' ) {
				$row_1 .= '<aside class="meta-row cat-row">';
				if ( $cat_meta != '' ) {
					$row_1 .= sprintf( '<div%s class="meta-col%s">%s</div>',
						$enable_schema ? ' itemprop="about"' : '',
						$review_meta != '' ? ' col-60' : '',
						$cat_meta
					);
				}

				if ( $review_meta != '' ) {
					$row_1 .= sprintf( '<div class="meta-col%s">%s</div>',
						$cat_meta != '' ? ' col-40 text-right' : '',
						$review_meta
					);
				}
				$row_1 .= '</aside>';
			}

			//if ( $meta_data || $views_meta || $comment_meta ) {
				$row_4 .= sprintf( '<aside class="meta-row row-3%s">',
					( $hide_date && $hide_author && $hide_views && $hide_comments && 'true' !== $sharing ) ? ' hidden' : ''
				);

				if ( '' == $views_meta && '' == $comment_meta && 'true' !== $sharing ) {
					$row_4 .= sprintf( '<div class="meta-col">%s</div>', $meta_data );
				}

				elseif ( '' == $meta_data ) {
					$row_4 .= sprintf( '<div class="meta-col">%s%s%s</div>', $views_meta, $comment_meta, $share_btns_output );
				}

				else {
					$row_4 .= sprintf( '<div class="meta-col col-60">%s</div><div class="meta-col col-40 text-right">%s%s%s</div>', $meta_data, $views_meta, $comment_meta, $share_btns_output );
				}
				$row_4 .= '</aside>';
			//}

			if ( $readmore ) {
				if ( $meta_data ) {
					$row_2 = sprintf( '<aside class="meta-row row-2%s"><div class="meta-col">%s</div></aside>',
						( $hide_date && $hide_author && $hide_views && $hide_comments && 'true' !== $sharing ) ? ' hidden' : '',
						$meta_data
					);
				}

				if ( $readmore || $views_meta || $comment_meta || $sharing ) {
					$row_3 = sprintf( '<aside class="meta-row row-3"><div class="meta-col col-50"><a class="readmore-link" href="%s">%s</a></div><div class="meta-col col-50 text-right">%s%s%s</div></aside>',
						$ext_link && $custom_link ? esc_url( $custom_link) : esc_url( get_permalink() ),
						esc_attr( $readmore_text ),
						$views_meta,
						$comment_meta,
						$share_btns_output
					);
				}
			}

			else {
				$row_3 = $row_4;
			}

		$meta_arr = array();
		$meta_arr['row_1'] = $row_1;
		$meta_arr['row_2'] = $row_2;
		$meta_arr['row_3'] = $row_3;
		$meta_arr['row_4'] = $row_4;
		return $meta_arr;
	}


/**
 * Social Sharing feature on single posts
 */

	public static function newsplus_share_btns( $share_btns ) {
		global $post;
		setup_postdata( $post );
		$share_btns = ( $share_btns ) ? explode( ',', $share_btns ) : '';

		// Set variables
		$out = '';
		$list = '';
		$share_image = '';
		$protocol = is_ssl() ? 'https' : 'http';

		if ( has_post_thumbnail( $post->ID ) ) {
			$share_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		}

		$share_content = strip_tags( get_the_excerpt() );

		if ( ! empty( $share_btns ) && is_array( $share_btns ) ) {
			$out .= sprintf( '<div class="np-inline-sharing-container"><a class="share-trigger" title="%1$s"><span class="sr-only">%1$s</span></a><ul class="np-inline-sharing">', __( 'Share this post', 'newsplus' ) );
				foreach ( $share_btns as $button ) {

					switch( $button ) {

						case 'twitter':
							$list .= sprintf( '<li class="newsplus-twitter"><a href="%s://twitter.com/home?status=%s" target="_blank" title="%s">%s</a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on twitter', 'newsplus' ), esc_attr__( 'Twitter', 'newsplus' ) );
						break;

						case 'facebook':
							$list .= sprintf( '<li class="newsplus-facebook"><a href="%s://www.facebook.com/sharer/sharer.php?u=%s" target="_blank" title="%s">%s</a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on facebook', 'newsplus' ), esc_attr__( 'Facebook', 'newsplus' ) );
						break;

						case 'whatsapp':
							if ( wp_is_mobile() ) {
								$list .= sprintf( '<li class="newsplus-whatsapp"><a href="whatsapp://send?text=%s" title="%s" data-action="share/whatsapp/share">%s</a></li>', urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on Whatsapp', 'newsplus' ), esc_attr__( 'Whatsapp', 'newsplus' ) );
							}
						break;

						case 'googleplus':
							$list .= sprintf( '<li class="newsplus-googleplus"><a href="%s://plus.google.com/share?url=%s" target="_blank" title="%s">%s</a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on Google+', 'newsplus' ), esc_attr__( 'Google+', 'newsplus' ) );
						break;

						case 'linkedin':
							$list .= sprintf( '<li class="newsplus-linkedin"><a href="%s://www.linkedin.com/shareArticle?mini=true&amp;url=%s" target="_blank" title="%s">%s</a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on LinkedIn', 'newsplus' ), esc_attr__( 'LinkedIn', 'newsplus' ) );
						break;

						case 'pinterest':
							$list .= sprintf( '<li class="newsplus-pinterest"><a href="%s://pinterest.com/pin/create/button/?url=%s&amp;media=%s" target="_blank" title="%s">%s</a></li>',
								esc_attr( $protocol ),
								urlencode( esc_url( get_permalink() ) ),
								esc_url( $share_image ),
								esc_attr__( 'Pin it', 'newsplus' ),
								esc_attr__( 'Pinterest', 'newsplus' )
							);
						break;

						case 'vkontakte':
							$list .= sprintf( '<li class="newsplus-vkontakte"><a href="%s://vkontakte.ru/share.php?url=%s" target="_blank" title="%s">%s</a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share via VK', 'newsplus' ), esc_attr__( 'VKOntakte', 'newsplus' ) );
						break;

						case 'reddit':
							$list .= sprintf( '<li class="newsplus-reddit"><a href="//www.reddit.com/submit?url=%s" title="%s">%s</a></li>', urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on Reddit', 'newsplus' ), esc_attr__( 'Reddit', 'newsplus' ) );
						break;

						case 'email':
							$list .= sprintf( '<li class="newsplus-email no-popup"><a href="mailto:someone@example.com?Subject=%s" title="%s">%s</a></li>', urlencode( esc_attr( get_the_title() ) ), esc_attr__( 'Email this', 'newsplus' ), esc_attr__( 'Email', 'newsplus' ) );

						break;
					} // switch

				} // foreach

			// Support extra meta items via action hook
			ob_start();
			do_action( 'newsplus_sharing_buttons_li' );
			$out .= ob_get_contents();
			ob_end_clean();

			$out .= $list . '</ul></div>';
		} // if

		return $out;
	}


	public static function col( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'type' => 'full',
			'xclass' => false
		), $atts ) );
		$out = sprintf( '<div class="column%s%s">%s</div>',
			' ' . esc_attr( $type ),
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
		if ( strpos( $type, 'last' ) ) {
			$out .= '<div class="clear"></div>';
		}
		return $out;
	}

	public static function row( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'xclass' => false
		), $atts ) );
		$out = sprintf( '<div class="row%s">%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
		return $out;
	}

	public static function tabs( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'xclass' => false
		), $atts ) );
		$out = sprintf( '<div class="tabber%s">%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
		return $out;
	}

	public static function tab( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'title' => 'mytab',
		  'xclass' => false
		  ), $atts ) );
		$tab_id = 'tab-' . rand( 2, 20000 );
		$out = sprintf( '<div class="tabbed%s" id="%s"><h4 class="tab_title">%s</h4>%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			esc_attr( $tab_id ),
			esc_attr( $title ),
			self::newsplus_return_clean( $content )
		);
		return $out;
	}

	public static function toggle( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'title' => 'mytoggle'
		  ), $atts ) );
		$out = '<h5 class="toggle">' . $title . '</h5><div class="toggle-content">' . self::newsplus_return_clean( $content ) . '</div>';
		return $out;
	}

	public static function accordion( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'xclass' => false
		  ), $atts ) );
		$out = sprintf( '<div class="accordion%s">%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
		return $out;
	}

	public static function acc_item( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'title' => 'myaccordion'
		  ), $atts ) );
		$out = '<h5 class="handle">' . $title . '</h5><div class="acc-content"><div class="acc-inner">' . self::newsplus_return_clean( $content ) . '</div></div>';
		return $out;
	}

	public static function box( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'style' 		=> '0',
		  'close_btn'	=> false,
		  'xclass'		=> false
		  ), $atts ) );

		$out = sprintf( '<div class="box box%s%s">%s%s</div>',
			esc_attr( $style ),
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content ),
			$close_btn ? '<span class="hide-box"></span>' : ''
		);
		return $out;
	}

	public static function btn( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'link'		=> false,
			'color'		=> false,
			'size'		=> false,
			'target'	=> false,
			'xclass'	=> false
		), $atts ) );

		return sprintf( '<a href="%s" class="ss-button%s%s"%s>%s</a>',
			$link ? esc_url( $link ) : '#',
			$color ? ' ' . esc_attr( $color ) : ' default',
			$size ? ' ' . esc_attr( $size ) : '',
			$target ? ' target="' . esc_attr( $target ) . '"' : '',
			self::newsplus_return_clean( $content )
		);
	}

	public static function hr( $atts, $content = null ) {
	   extract( shortcode_atts( array(
		  'style' => 'single',
		  'xclass' => false
		  ), $atts ) );
		$class = '';
		if ( $style == 'single' ) $class = 'hr';
		if ( $style == 'double' ) $class = 'hr-double';
		if ( $style == '3d' ) $class = 'hr-3d';
		if ( $style == 'bar' ) $class = 'hr-bar';
		if ( $style == 'dashed' ) $class = 'hr-dashed';

		return sprintf( '<div class="hr%s%s"></div>',
			' ' . esc_attr( $class ),
			$xclass ? ' ' . esc_attr( $xclass ) : ''
		);
	}

	public static function indicator( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'label'	=> 'Label here',
			'bg'	=> '#ffcc00',
			'value'	=> '75',
		), $atts ) );
		if ( $value < 0 )
			$value = 0;
		elseif ( $value > 100 )
			$value = 100;
		return '<div class="p_bar"><div class="p_label">' . esc_attr( $label ) . '</div><div class="p_indicator"><div class="p_active" style="width:' . esc_attr( $value ) . '%; background:' . esc_attr( $bg ) . '"></div></div><div class="p_value">' . esc_attr( $value ) . '%</div></div>';
	}

	public static function slider( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'effect'			=> 'fade',
			'easing'			=> 'swing',
			'speed'				=> '400',
			'timeout'			=> '4000',
			'animationloop'		=> 'false',
			'slideshow'			=> 'true',
			'smoothheight'		=> 'false',
			'controlnav'		=> 'true',
			'directionnav'		=> 'true',
			'items'				=> 1,
			'margin'			=> 24,
			'margin_mobile'		=> 16,
			'animatein'			=> '',
			'animateout'		=> '',
			'xclass'			=> false
		), $atts ) );

		$slider_id = 'slider-' . rand( 2, 400000 );

		$params = array(
				'items'				=> esc_attr( $items ),
				'margin'			=> esc_attr( $margin ),
				'margin_mobile'		=> esc_attr( $margin_mobile ),
				'speed'				=> esc_attr( $speed ),
				'timeout'			=> esc_attr( $timeout ),
				'autoheight'		=> esc_attr( $smoothheight ),
				'dots'				=> esc_attr( $controlnav ),
				'nav'				=> esc_attr( $directionnav ),
				'loop'				=> esc_attr( $animationloop ),
				'autoplay'			=> esc_attr( $slideshow ),
				'animatein'			=> esc_attr( $animatein ),
				'animateout'		=> esc_attr( $animateout )
			);

		$json = json_encode( $params );

		$out = sprintf( '<div class="owl-wrap np-posts-slider%s" data-params=\'%s\'><div class="owl-carousel owl-loading" id="%s">%s</div></div>',
				$xclass ? ' ' . esc_attr( $xclass ) : '',
				$json,
				esc_attr( $slider_id ),
				self::newsplus_return_clean( $content, false, true )
				//$container_markup
			);

		return self::newsplus_return_clean($out, 0, 1);
	}

	public static function slide( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'xclass' => false
		), $atts ) );
		return sprintf( '<div class="slide%s">%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
	}

	public static function slide_text( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'xclass' => false
		), $atts ) );

		return sprintf( '<div class="slide-content%s">%s</div>',
			$xclass ? ' ' . esc_attr( $xclass ) : '',
			self::newsplus_return_clean( $content )
		);
	}

	public static function posts_carousel( $atts ) {
		$params = shortcode_atts( array(
			'query_type'		=> 'category',
			'cats'				=> null,
			'posts'				=> null,
			'pages'				=> null,
			'post__not_in'		=> null,
			'post__in'			=> null,
			'tags'				=> null,
			'post_type'			=> null,
			'taxonomy'			=> null,
			'terms'				=> null,
			'blog_id'			=> null,
			'operator'			=> 'IN',
			'order'				=> 'desc',
			'orderby'			=> 'date',
			'num'				=> '6',
			'offset'			=> '0',
			'ignore_sticky'		=> 0,
			'excerpt_length'	=> '140',
			'use_word_length' 	=> 'false',
			'hide_excerpt'		=> 'false',
			'hide_meta'			=> 'false',
			'hide_image'		=> 'false',
			'hide_video'		=> 'true',
			'imgwidth'			=> '',
			'imgheight'			=> '',
			'imgcrop'			=> false,
			'imgupscale'		=> 'true',
			'imgquality'		=> '',
			'effect'			=> 'fade',
			'easing'			=> 'swing',
			'speed'				=> '400',
			'timeout'			=> '4000',
			'animationloop'		=> 'false',
			'slideshow'			=> 'true',
			'smoothheight'		=> 'false',
			'controlnav'		=> 'true',
			'directionnav'		=> 'true',
			'items'				=> 3,
			'margin'			=> 24,
			'margin_mobile'		=> 16,
			'animatein'			=> '',
			'animateout'		=> '',
			'xclass'			=> false,
			'ptclass'			=> false,
			'htag'				=> 'h2',
			'hsize'				=> '',
			'ptag'				=> 'p',
			'enable_schema'		=> false,
			'use_short_title'	=> false,
			'ext_link'			=> false
		), $atts );

		$sc_params = '';

		foreach( $params as $key => $value ) {
			if ( null !== $value ) {
				$sc_params .= $key . '="' . $value . '" ';
			}
		}

		return do_shortcode( '[posts_slider ' . $sc_params . ']' );

	}

	public static function posts_slider( $atts ) {
		extract( shortcode_atts( array(

			// Deprecated params
			'query_type'		=> 'category',
			'cats'				=> null,
			'posts'				=> null,
			'pages'				=> null,
			'tags'				=> null,

			// New WP Query params
			'author_name' 			=> null,
			'author__in' 			=> null,
			'cat' 					=> null,
			'category_name' 		=> null,
			'tag' 					=> null,
			'tag_id' 				=> null,
			'taxonomy' 				=> null,
			'terms'					=> null,
			'p' 					=> null,
			'name' 					=> null,
			'page_id' 				=> null,
			'pagename' 				=> null,
			'post__in' 				=> null,
			'post__not_in' 			=> null,
			'post_type' 			=> null,
			'post_status' 			=> 'publish',
			'num' 					=> 5,
			'offset' 				=> 0,
			'ignore_sticky_posts' 	=> false,
			'order' 				=> 'DESC',
			'orderby' 				=> 'date',
			'year' 					=> null,
			'monthnum' 				=> null,
			'w' 					=> null,
			'day'					=> null,
			'meta_key' 				=> null,
			'meta_value'			=> null,
			'meta_value_num' 		=> null,
			'meta_compare' 			=> '=',
			's' 					=> null,
			'blog_id'				=> null,

			'substyle'			=> 'grid',
			'excerpt_length'	=> '140',
			'use_word_length' 	=> 'false',
			'use_short_title'	=> false,
			'ext_link'			=> false,
			'hide_excerpt'		=> false,
			'hide_meta'			=> false,
			'hide_image'		=> false,
			'hide_video'		=> 'true',
			'imgcrop'			=> false,
			'imgupscale'		=> 'true',
			'imgquality'		=> '',

			// Slider params
			'effect'			=> 'slide', // fade
			'easing'			=> 'swing',
			'speed'				=> '400',
			'timeout'			=> '4000',
			'animationloop'		=> 'false',
			'slideshow'			=> 'true',
			'smoothheight'		=> 'false',
			'controlnav'		=> 'true',
			'directionnav'		=> 'true',
			'items'				=> 1,
			'margin'			=> 24,
			'margin_mobile'		=> 16,
			'animatein'			=> 'fadeIn',
			'animateout'		=> 'fadeOut',
			'imgwidth'			=> '',
			'imgheight'			=> '',
			'imgcrop'			=> false,
			'imgupscale'		=> 'true',
			'imgquality'		=> '',
			'enable_schema'		=> false,
			'date_format'		=> get_option( 'date_format' ),
			'hide_cats' 		=> false,
			'hide_reviews'		=> false,
			'hide_date' 		=> false,
			'hide_author' 		=> false,
			'hide_excerpt' 		=> false,
			'show_avatar' 		=> false,
			'hide_views' 		=> false,
			'hide_comments' 	=> false,
			'readmore' 			=> false,
			'readmore_text' 	=> false,
			'publisher_logo' 	=> get_template_directory_uri() . '/images/logo.png',
			'excerpt_length' 	=> 140,
			'htag'				=> 'h2',
			'hsize'				=> '',
			'ptag' 				=> 'p',
			'psize' 			=> '',
			'video_custom_field' => 'pf_video',
			'template'			=> 'grid',
			'xclass'			=> false,
			'ptclass'			=> false,
			'overlay_style'		=> 'default',
			'sharing'			=> false,
			'share_btns'		=> ''
		), $atts ) );

		// Sanitize WP Query args
		$author__in 				= $author__in ? explode( ',', $author__in ) : null;
		$post__in 					= $post__in ? explode( ',', $post__in ) : ( $posts ? explode( ',', $posts ) : ( $pages ? explode( ',', $pages ) : null ) );
		$post__not_in 				= $post__not_in ? explode( ',', $post__not_in ) : null;
		$terms 						= ( $terms ) ? explode( ',', $terms ) : null;
		$post_type					= ( $post_type ) ? explode( ',', $post_type ) : null;
		$taxonomy					= ( $taxonomy ) ? explode( ',', $taxonomy ) : null;
		$tax_query 					= null;

		if ( $taxonomy && $terms ) {
			$tax_query = array( 'relation' => 'OR' );

			if ( is_array( $taxonomy ) ) {
				foreach( $taxonomy as $tax ) {
					$tax_query[] = array(
						'taxonomy'	=> $tax,
						'field'		=> 'slug',
						'terms'		=> $terms,
						'operator' 	=> 'IN' // Allowed values AND, IN, NOT IN
					);
				}
			}
		}

		// Allowed args in WP Query
		$custom_args = array(
			'author_name' 			=> $author_name,
			'author__in' 			=> $author__in,
			'cat' 					=> $cat ? $cat : ( $cats ? $cats : null ),
			'category_name' 		=> $category_name,
			'tag' 					=> $tag ? $tag : ( $tags ? $tags : null ),
			'tag_id' 				=> $tag_id,
			'tax_query' 			=> $tax_query,
			'p' 					=> $p,
			'name' 					=> $name,
			'page_id' 				=> $page_id,
			'pagename' 				=> $pagename,
			'post__in' 				=> $post__in,
			'post__not_in' 			=> $post__not_in,
			'post_type' 			=> $post_type,
			'post_status' 			=> $post_status,
			'posts_per_page' 		=> $num,
			'offset' 				=> $offset,
			'ignore_sticky_posts' 	=> $ignore_sticky_posts,
			'order' 				=> $order,
			'orderby' 				=> $orderby,
			'year' 					=> $year,
			'monthnum' 				=> $monthnum,
			'w' 					=> $w,
			'day'					=> $day,
			'meta_key' 				=> $meta_key,
			'meta_value'			=> $meta_value,
			'meta_value_num' 		=> $meta_value_num,
			's' 					=> $s,
		);

		$new_args = array();

		// Set args which are provided by user
		foreach ( $custom_args as $key => $value ) {
			if ( isset( $value ) )
				$new_args[ $key ] = $value;
		}

		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		// Get master class of King Composer
		$master_class = apply_filters( 'kc-el-class', $atts );

		$custom_query = new WP_Query( $new_args );

		if ( $custom_query->have_posts() ) :

			ob_start();

			$template_path = apply_filters( 'newsplus_template_path',  '/newsplus-post-templates/' );

			if ( locate_template( $template_path . 'slider-' . $substyle . '.php' ) ) {
				require( get_stylesheet_directory() . $template_path . 'slider-' . $substyle . '.php' );
			}

			else {
				require( dirname( __FILE__ ) . $template_path . 'slider-' . $substyle . '.php' );
			}

			$out = ob_get_contents();

			ob_end_clean();

			wp_reset_query();
			wp_reset_postdata(); // Restore global post data
			if ( is_multisite() ) {
				restore_current_blog(); // Restore current blog
			}
			return $out;
		else :
			return esc_attr__( 'No posts found matching your query. Please try again by changing post parameters.', 'newsplus' );
		endif;
	}

	public static function insert_posts( $atts ) {
		extract( shortcode_atts( array(

			// Deprecated params
			'query_type'		=> 'category',
			'cats'				=> null,
			'posts'				=> null,
			'pages'				=> null,
			'tags'				=> null,
			'display_style'		=> '',
			'hide_meta'			=> 'false',

			// New WP Query params
			'author_name' 			=> null,
			'author__in' 			=> null,
			'cat' 					=> null,
			'category_name' 		=> null,
			'tag' 					=> null,
			'tag_id' 				=> null,
			'taxonomy' 				=> null,
			'terms'					=> null,
			'p' 					=> null,
			'name' 					=> null,
			'page_id' 				=> null,
			'pagename' 				=> null,
			'post__in' 				=> null,
			'post__not_in' 			=> null,
			'post_type' 			=> null,
			'post_status' 			=> 'publish',
			'num' 					=> 5,
			'offset' 				=> 0,
			'ignore_sticky_posts' 	=> false,
			'order' 				=> 'DESC',
			'orderby' 				=> 'date',
			'year' 					=> null,
			'monthnum' 				=> null,
			'w' 					=> null,
			'day'					=> null,
			'meta_key' 				=> null,
			'meta_value'			=> null,
			'meta_value_num' 		=> null,
			'meta_compare' 			=> '=',
			's' 					=> null,
			'blog_id'				=> null,

			// Display specific
			'template'			=> 'grid', // list, gallery
			'col'				=> 1, // for grid and gallery
			'excerpt_length'	=> '140',
			'use_word_length'	=> 'false',
			'use_short_title'	=> false,
			'ext_link'			=> false,
			'hide_image'		=> 'false',
			'hide_video'		=> 'false',
			'enable_masonry'	=> false,
			'imgwidth'			=> '',
			'imgheight'			=> '',
			'imgcrop'			=> false,
			'imgupscale'		=> 'true',
			'imgquality'		=> '80',
			'enable_schema'		=> false,
			'date_format'		=> get_option( 'date_format' ),
			'hide_cats' 		=> false,
			'hide_reviews'		=> false,
			'show_cats'			=> false, // For list small
			'show_reviews'		=> false, // For list small
			'hide_date' 		=> false,
			'hide_author' 		=> false,
			'hide_excerpt' 		=> false,
			'show_excerpt'		=> false, // For list small
			'show_avatar' 		=> false,
			'hide_views' 		=> false,
			'hide_comments' 	=> false,
			'readmore' 			=> false,
			'readmore_text' 	=> esc_attr__( 'Read more', 'newsplus' ),
			'publisher_logo' 	=> get_template_directory_uri() . '/images/logo.png',
			'excerpt_length' 	=> 140,
			'htag'				=> 'h2',
			'hsize'				=> '',
			'ptag' 				=> 'p',
			'psize' 			=> '',
			'video_custom_field' => 'pf_video',
			'split'				=> '33-67',
			'lightbox'			=> false, // Used for gallery display type
			'show_title'		=> false, // Gallery specific
			'xclass'			=> false,
			'ptclass'			=> false,
			'overlay_style'		=> 'default',
			'sharing'			=> false,
			'share_btns'		=> ''
		), $atts ) );

		// Sanitize WP Query args
		$author__in 				= $author__in ? explode( ',', $author__in ) : null;
		$post__in 					= $post__in ? explode( ',', $post__in ) : ( $posts ? explode( ',', $posts ) : ( $pages ? explode( ',', $pages ) : null ) );
		$post__not_in 				= $post__not_in ? explode( ',', $post__not_in ) : null;
		$terms 						= ( $terms ) ? explode( ',', $terms ) : null;
		$post_type					= ( $post_type ) ? explode( ',', $post_type ) : null;
		$taxonomy					= ( $taxonomy ) ? explode( ',', $taxonomy ) : null;
		$tax_query 					= null;

		if ( $taxonomy && $terms ) {
			$tax_query = array( 'relation' => 'OR' );

			if ( is_array( $taxonomy ) ) {
				foreach( $taxonomy as $tax ) {
					$tax_query[] = array(
						'taxonomy'	=> $tax,
						'field'		=> 'slug',
						'terms'		=> $terms,
						'operator' 	=> 'IN' // Allowed values AND, IN, NOT IN
					);
				}
			}
		}

		// Allowed args in WP Query
		$custom_args = array(
			'author_name' 			=> $author_name,
			'author__in' 			=> $author__in,
			'cat' 					=> $cat ? $cat : ( $cats ? $cats : null ),
			'category_name' 		=> $category_name,
			'tag' 					=> $tag ? $tag : ( $tags ? $tags : null ),
			'tag_id' 				=> $tag_id,
			'tax_query' 			=> $tax_query,
			'p' 					=> $p,
			'name' 					=> $name,
			'page_id' 				=> $page_id,
			'pagename' 				=> $pagename,
			'post__in' 				=> $post__in,
			'post__not_in' 			=> $post__not_in,
			'post_type' 			=> $post_type,
			'post_status' 			=> $post_status,
			'posts_per_page' 		=> $num,
			'offset' 				=> $offset,
			'ignore_sticky_posts' 	=> $ignore_sticky_posts,
			'order' 				=> $order,
			'orderby' 				=> $orderby,
			'year' 					=> $year,
			'monthnum' 				=> $monthnum,
			'w' 					=> $w,
			'day'					=> $day,
			'meta_key' 				=> $meta_key,
			'meta_value'			=> $meta_value,
			'meta_value_num' 		=> $meta_value_num,
			's' 					=> $s,
		);

		$new_args = array();

		// Set args which are provided by user
		foreach ( $custom_args as $key => $value ) {
			if ( isset( $value ) )
				$new_args[ $key ] = $value;
		}

		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		// Get master class of King Composer
		$master_class = apply_filters( 'kc-el-class', $atts );

		$custom_query = new WP_Query( $new_args );

		if ( $custom_query->have_posts() ) :

			if ( $display_style == 'two-col' ) {
				$col = 2;
			}
			elseif ( $display_style == 'three-col' ) {
				$col = 3;
			}
			elseif ( $display_style == 'four-col' ) {
				$col = 4;
			}

			if ( $display_style == 'one-col' || $display_style == 'two-col' || $display_style == 'three-col' || $display_style == 'four-col' ) {
				$template = 'grid';
			}

			elseif ( $display_style == 'list-big' ) {
				$template = 'list-big';
			}

			elseif ( $display_style == 'list-small' || $display_style == 'list-plain' ) {
				$template = 'list-small';
			}

			elseif ( $display_style == 'gallery' ) {
				$template = 'gallery';
			}

			$template = ( '' == $template ) ? 'grid' : $template;

			ob_start();

			$template_path = apply_filters( 'newsplus_template_path',  '/newsplus-post-templates/' );

			if ( locate_template( $template_path . esc_attr( $template ) . '.php' ) ) {
				require( get_stylesheet_directory() . $template_path . esc_attr( $template ) . '.php' );
			}

			else {
				require( dirname( __FILE__ ) . $template_path . esc_attr( $template ) . '.php' );
			}

			$out = ob_get_contents();

			ob_end_clean();

			wp_reset_query();
			wp_reset_postdata(); // Restore global post data
			if ( is_multisite() ) {
				restore_current_blog(); // Restore current blog
			}
		return $out;

		else :
			return esc_attr__( 'No posts found matching your query. Please try again by changing post parameters.', 'newsplus' );
		endif;
	}

	public static function newsplus_sidebar( $atts ) {
		extract( shortcode_atts( array(
			'id'				=> 'default-sidebar',
			'xclass'			=> false
		), $atts ) );

		$id = sanitize_title($id);

		ob_start();
		printf( '<div class="widget-area%s">',
			( $xclass ) ? ' ' . esc_attr( $xclass ) : ''
		);

		if ( is_active_sidebar( $id ) ) :
			dynamic_sidebar( $id );
		endif;

		printf( '</div>' );

		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}

	public static function newsplus_grid_list( $atts ) {
		extract( shortcode_atts( array(

			// Deprecated params
			'query_type'		=> 'category',
			'cats'				=> null,
			'posts'				=> null,
			'pages'				=> null,
			'tags'				=> null,
			'hide_meta'			=> 'false',

			// New WP Query params
			'author_name' 			=> null,
			'author__in' 			=> null,
			'cat' 					=> null,
			'category_name' 		=> null,
			'tag' 					=> null,
			'tag_id' 				=> null,
			'taxonomy' 				=> null,
			'terms'					=> null,
			'p' 					=> null,
			'name' 					=> null,
			'page_id' 				=> null,
			'pagename' 				=> null,
			'post__in' 				=> null,
			'post__not_in' 			=> null,
			'post_type' 			=> null,
			'post_status' 			=> 'publish',
			'num' 					=> 5,
			'offset' 				=> 0,
			'ignore_sticky_posts' 	=> false,
			'order' 				=> 'DESC',
			'orderby' 				=> 'date',
			'year' 					=> null,
			'monthnum' 				=> null,
			'w' 					=> null,
			'day'					=> null,
			'meta_key' 				=> null,
			'meta_value'			=> null,
			'meta_value_num' 		=> null,
			'meta_compare' 			=> '=',
			's' 					=> null,
			'blog_id'				=> null,

			// Display Specific
			'display_style'		=> 's1',
			'use_word_length'	=> 'false',
			'use_short_title'	=> false,
			'ext_link'			=> false,
			'hide_excerpt'		=> 'false',
			'imgquality'		=> '',
			'viewport_width'	=> 1192,
			'gutter'			=> 4,
			'aspect_ratio'		=> .75,
			'featured_label'	=> false,
			'xclass'			=> false,
			'htag'				=> 'h2',
			'hsize'				=> '',
			'htag_sub'			=> 'h2',
			'hsize_sub'			=> '14',
			'enable_schema'		=> false,
			'date_format'		=> get_option( 'date_format' ),
			'hide_cats' 		=> false,
			'hide_reviews'		=> false,
			'hide_date' 		=> false,
			'hide_author' 		=> false,
			'hide_excerpt' 		=> false,
			'show_avatar' 		=> false,
			'hide_views' 		=> false,
			'hide_comments' 	=> false,
			'readmore' 			=> false,

			// For sub tiles
			'hide_cats_sub' 		=> 'true',
			'hide_reviews_sub'		=> 'true',
			'hide_date_sub' 		=> false,
			'hide_author_sub' 		=> false,
			'hide_excerpt_sub' 		=> 'true',
			'show_avatar_sub' 		=> false,
			'hide_views_sub' 		=> 'true',
			'hide_comments_sub' 	=> 'true',
			'readmore_sub' 			=> false,
			'readmore_text' 		=> false,
			'publisher_logo' 	=> get_template_directory_uri() . '/images/logo.png',
			'excerpt_length' 	=> 140,
			'ptag' 				=> 'p',
			'psize' 			=> '',
			'xclass'			=> false,
			'sharing'			=> false,
			'share_btns'		=> ''
		), $atts ) );

		// Sanitize WP Query args
		$author__in 				= $author__in ? explode( ',', $author__in ) : null;
		$post__in 					= $post__in ? explode( ',', $post__in ) : ( $posts ? explode( ',', $posts ) : ( $pages ? explode( ',', $pages ) : null ) );
		$post__not_in 				= $post__not_in ? explode( ',', $post__not_in ) : null;
		$terms 						= ( $terms ) ? explode( ',', $terms ) : null;
		$post_type					= ( $post_type ) ? explode( ',', $post_type ) : null;
		$taxonomy					= ( $taxonomy ) ? explode( ',', $taxonomy ) : null;
		$tax_query 					= null;

		if ( $taxonomy && $terms ) {
			$tax_query = array( 'relation' => 'OR' );

			if ( is_array( $taxonomy ) ) {
				foreach( $taxonomy as $tax ) {
					$tax_query[] = array(
						'taxonomy'	=> $tax,
						'field'		=> 'slug',
						'terms'		=> $terms,
						'operator' 	=> 'IN' // Allowed values AND, IN, NOT IN
					);
				}
			}
		}

		// Allowed args in WP Query
		$custom_args = array(
			'author_name' 			=> $author_name,
			'author__in' 			=> $author__in,
			'cat' 					=> $cat ? $cat : ( $cats ? $cats : null ),
			'category_name' 		=> $category_name,
			'tag' 					=> $tag ? $tag : ( $tags ? $tags : null ),
			'tag_id' 				=> $tag_id,
			'tax_query' 			=> $tax_query,
			'p' 					=> $p,
			'name' 					=> $name,
			'page_id' 				=> $page_id,
			'pagename' 				=> $pagename,
			'post__in' 				=> $post__in,
			'post__not_in' 			=> $post__not_in,
			'post_type' 			=> $post_type,
			'post_status' 			=> $post_status,
			'posts_per_page' 		=> $num,
			'offset' 				=> $offset,
			'ignore_sticky_posts' 	=> $ignore_sticky_posts,
			'order' 				=> $order,
			'orderby' 				=> $orderby,
			'year' 					=> $year,
			'monthnum' 				=> $monthnum,
			'w' 					=> $w,
			'day'					=> $day,
			'meta_key' 				=> $meta_key,
			'meta_value'			=> $meta_value,
			'meta_value_num' 		=> $meta_value_num,
			's' 					=> $s,
		);

		$new_args = array();

		// Set args which are provided by user
		foreach ( $custom_args as $key => $value ) {
			if ( isset( $value ) )
				$new_args[ $key ] = $value;
		}

		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		// Get master class of King Composer
		$master_class = apply_filters( 'kc-el-class', $atts );

		$custom_query = new WP_Query( $new_args );

		if ( $custom_query->have_posts() ) :
			$allowed_styles = array( 's1', 's2', 's3', 's4', 's5', 's6' );

			if ( ! in_array( $display_style, $allowed_styles ) ) {
				$display_style = 's1';
			}
			ob_start();

			$template_path = apply_filters( 'newsplus_template_path',  '/newsplus-post-templates/' );

			if ( locate_template( $template_path . 'tile.php' ) ) {
				require( get_stylesheet_directory() . $template_path . 'tile.php' );
			}

			else {
				require( dirname( __FILE__ ) . $template_path . 'tile.php' );
			}

			$out = ob_get_contents();

			ob_end_clean();


			wp_reset_query();
			wp_reset_postdata(); // Restore global post data
			if ( is_multisite() ) {
				restore_current_blog(); // Restore current blog
			}
		return $out;
		endif;
	}

	public static function newsplus_news_ticker( $atts ) {
		extract( shortcode_atts( array(
			// Deprecated params
			'query_type'		=> 'category',
			'cats'				=> null,
			'posts'				=> null,
			'pages'				=> null,
			'tags'				=> null,

			// New WP Query params
			'author_name' 			=> null,
			'author__in' 			=> null,
			'cat' 					=> null,
			'category_name' 		=> null,
			'tag' 					=> null,
			'tag_id' 				=> null,
			'taxonomy' 				=> null,
			'terms'					=> null,
			'p' 					=> null,
			'name' 					=> null,
			'page_id' 				=> null,
			'pagename' 				=> null,
			'post__in' 				=> null,
			'post__not_in' 			=> null,
			'post_type' 			=> null,
			'post_status' 			=> 'publish',
			'num' 					=> 5,
			'offset' 				=> 0,
			'ignore_sticky_posts' 	=> false,
			'order' 				=> 'DESC',
			'orderby' 				=> 'date',
			'year' 					=> null,
			'monthnum' 				=> null,
			'w' 					=> null,
			'day'					=> null,
			'meta_key' 				=> null,
			'meta_value'			=> null,
			'meta_value_num' 		=> null,
			'meta_compare' 			=> '=',
			's' 					=> null,
			'blog_id'				=> null,

			// Dislay specific
			'title_length'		=> '10',
			'ticker_label'		=> __( 'Breaking News', 'newsplus' ),
			'use_short_title'	=> false,
			'ext_link'			=> false,
			'duration'			=> 15000,
			'xclass'			=> false
		), $atts ) );

		// Sanitize WP Query args
		$author__in 				= $author__in ? explode( ',', $author__in ) : null;
		$post__in 					= $post__in ? explode( ',', $post__in ) : ( $posts ? explode( ',', $posts ) : ( $pages ? explode( ',', $pages ) : null ) );
		$post__not_in 				= $post__not_in ? explode( ',', $post__not_in ) : null;
		$terms 						= ( $terms ) ? explode( ',', $terms ) : null;
		$post_type					= ( $post_type ) ? explode( ',', $post_type ) : null;
		$taxonomy					= ( $taxonomy ) ? explode( ',', $taxonomy ) : null;
		$tax_query 					= null;

		if ( $taxonomy && $terms ) {
			$tax_query = array( 'relation' => 'OR' );

			if ( is_array( $taxonomy ) ) {
				foreach( $taxonomy as $tax ) {
					$tax_query[] = array(
						'taxonomy'	=> $tax,
						'field'		=> 'slug',
						'terms'		=> $terms,
						'operator' 	=> 'IN' // Allowed values AND, IN, NOT IN
					);
				}
			}
		}

		// Allowed args in WP Query
		$custom_args = array(
			'author_name' 			=> $author_name,
			'author__in' 			=> $author__in,
			'cat' 					=> $cat ? $cat : ( $cats ? $cats : null ),
			'category_name' 		=> $category_name,
			'tag' 					=> $tag ? $tag : ( $tags ? $tags : null ),
			'tag_id' 				=> $tag_id,
			'tax_query' 			=> $tax_query,
			'p' 					=> $p,
			'name' 					=> $name,
			'page_id' 				=> $page_id,
			'pagename' 				=> $pagename,
			'post__in' 				=> $post__in,
			'post__not_in' 			=> $post__not_in,
			'post_type' 			=> $post_type,
			'post_status' 			=> $post_status,
			'posts_per_page' 		=> $num,
			'offset' 				=> $offset,
			'ignore_sticky_posts' 	=> $ignore_sticky_posts,
			'order' 				=> $order,
			'orderby' 				=> $orderby,
			'year' 					=> $year,
			'monthnum' 				=> $monthnum,
			'w' 					=> $w,
			'day'					=> $day,
			'meta_key' 				=> $meta_key,
			'meta_value'			=> $meta_value,
			'meta_value_num' 		=> $meta_value_num,
			's' 					=> $s,
		);

		$new_args = array();

		// Set args which are provided by user
		foreach ( $custom_args as $key => $value ) {
			if ( isset( $value ) )
				$new_args[ $key ] = $value;
		}

		if ( is_multisite() ) {
			switch_to_blog( $blog_id );
		}

		// Get master class of King Composer
		$master_class = apply_filters( 'kc-el-class', $atts );

		$custom_query = new WP_Query( $new_args );

		if ( $custom_query->have_posts() ) :

			$out = sprintf( '<div class="np-news-ticker-container%s%s">%s<div class="np-news-ticker" data-duration="%s">',
					isset( $master_class ) && '' !== $master_class ? ' ' . implode( ' ', $master_class ) : '',
					$xclass ? ' ' . esc_attr( $xclass ) : '',
					$ticker_label ? '<div class="ticker-label">' . esc_attr( $ticker_label ) . '</div>' : '',
					(int)$duration
			);

			while ( $custom_query->have_posts() ) :
				$custom_query->the_post();
				global $multipage;
				$multipage = 0;

				$permalink = get_permalink();
				$title = self::newsplus_short_by_word( get_the_title(), $title_length );

				$postID = get_the_ID();

				$post_class_obj = get_post_class( $postID );
				$post_classes = '';

				if ( isset( $post_class_obj ) && is_array( $post_class_obj ) ) {
					foreach( $post_class_obj as $post_class ) {
						$post_classes .= ' ' . $post_class;
					}
				}

				$format = apply_filters( 'news_ticker_list_output', '<span><a href="%1$s" title="%2$s">%2$s</a></span>' );
				$out .= sprintf ( $format, $permalink, $title );

			endwhile;
			$out .= '</div></div>';
			wp_reset_query();
			wp_reset_postdata(); // Restore global post data
			if ( is_multisite() ) {
				restore_current_blog(); // Restore current blog
			}
		return $out;
		endif;
	}

	public static function newsplus_recipe( $atts, $content = null ) {
		extract( shortcode_atts( array(
			// Image specific
			'img_src'			=> 'featured', //media_lib, ext
			'img_lib'			=> '',
			'img_ext'			=> '',
			'img_alt'			=> '',
			'img_caption'		=> '',
			'imgwidth'			=> '',
			'imgheight'			=> '',
			'imgcrop'			=> '',
			'imgquality'		=> '',
			'img_align'			=> 'none',
			'hide_img'			=> false,
			'json_ld'			=> false, // Whether to include JSON LD microdata

			// Recipe name and summary
			'name_src'			=> 'post_title', // custom
			'name_txt'			=> '',
			'hide_name'			=> false,
			'summary'			=> '',
			'hide_summary'		=> false,
			'author_src'		=> 'post_author',
			'author_name'		=> '',
			'author_url'		=> '',
			'hide_author'		=> false,
			'hide_date'			=> false,

			// Recipe meta
			'prep_time'			=> '', // in minutes
			'cook_time'			=> '', // in minutes
			'cooking_method'	=> '',
			'recipe_category'	=> '',
			'recipe_category_other'	=> '',
			'recipe_cuisine'	=> '',
			'recipe_cuisine_other'	=> '',
			'ingredients'		=> '', // base64 and json encoded data
			'ing_heading'		=> __( 'Ingredients', 'newsplus' ),
			'method_heading'		=> __( 'Method', 'newsplus' ),
			'enable_numbering'  => 'true',
			'other_notes'		=> '',

			// Nutrition facts
			'recipe_yield'		=> '',
			'serving_size'		=> '',
			'calories'			=> '',
			'suitable_for_diet'	=> '',
			'nutrition'			=> '', // base64 and json encoded data
			'nutri_heading'		=> __( 'Nutrition Facts', 'newsplus' ),
			'hide_nutrition'  	=> false,

			// Nutrients
			'total_fat'			=> '',
			'saturated_fat'		=> '',
			'trans_fat'			=> '',
			'polyunsat_fat'		=> '',
			'monounsat_fat'		=> '',
			'cholesterol'		=> '',
			'sodium'			=> '',
			'carbohydrate'		=> '',
			'fiber'				=> '',
			'sugar'				=> '',
			'added_sugar'		=> '',
			'sugar_alcohal'		=> '',
			'protein'			=> '',
			'vitamin_d'			=> '',
			'calcium'			=> '',
			'iron'				=> '',
			'potassium'			=> '',
			'vitamin_a'			=> '',
			'vitamin_c'			=> '',
			'vitamin_e'			=> '',
			'vitamin_k'			=> '',
			'vitamin_b1'		=> '',
			'vitamin_b2'		=> '',
			'vitamin_b3'		=> '',
			'vitamin_b6'		=> '',
			'folate'			=> '',
			'vitamin_b12'		=> '',
			'biotin'			=> '',
			'choline'			=> '',
			'vitamin_b5'		=> '',
			'phosphorus'		=> '',
			'iodine'			=> '',
			'magnesium'			=> '',
			'zinc'				=> '',
			'selenium'			=> '',
			'copper'			=> '',
			'manganese'			=> '',
			'chromium'			=> '',
			'molybdenum'		=> '',
			'chloride'			=> ''
		), $atts ) );

		ob_start();

		$template_path = apply_filters( 'newsplus_template_path', '/newsplus-post-templates/' );

		if ( locate_template( $template_path . 'recipe.php' ) ) {
			require( get_stylesheet_directory() . $template_path . 'recipe.php' );
		}

		else {
			require( dirname( __FILE__ ) . $template_path . 'recipe.php' );
		}

		$out = ob_get_contents();

		ob_end_clean();

		return $out;

	}

	public static function newsplus_recipe_method( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'method_title' => '',
			'xclass' => false,
		), $atts ) );

		$out = '';
		$num_box = '<span class="step-num step-%1$s">%1$s</span>';

		if ( isset( $GLOBALS['np_recipe_method_count'] ) )
			$GLOBALS['np_recipe_method_count']++;
		else
			$GLOBALS['np_recipe_method_count'] = 1;

		if ( '' !== $method_title ) {
			$out .= '<p class="inst-subhead"><strong>' . esc_attr( $method_title ) . '</strong></p>';
		}

		$out .= sprintf( '<div id="recipe_step_%s" class="recipe-instruction%s" itemprop="recipeInstructions">%s%s</div>',
				$GLOBALS['np_recipe_method_count'],
				$xclass ? ' ' . esc_attr( $xclass ) : '',
				sprintf( $num_box, number_format_i18n( $GLOBALS['np_recipe_method_count'] ) ),
				self::newsplus_return_clean( $content )
			);

		return $out;
	}

// Return parameters for BFI image resize
	public static function newsplus_image( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'src'				=> '', //media_lib, ext
			'alt'				=> '',
			'caption'			=> '',
			'align'				=> 'none',
			'imgwidth'			=> '',
			'imgheight'			=> '',
			'imgcrop'			=> '',
			'imgquality'		=> '80',
			'imgcolor'			=> '',
			'imggrayscale'		=> '',
			'linkrel'			=> '', // prettyPhoto
			'linkto'			=> 'none', // media, custom,
			'custom_url'		=> '',
			'imgtitle'			=> '',
			'target'			=> false,
			'xclass'			=> ''
		), $atts ) );

		$img = self::newsplus_image_resize( $src, $imgwidth, $imgheight, $imgcrop, $imgquality, $imgcolor, $imggrayscale );
		$img_html = '';
		$link_open = $link_close = $div_open = $div_close = $caption_html = '';

		if ( isset( $img ) ) {

			$img_html = sprintf( '<img%s src="%s"%s />',
				( 'none' !== $align && '' == $caption ) ? ' class="align' . esc_attr( $align ) . '"' : '',
				$img,
				$alt ? ' alt="' . esc_attr( $alt ) . '"' : ''
			);

			if ( '' !== $caption ) {
				$div_open = sprintf( '<div class="wp-caption%s%s">',
					( 'none' !== $align ) ? ' align' . esc_attr( $align ) : '',
					$xclass ? ' ' . esc_attr( $xclass ) : ''
				);

				$div_close = '</div>';

				$caption_html = '<p class="wp-caption-text">' . $caption . '</p>';
			}

			if ( 'none' !== $linkto ) {
				$link_open = sprintf( '<a href="%s"%s%s%s>',
					( 'media' == $linkto ) ? esc_url( $src ) : ( '' !== $custom_url ? esc_url( $custom_url ) : '' ),
					( '' !== $linkrel ) ? ' rel="' . esc_attr( $linkrel ) . '"' : '',
					( '' != $imgtitle ) ? ' title="' . esc_attr ( $imgtitle ) . '"' : '',
					$target ? ' target="_blank"' : ''
				);

				$link_close = '</a>';
			}

			return $div_open . $link_open . $img_html . $link_close . $caption_html . $div_close;

		} //img
	}

	public static function newsplus_theme_url( $atts ) {
		extract( shortcode_atts( array(
			'child' => false
		), $atts ) );

		return ( $child ) ? get_stylesheet_directory_uri() : get_template_directory_uri();
	}


// Title Shortcode
	public static function newsplus_title( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'htag' 				=> 'h2',
			'hsize'				=> '14',
			'style'				=> 'plain',
			'margin_btm'		=> 'theme_defined',
			'color'				=> '',
			'text'				=> '',
			'link'				=> '',
			'link_title'		=> '',
			'target'			=> false,
			'sub_text'			=> '',
			'sub_link'			=> '',
			'sub_link_title'	=> '',
			'sub_target'		=> false,
			'round_corner'		=> false,
			'xclass'			=> ''
		), $atts ) );

		// Get master class of King Composer
		$master_class = apply_filters( 'kc-el-class', $atts );

		$main = $text ? '<span class="main-text">' . esc_attr( $text ) . '</span>' : '';
		$sub = $sub_text ? '<span class="sub-text">' . esc_attr( $sub_text ) . '</span>' : '';

		if ( $link && $text ) {
			$main = sprintf( '<a class="main-text" href="%s"%s%s>%s</a>',
						esc_url( $link ),
						$target ? ' target="_blank"' : '',
						$link_title ? ' title="' . esc_attr( $link_title ) . '"' : '',
						esc_attr( $text )
					);
		}

		if ( $sub_link && $sub_text ) {
			$sub = sprintf( '<a class="sub-text" href="%s"%s%s>%s</a>',
						esc_url( $sub_link ),
						$sub_target ? ' target="_blank"' : '',
						$sub_link_title ? ' title="' . esc_attr( $sub_link_title ) . '"' : '',
						esc_attr( $sub_text )
					);
		}

		return sprintf( '<%1$s class="newsplus-title%2$s%3$s%4$s%5$s%6$s%7$s%8$s">%9$s%10$s</%1$s>',
					$htag,
					' st-' . esc_attr( $style ),
					$color ? ' ' . esc_attr( $color ) : '',
					$hsize ? ' fs-' . esc_attr( $hsize ) : '',
					$round_corner ? ' rounded' : '',
					isset( $master_class ) && '' !== $master_class ? ' ' . implode( ' ', $master_class ) : '',
					$xclass ? ' ' . esc_attr( $xclass ) : '',
					'theme_defined' !== $margin_btm ? ' btm-' . esc_attr( $margin_btm ) : '',
					$main,
					$sub
				);
	}

// Register and initialize short codes
	public static function newsplus_add_shortcodes() {

	}

public static function newsplus_kc_map_shortcodes() {

	// Add shortcode map
	if ( function_exists( 'kc_add_map' ) ) {
		global $sitepress;
		if ( isset( $sitepress ) ) {
			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
		}
		// Categories array
		$cat_arr = array();
		$categories = get_categories();
		foreach( $categories as $category ){
		  $cat_arr[ $category->term_id ] = $category->name;
		}

		// Post types
		$post_type_arr = array();
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			if ( ! in_array( $post_type, array( 'revision', 'attachment', 'nav_menu_item' ) ) ) {
				$post_type_arr[ $post_type ] = $post_type;
			}
		}

		// Find taxonomies for use in dropdown
		$taxonomies = get_taxonomies( array( 'public' => true ) );
		$tax_arr = array();
		$terms_arr = array();
		foreach ( $taxonomies as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy );

			// Get terms for each taxonomy
			$term_arr = array();
			$terms = get_terms( array(
				'taxonomy' => $taxonomy,
				'hide_empty' => true,
				'number' => apply_filters( 'newsplus_cat_limit', 999 )
			) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
				foreach ( $terms as $term ) {
					$term_arr[ $term->slug ] = $term->name;
				}
			}

			// Store taxonomies in array
			if ( ! in_array( $taxonomy, array( 'nav_menu', 'link_category', 'post_format', 'product_type', 'product_shipping_class' ) ) ) {
				$tax_arr[ $taxonomy ] = $tax->labels->name;
				$terms_arr[ $tax->labels->name ] = $term_arr;
			}
		}

		if ( isset( $sitepress ) ) {
			add_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
		}

		/**
		 * Query parameters
		 * reusable for various post shortcodes
		 */

		// Query params
		$wp_query_params = array(

			array(
				'type' => 'multiple',
				'label' => __( 'Show data from', 'newsplus' ),
				'name' => 'post_type',
				'options' => $post_type_arr,
				'description' => __( 'Select post types. Use Ctrl + Select or Command + select for mutiple selection.', 'newsplus' )
			),

			array(
				'label' => __( 'Number of Posts', 'newsplus' ),
				'name' => 'num',
				'type' => 'number_slider',
				'options' => array(
					'min' => 1,
					'max' => 100,
					'unit' => '',
					'ratio' => '1',
					'show_input' => true
				),
				'value' => '5',
				'description' => __( 'Provide number of posts to show. E.g. 10', 'newsplus' )

			),

			array(
				'type' => 'multiple',
				'label' => __( 'Taxonomy', 'newsplus' ),
				'name' => 'taxonomy',
				'options' => $tax_arr,
				'description' => __( 'Select taxonomy', 'newsplus' )
			),

			array(
				'name'     => 'terms',
				'label'    => 'Terms',
				'type'     => 'multiple',
				'options'  => $terms_arr,
				'description' => __( 'Select terms', 'newsplus' )
			),

			array(
				'type' => 'autocomplete',
				'label' => __( 'Filter by Post, page or Custom post types', 'newsplus' ),
				'name' => 'post__in',
				'value' => '',
				'description' => __( 'Type keywords to search for a post or page. Then click on result to include a post or page.', 'newsplus' ),
				'admin_label' => true,
			),

			array(
				'type' => 'text',
				'label' => __( 'Filter by Author IDs', 'newsplus' ),
				'name' => 'author__in',
				'value' => '',
				'description' => __( 'Provide numeric IDs of Authors, separated by comma. E.g. 2,6', 'newsplus' )
			),

			array(
				'label' => __( 'Offset', 'newsplus' ),
				'name' => 'offset',
				'type' => 'number_slider',
				'options' => array(
					'min' => 0,
					'max' => 100,
					'unit' => '',
					'ratio' => '1',
					'show_input' => true
				),
				'value' => 0,
				'description' => __( 'Provide an offset number. E.g. 2. Offset is used to skip a particular number of posts from loop.', 'newsplus' )
			),

			array(
				'type' => 'dropdown',
				'label' => __( 'Order', 'newsplus' ),
				'name' => 'order',
				'options' => array(
						'DESC' => esc_attr__( 'Descending', 'newsplus' ),
						'ASC' => esc_attr__( 'Ascending', 'newsplus' )
				),
				'value' => 'DESC',
				'description' => __( 'Select posts order.', 'newsplus' )
			),

			array(
				'type' => 'dropdown',
				'label' => __( 'Order by', 'newsplus' ),
				'name' => 'orderby',
				'options' => array(
						'none' => esc_attr__( 'None', 'newsplus' ),
						'ID' => esc_attr__( 'ID', 'newsplus' ),
						'author' => esc_attr__( 'Author', 'newsplus' ),
						'title' => esc_attr__( 'Title', 'newsplus' ),
						'name' => esc_attr__( 'Name', 'newsplus' ),
						'type' => esc_attr__( 'Post Type', 'newsplus' ),
						'date' => esc_attr__( 'Date', 'newsplus' ),
						'modified' => esc_attr__( 'Last Modified', 'newsplus' ),
						'parent' => esc_attr__( 'Parent ID', 'newsplus' ),
						'rand' => esc_attr__( 'Random', 'newsplus' ),
						'comment_count' => esc_attr__( 'Comment Count', 'newsplus' ),
						'menu_order' => esc_attr__( 'Menu Order', 'newsplus' ),
						'post__in' => esc_attr__( 'Post In', 'newsplus' ),
				),
				'value' => 'date',
				'description' => __( 'Select posts orderby criteria.', 'newsplus' )
			),

			array(
				'name'        => 'ignore_sticky_posts',
				'label'       => 'Sticky Posts',
				'type'        => 'checkbox',
				'description' => __( 'Check to ignore sticky posts', 'newsplus' ),
				'options'     => array( 'true' => __( 'Ignore sticky posts', 'newsplus' ) )
			)

		);

		/**
		 * Content parameters
		 * reusable in different post shortcodes
		 */

		$content_params =  array(

			array(
				'label' => __( 'Heading tag', 'newsplus' ),
				'name' => 'htag',
				'type' => 'dropdown',
				'options' => array(
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6'
				),
				'value' => 'h2',
				'description' => __( 'Select heading tag for post title.', 'newsplus' )
			),

			array(
				'label' => __( 'Heading size', 'newsplus' ),
				'name' => 'hsize',
				'type' => 'dropdown',
				'options' => array(
					''   => _x( 'Select', 'dropdown label', 'newsplus' ),
					'12' => '12px',
					'14' => '14px',
					'16' => '16px',
					'18' => '18px',
					'20' => '20px',
					'24' => '24px',
					'34' => '34px'
				),
				'value' => '',
				'description' => __( 'Select heading font size for post title.', 'newsplus' )
			),

			array(
				'name' => 'hide_excerpt',
				'label' => __( 'Hide post excerpt', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide post excerpt', 'newsplus' )
				),
				'description' => __( 'Check to hide post excerpt in post module.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'template',
					'hide_when' => 'list-small'
				)
			),

			array(
				'label' => __( 'Excerpt tag', 'newsplus' ),
				'name' => 'ptag',
				'type' => 'dropdown',
				'options' => array(
					'p' => 'p',
					'span' => 'span',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6'
				),
				'value' => 'p',
				'description' => __( 'Select tag for post excerpt.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'hide_excerpt',
					'hide_when' => 'true'
				)
			),

			array(
				'label' => __( 'Excerpt Size', 'newsplus' ),
				'name' => 'psize',
				'type' => 'dropdown',
				'options' => array(
					''   => _x( 'Select', 'dropdown label', 'newsplus' ),
					'12' => '12px',
					'13' => '13px',
					'14' => '14px',
					'16' => '16px',
					'18' => '18px'
				),
				'value' => '',
				'description' => __( 'Select font size for post excerpt.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'hide_excerpt',
					'hide_when' => 'true'
				)
			),

			array(
				'name' => 'use_word_length',
				'label' => __( 'Use word length', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Use word length', 'newsplus' )
				),
				'description' => __( 'Check to enable trimming in word length instead of character length.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'hide_excerpt',
					'hide_when' => 'true'
				)
			),

			array(
				'label' => __( 'Excerpt length', 'newsplus' ),
				'name' => 'excerpt_length',
				'type' => 'number_slider',
				'options' => array(
					'min' => 1,
					'max' => 500,
					'unit' => '',
					'ratio' => '1',
					'show_input' => true
				),
				'value' => '140',
				'description' => __( 'Provide excerpt length in characters or words. If enabled word length, provide length in words. E.g. 20', 'newsplus' ),
				'relation' => array(
					'parent'    => 'hide_excerpt',
					'hide_when' => 'true'
				)
			),

			array(
				'name' => 'readmore',
				'label' => __( 'Show readmore link', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Show readmore link', 'newsplus' )
				),
				//'value' => 'true', // remove this if you do not need a default content
				'description' => __( 'Check to show a readmore link.', 'newsplus' )
			),

			array(
				'label' => __( 'Readmore text', 'newsplus' ),
				'name' => 'readmore_text',
				'type' => 'text',
				'value' => 'Read more',
				'description' => __( 'The text for readmore button.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'readmore',
					'show_when' => 'true'
				)
			),

			array(
				'name' => 'use_short_title',
				'label' => __( 'Use short title', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Enable short titles support', 'newsplus' )
				),
				'description' => __( 'Check to enable short titles. The posts must have "np_short_title" custom field with the value as short title.', 'newsplus' )
			),

			array(
				'name' => 'ext_link',
				'label' => __( 'Use external links', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Enable external links', 'newsplus' )
				),
				'description' => __( 'Check to enable external link on post title and thumbnail. The posts must have custom field "np_custom_link" with value as external link.', 'newsplus' )
			)
		);

		$content_params_sub = array(
			array(
				'label' => __( 'Heading tag for sub tiles', 'newsplus' ),
				'name' => 'htag_sub',
				'type' => 'dropdown',
				'options' => array(
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'h6' => 'h6'
				),
				'value' => 'h2',
				'description' => __( 'Select heading tag for post title in sub tiles.', 'newsplus' )
			),

			array(
				'label' => __( 'Heading size for sub tiles', 'newsplus' ),
				'name' => 'hsize_sub',
				'type' => 'dropdown',
				'options' => array(
					'12' => '12px',
					'14' => '14px',
					'16' => '16px',
					'18' => '18px',
					'20' => '20px',
					'24' => '24px',
					'34' => '34px'
				),
				'value' => '14',
				'description' => __( 'Select heading font size for post title in sub tiles.', 'newsplus' )
			)
		);

		/**
		 * Meta params
		 */

		 $meta_params = array(
			array(
				'name' => 'hide_cats',
				'label' => __( 'Hide category links', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide category links', 'newsplus' )
				),
				'description' => __( 'Check to hide category links in post module.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'template',
					'hide_when' => 'list-small'
				)
			),

			array(
				'name' => 'hide_reviews',
				'label' => __( 'Hide review stars', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide review stars', 'newsplus' )
				),
				'description' => __( 'Check to hide review stars in post module. The review feature requires WP Review plugin.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'template',
					'hide_when' => 'list-small'
				)
			),

			array(
				'name' => 'hide_date',
				'label' => __( 'Hide post date', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide post publish date', 'newsplus' )
				),
				'description' => __( 'Check to hide post publish date in post module.', 'newsplus' )
			),

			array(
				'label' => __( 'Date format', 'newsplus' ),
				'name' => 'date_format',
				'type' => 'text',
				'value' => get_option( 'date_format' ),
				'description' => __( 'Provide a <a target="_blank" href="https://codex.wordpress.org/Formatting_Date_and_Time">date format</a> for post date. Use \'human\' for "xx days ago" format.', 'newsplus' ),
				'relation' => array(
					'parent'    => 'hide_date',
					'hide_when' => 'true'
				)
			),

			array(
				'name' => 'hide_author',
				'label' => __( 'Hide author link', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide author link', 'newsplus' )
				),
				'description' => __( 'Check to hide author link in post module.', 'newsplus' )
			),

			array(
				'name' => 'hide_views',
				'label' => __( 'Hide views number', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide views number', 'newsplus' )
				),
				'description' => __( 'Check to hide views number in post module. The views feature requires Post Views Counter plugin.', 'newsplus' )
			),

			array(
				'name' => 'hide_comments',
				'label' => __( 'Hide comments link', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide comments link', 'newsplus' )
				),
				'description' => __( 'Check to hide comments link in post module.', 'newsplus' )
			),

			array(
				'name' => 'show_avatar',
				'label' => __( 'Show author avatar', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Show author avatar', 'newsplus' )
				),
				'description' => __( 'Check to show author avatar in post meta', 'newsplus' )
			)
		);

		 $meta_params_sub = array(
			array(
				'name' => 'hide_cats_sub',
				'label' => __( 'Hide category links', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide category links', 'newsplus' )
				),
				'value' => 'true',
				'description' => __( 'Check to hide category links in post module.', 'newsplus' )
			),

			array(
				'name' => 'hide_reviews_sub',
				'label' => __( 'Hide review stars', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide review stars', 'newsplus' )
				),
				'value' => 'true',
				'description' => __( 'Check to hide review stars in post module. The review feature requires WP Review plugin.', 'newsplus' )
			),

			array(
				'name' => 'hide_date_sub',
				'label' => __( 'Hide post date', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide post publish date', 'newsplus' )
				),
				'description' => __( 'Check to hide post publish date in post module.', 'newsplus' )
			),

			array(
				'name' => 'hide_author_sub',
				'label' => __( 'Hide author link', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide author link', 'newsplus' )
				),
				'description' => __( 'Check to hide author link in post module.', 'newsplus' )
			),

			array(
				'name' => 'hide_views_sub',
				'label' => __( 'Hide views number', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide views number', 'newsplus' )
				),
				'value' => 'true',
				'description' => __( 'Check to hide views number in post module. The views feature requires Post Views Counter plugin.', 'newsplus' )
			),

			array(
				'name' => 'hide_comments_sub',
				'label' => __( 'Hide comments link', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Hide comments link', 'newsplus' )
				),
				'value' => 'true',
				'description' => __( 'Check to hide comments link in post module.', 'newsplus' )
			),

			array(
				'name' => 'show_avatar_sub',
				'label' => __( 'Show author avatar', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Show author avatar', 'newsplus' )
				),
				'description' => __( 'Check to show author avatar in post meta', 'newsplus' )
			),

			array(
				'name' => 'readmore_sub',
				'label' => __( 'Show readmore link in sub tiles', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Show readmore link', 'newsplus' )
				),
				//'value' => 'true', // remove this if you do not need a default content
				'description' => __( 'Check to show a readmore link.', 'newsplus' )
			)
		);

		/**
		 * Schema params
		 */

		$schema_params = array(
			array(
				'name' => 'enable_schema',
				'label' => __( 'Enable schema support', 'newsplus' ),
				'type' => 'checkbox',
				'options' => array(
					'true' => __( 'Enable schema support in generated content', 'newsplus' )
				),
				'description' => __( 'Check to enable schema markup in generated content.', 'newsplus' )
			),
			array(
				'name' => 'publisher_logo',
				'label' => __( 'Add publisher logo', 'newsplus' ),
				'type' => 'attach_image_url',
				'description' => __( 'Add your site logo for publisher image schema.', 'newsplus' ),
				'value' => get_template_directory_uri() . '/images/logo.png',
				'relation' => array(
					'parent'    => 'enable_schema',
					'show_when' => 'true'
				)
			)
		);

		// Add shortcode to King Composer
		kc_add_map(
			array(
				'insert_posts' => array(
					'name' => __( 'Posts Module', 'newsplus' ),
					'description' => __( 'Display posts in various styles', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-pencil',
					'title' => 'Posts module',
					'is_container' => false,
					'system_only' => false,
					'nested' => false,

					'params' => array(

						// Query tab
						'Query' => 	$wp_query_params,

						// Display tab
						'Display' => array(
							array(
								'name' => 'template',
								'label' => 'Display Style',
								'type' => 'dropdown',
								'options' => array(
									'grid' => __( 'Grid', 'newsplus' ),
									'list-big' => __( 'List (Big thumbnails)', 'newsplus' ),
									'list-small' => __( 'List (Small thumbnails)', 'newsplus' ),
									'card' => __( 'Card', 'newsplus' ),
									'gallery' => __( 'Gallery', 'newsplus' ),
									'overlay' => __( 'Overlay', 'newsplus' )
								),
								'value' => 'grid',
								'description' => __( 'Select a display style for posts', 'newsplus' )
							),

							array(
								'name'     => 'col',
								'label'    => 'Number of columns',
								'type'     => 'dropdown',
								'options' => array(
									'1' => '1',
									'2' => '2',
									'3' => '3',
									'4' => '4'
								),
								'value' => 1,
								'description' => __( 'Select number of columns for Grid or Gallery template.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => array( 'grid', 'gallery', 'card', 'overlay' )
								)
							),

							array(
								'name' => 'overlay_style',
								'label' => 'Overlay Style',
								'type' => 'dropdown',
								'options' => array(
									'default' => __( 'White Cutout', 'newsplus' ),
									'dark-bg' => __( 'Dark Scrim', 'newsplus' )
								),
								'value' => 'default',
								'description' => __( 'Select a display style for overlay content', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'overlay'
								)
							),
							array(
								'name'     => 'split',
								'label'    => 'List layout split ratio',
								'type'     => 'dropdown',
								'options' => array(
									'20-80' => '20-80',
									'25-75' => '25-75',
									'33-67' => '33-67',
									'40-60' => '40-60',
									'50-50' => '50-50',
								),
								'value' => '33-67',
								'description' => __( 'Select a layout split ratio (%) for thumbnail + content in list style module.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => array( 'list-big', 'list-small' )
								)
							),

							array(
								'name' => 'enable_masonry',
								'label' => __( 'Enable masonry layout', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable masonry layout', 'newsplus' )
								),
								'description' => __( 'Check to enable masonry layout for grid and gallery template style.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => array( 'grid', 'gallery', 'card', 'overlay' )
								)
							),

							array(
								'name' => 'lightbox',
								'label' => __( 'Enable lightbox', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable lightbox on gallery', 'newsplus' )
								),
								'description' => __( 'Check to enable lightbox on gallery template type.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'gallery'
								)
							),

							array(
								'name' => 'show_title',
								'label' => __( 'Show titles', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show titles on hover', 'newsplus' )
								),
								'description' => __( 'Check to enable titles on hover in gallery thumbnail', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'gallery'
								)
							),

							array(
								'label' => __( 'Image width', 'newsplus' ),
								'name' => 'imgwidth',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image width (in px, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image height', 'newsplus' ),
								'name' => 'imgheight',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image height (in px, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image quality', 'newsplus' ),
								'name' => 'imgquality',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '80',
								'description' => __( 'Provide image quality (in %, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'name' => 'imgcrop',
								'label' => __( 'Image Crop', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable hard crop mode', 'newsplus' )
								),
								'description' => __( 'Check to enable hard cropping of thumbnail image.', 'newsplus' )
							),

							array(
								'name' => 'hide_image',
								'label' => 'Hide image',
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Hide image thumbnail', 'newsplus' )
								),
								//'value' => 'true', // remove this if you do not need a default content
								'description' => __( 'Check to hide image thumbnail', 'newsplus' )
							),

							array(
								'name' => 'hide_video',
								'label' => 'Hide video',
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Hide video embed', 'newsplus' )
								),
								//'value' => 'true', // remove this if you do not need a default content
								'description' => __( 'Check to hide video embed in post module. If featured image is available, it will be shown, else video screenshot will be automatically detected and loaded.', 'newsplus' )
							),
							array(
								'name' => 'video_custom_field',
								'label' => 'Video custom field',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide custom field name for videos. Use this option if your posts already have custom field for videos, with value as valid video URL.', 'newsplus' )
							),

							array(
								'name' => 'xclass',
								'label' => 'CSS class for post container',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the post module container.', 'newsplus' )
							),

							array(
								'name' => 'ptclass',
								'label' => 'CSS class for post thumbnail',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the post thumbnail container.', 'newsplus' )
							),

							array(
								'name' => 'show_excerpt',
								'label' => __( 'Show post excerpt (for list-small template)', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show post excerpt', 'newsplus' )
								),
								'description' => __( 'Check to show post excerpt in post module.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'list-small'
								)
							),


							array(
								'name' => 'show_cats',
								'label' => __( 'Show category links (for list-small template)', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show category links', 'newsplus' )
								),
								'description' => __( 'Check to show category links in post module.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'list-small'
								)
							),


							array(
								'name' => 'show_reviews',
								'label' => __( 'Show review stars (for list-small template)', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show review stars', 'newsplus' )
								),
								'description' => __( 'Check to show review stars in post module.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'template',
									'show_when' => 'list-small'
								)
							)

						),

						// Content tab
						'Content' => $content_params,

						// Meta tab
						'Meta' => $meta_params,

						// Schema tab
						'Schema' => $schema_params,

						// Styling tab
						__( 'Styling', 'newsplus' ) => array(
							array(
								'name' => 'inline_css',
								'label' => __( 'Custom CSS', 'newsplus' ),
								'type' => 'css',
								'options' => array(
									array(
										'screens' => "any",

										__( 'General', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'des' => __( 'This font family will apply to the post module container and it\'s child items.', 'newsplus' ) )
										),

										__( 'Heading', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Heading color', 'newsplus' ), 'selector' => '.entry-title,.entry-title > a' ),
											array( 'property' => 'color', 'label' => __( 'Heading hover color', 'newsplus' ), 'selector' => '.entry-title > a:hover' ),
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.entry-title', 'des' => __( 'This font family will apply to the main post heading.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.entry-title' )
										),

										__( 'Excerpt', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.post-excerpt', 'des' => __( 'This font family will apply to the post excerpt.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'color', 'label' => __( 'Color', 'newsplus' ), 'selector' => '.post-excerpt')
										),

										__( 'Meta', 'newsplus' ) => array(
											array( 'property' => 'background-color', 'label' => __( 'Category links background', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'color', 'label' => __( 'Category links color', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'background-color', 'label' => __( 'Category links hover background', 'newsplus' ), 'selector' => '.post-categories > li > a:hover, .dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Category links hover color', 'newsplus' ), 'selector' => '.post-categories > li > a:hover,.dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Meta text/link color', 'newsplus' ), 'selector' => '.meta-row, .meta-row a'),
											array( 'property' => 'color', 'label' => __( 'Meta links hover color', 'newsplus' ), 'selector' => '.meta-row a:hover'),
										),

										__( 'Review', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Star background color', 'newsplus' ), 'selector' => '.review-star' ),
											array( 'property' => 'color', 'label' => __( 'Star foreground color', 'newsplus' ), 'selector' => '.review-result', 'des' => __( 'Click on important attribute flag for priority over default CSS.', 'newsplus' ) )
										),

										__( 'Custom', 'newsplus' ) => array(
											array( 'property' => 'custom', 'label' => __( 'Custom CSS', 'newsplus' ), 'des' => __( 'Applies to the main post module container.', 'newsplus' ) )
										)
									)
								)
							)
						),

						__( 'Social', 'newsplus' ) => array(
							array(
								'type' => 'checkbox',
								'label' =>  __( 'Social sharing', 'newsplus' ),
								'name' => 'sharing',
								'description' =>  __( 'Check to enable social sharing for post modules', 'newsplus' ),
								'options' => array( 'true' => __( 'Enable social sharing for post modules', 'newsplus' ) )
							),

							array(
								'type' => 'multiple',
								'label' => __( 'Social sharing buttons', 'newsplus' ),
								'name' => 'share_btns',
								'options' => array(
									'twitter' => __( 'Twitter', 'newsplus' ),
									'facebook' => __( 'Facebook', 'newsplus' ),
									'whatsapp' => __( 'Whatsapp', 'newsplus' ),
									'googleplus' => __( 'Google Plus', 'newsplus' ),
									'linkedin' => __( 'LinkedIn', 'newsplus' ),
									'pinterest' => __( 'Pinterest', 'newsplus' ),
									'vkontakte' => __( 'VK Ontakte', 'newsplus' ),
									'reddit' => __( 'Reddit', 'newsplus' ),
									'email' => __( 'E Mail', 'newsplus' )
								),
								'description' => __( 'Select social share buttons. Use Ctrl + Select or Command + select for mutiple selection.', 'newsplus' ),
								'value' => '',
								'relation' => array(
									'parent' => 'sharing',
									'show_when' => 'true'
								)
							)
						)
					)
				), // insert_posts

				'posts_slider' => array(

					'name' => __( 'Posts Slider', 'newsplus' ),
					'description' => __( 'Display a slider of posts', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-tv',
					'title' => 'Posts slider',
					'is_container' => false,
					'system_only' => false,
					'nested' => false,

					'params' => array(
						// Query tab
						'Query' => 	$wp_query_params,

						// Display tab
						'Display' => array(

							array(
								'label' => __( 'Sub style for items', 'newsplus' ),
								'name' => 'substyle',
								'type' => 'dropdown',
								'options' => array(
									'grid' => __( 'Grid', 'newsplus' ),
									'card' => __( 'Card', 'newsplus' ),
									'overlay' => __( 'Overlay', 'newsplus' )
								),
								'value' => 'grid',
								'description' => __( 'Choose a substyle for slider items', 'newsplus' )
							),

							array(
								'name' => 'overlay_style',
								'label' => 'Overlay Style',
								'type' => 'dropdown',
								'options' => array(
									'default' => __( 'White Cutout', 'newsplus' ),
									'dark-bg' => __( 'Dark Scrim', 'newsplus' )
								),
								'value' => 'default',
								'description' => __( 'Select a display style for overlay content', 'newsplus' ),
								'relation' => array(
									'parent'    => 'substyle',
									'show_when' => 'overlay'
								)
							),

							array(
								'label' => __( 'Number of items', 'newsplus' ),
								'name' => 'items',
								'type' => 'number_slider',
								'options' => array(
									'min' => 1,
									'max' => 5,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '1',
								'description' => __( 'Provide number of items per viewport.', 'newsplus' )
							),

							array(
								'label' => __( 'Slides margin', 'newsplus' ),
								'name' => 'margin',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => 24,
								'description' => __( 'Provide margin between slides (in px, without unit).', 'newsplus' )
							),

							array(
								'label' => __( 'Slides margin (mobile)', 'newsplus' ),
								'name' => 'margin_mobile',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => 16,
								'description' => __( 'Provide margin between slides for mobile (in px, without unit).', 'newsplus' )
							),

							array(
								'name' => 'smoothheight',
								'label' => __( 'Auto height', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable auto height for slides', 'newsplus' )
								),
								'description' => __( 'Check to enable auto height for slides', 'newsplus' )
							),

							array(
								'name' => 'controlnav',
								'label' => __( 'Dots navigation', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show dots navigation', 'newsplus' )
								),
								'value' => 'true',
								'description' => __( 'Check to enable dots navigation', 'newsplus' )
							),

							array(
								'name' => 'directionnav',
								'label' => __( 'Prev/Next buttons', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Show prev/next buttons', 'newsplus' )
								),
								'value' => 'true',
								'description' => __( 'Check to enable previous next buttons', 'newsplus' )
							),

							array(
								'name' => 'animationloop',
								'label' => __( 'Loop animation', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Loop animation infinitely', 'newsplus' )
								),
								'description' => __( 'Check to loop animation infinitely', 'newsplus' )
							),

							array(
								'name' => 'slideshow',
								'label' => __( 'Autoplay on start', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Autoplay slider on start', 'newsplus' )
								),
								'value' => 'true',
								'description' => __( 'Check to enable autoplay on start', 'newsplus' )
							),

							array(
								'label' => __( 'Animation speed', 'newsplus' ),
								'name' => 'speed',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 9999,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '400',
								'description' => __( 'Provide animation speed for slides (in miliseconds)', 'newsplus' )
							),

							array(
								'label' => __( 'Animation timeout', 'newsplus' ),
								'name' => 'timeout',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 9999,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '4000',
								'description' => __( 'Provide animation timeout for slides (in miliseconds)', 'newsplus' )
							),

							array(
								'name' => 'effect',
								'label' => 'Slider effect',
								'type' => 'dropdown',
								'options' => array(
									'slide' => __( 'Slide', 'newsplus' ),
									'fade' => __( 'Fade', 'newsplus' )
								),
								'value' => 'slide',
								'description' => __( 'Select an effect for slide animation', 'newsplus' )
							),

							array(
								'label' => __( 'Image width', 'newsplus' ),
								'name' => 'imgwidth',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image width (in px, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image height', 'newsplus' ),
								'name' => 'imgheight',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image height (in px, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image quality', 'newsplus' ),
								'name' => 'imgquality',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',

									'show_input' => true
								),
								'value' => '80',
								'description' => __( 'Provide image quality (in %, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'name' => 'imgcrop',
								'label' => __( 'Image Crop', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable hard crop mode', 'newsplus' )
								),
								'description' => __( 'Check to enable hard cropping of thumbnail image.', 'newsplus' )
							),

							array(
								'name' => 'hide_image',
								'label' => 'Hide image',
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Hide image thumbnail', 'newsplus' )
								),
								//'value' => 'true', // remove this if you do not need a default content
								'description' => __( 'Check to hide image thumbnail', 'newsplus' )
							),

							array(
								'name' => 'hide_video',
								'label' => 'Hide video',
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Hide video embed', 'newsplus' )
								),
								'value' => 'true',
								'description' => __( 'Check to hide video embed in post module. If featured image is available, it will be shown, else video screenshot will be automatically detected and loaded.', 'newsplus' )
							),
							array(
								'name' => 'video_custom_field',
								'label' => 'Video custom field',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide custom field name for videos. Use this option if your posts already have custom field for videos, with value as valid video URL.', 'newsplus' )
							),
							array(
								'name' => 'xclass',
								'label' => 'CSS class for post container',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the post module container.', 'newsplus' )
							),

							array(
								'name' => 'ptclass',
								'label' => 'CSS class for post thumbnail',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the post thumbnail container.', 'newsplus' )
							)

						),

						// Content tab
						'Content' => $content_params,

						// Meta tab
						'Meta' => $meta_params,

						// Schema tab
						'Schema' => $schema_params,

						// Styling tab
						__( 'Styling', 'newsplus' ) => array(
							array(
								'name' => 'inline_css',
								'label' => __( 'Custom CSS', 'newsplus' ),
								'type' => 'css',
								'options' => array(
									array(
										'screens' => "any",

										__( 'General', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'des' => __( 'This font family will apply to the post module container and it\'s child items.', 'newsplus' ) )
										),

										__( 'Heading', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Heading color', 'newsplus' ), 'selector' => '.entry-title,.entry-title > a' ),
											array( 'property' => 'color', 'label' => __( 'Heading hover color', 'newsplus' ), 'selector' => '.entry-title > a:hover' ),
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.entry-title', 'des' => __( 'This font family will apply to the main post heading.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.entry-title' )
										),

										__( 'Excerpt', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.post-excerpt', 'des' => __( 'This font family will apply to the post excerpt.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'color', 'label' => __( 'Color', 'newsplus' ), 'selector' => '.post-excerpt')
										),

										__( 'Meta', 'newsplus' ) => array(
											array( 'property' => 'background-color', 'label' => __( 'Category links background', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'color', 'label' => __( 'Category links color', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'background-color', 'label' => __( 'Category links hover background', 'newsplus' ), 'selector' => '.post-categories > li > a:hover, .dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Category links hover color', 'newsplus' ), 'selector' => '.post-categories > li > a:hover,.dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Meta text/link color', 'newsplus' ), 'selector' => '.meta-row, .meta-row a'),
											array( 'property' => 'color', 'label' => __( 'Meta links hover color', 'newsplus' ), 'selector' => '.meta-row a:hover'),
										),

										__( 'Review', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Star background color', 'newsplus' ), 'selector' => '.review-star' ),
											array( 'property' => 'color', 'label' => __( 'Star foreground color', 'newsplus' ), 'selector' => '.review-result', 'des' => __( 'Click on important attribute flag for priority over default CSS.', 'newsplus' ) )
										),

										__( 'Custom', 'newsplus' ) => array(
											array( 'property' => 'custom', 'label' => __( 'Custom CSS', 'newsplus' ), 'des' => __( 'Applies to the main post module container.', 'newsplus' ) )
										)
									),
								)
							)
						),

						__( 'Social', 'newsplus' ) => array(
							array(
								'type' => 'checkbox',
								'label' =>  __( 'Social sharing', 'newsplus' ),
								'name' => 'sharing',
								'description' =>  __( 'Check to enable social sharing for post modules', 'newsplus' ),
								'options' => array( 'true' => __( 'Enable social sharing for post modules', 'newsplus' ) )
							),

							array(
								'type' => 'multiple',
								'label' => __( 'Social sharing buttons', 'newsplus' ),
								'name' => 'share_btns',
								'options' => array(
									'twitter' => __( 'Twitter', 'newsplus' ),
									'facebook' => __( 'Facebook', 'newsplus' ),
									'whatsapp' => __( 'Whatsapp', 'newsplus' ),
									'googleplus' => __( 'Google Plus', 'newsplus' ),
									'linkedin' => __( 'LinkedIn', 'newsplus' ),
									'pinterest' => __( 'Pinterest', 'newsplus' ),
									'vkontakte' => __( 'VK Ontakte', 'newsplus' ),
									'reddit' => __( 'Reddit', 'newsplus' ),
									'email' => __( 'E Mail', 'newsplus' )
								),
								'description' => __( 'Select social share buttons. Use Ctrl + Select or Command + select for mutiple selection.', 'newsplus' ),
								'value' => '',
								'relation' => array(
									'parent' => 'sharing',
									'show_when' => 'true'
								)
							)
						)
					)
				), // posts_slider

				'newsplus_grid_list' => array(

					'name' => __( 'Posts Tile Grid', 'newsplus' ),
					'description' => __( 'Display post grids as tiles', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-th-large',
					'title' => 'Posts tile grid',
					'is_container' => false,
					'system_only' => false,
					'nested' => false,

					'params' => array(
						// Query tab
						'Query' => 	$wp_query_params,

						// Display tab
						'Display' => array(

							array(
								'label' => __( 'Display style', 'newsplus' ),
								'name' => 'display_style',
								'type' => 'dropdown',
								'options' => array(
									's1' => __( '1 main image + 2x2 Sub', 'newsplus' ),
									's2' => __( '1 main image + 2x3 Sub', 'newsplus' ),
									's3' => __( '4 images per row', 'newsplus' ),
									's6' => __( '3 images per row', 'newsplus' ),
									's4' => __( '2 images per row', 'newsplus' ),
									's5' => __( '1 image per row', 'newsplus' ),
								),
								'value' => 's1',
								'description' => __( 'Select a display style for tile grid', 'newsplus' )
							),

							array(
								'label' => __( 'Viewport width', 'newsplus' ),
								'name' => 'viewport_width',
								'type' => 'number_slider',
								'options' => array(
									'min' => 300,
									'max' => 3000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '1192',
								'description' => __( 'Provide a viewport width for tiles (in px, without unit)', 'newsplus' )
							),

							array(
								'label' => __( 'Images aspect ratio', 'newsplus' ),
								'name' => 'aspect_ratio',
								'type' => 'text',
								'value' => 0.75,
								'description' => __( 'Provide an aspect ratio for image dimension. E.g. 4:3 ratio will be calculated as 3/4 = .75', 'newsplus' )
							),

							array(
								'label' => __( 'Image quality', 'newsplus' ),
								'name' => 'imgquality',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',

									'show_input' => true
								),
								'value' => '80',
								'description' => __( 'Provide image quality (in %, without unit) for the thumbnail image.', 'newsplus' )
							),

							array(
								'name' => 'xclass',
								'label' => 'Extra CSS class',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the post module container.', 'newsplus' )
							)
						),

						// Content tab
						'Content' => array_merge( $content_params, $content_params_sub ),

						// Meta tab
						'Meta' => $meta_params,

						// Meta sub tab
						'Sub Meta' => $meta_params_sub,

						// Schema tab
						'Schema' => $schema_params,

						// Styling tab
						__( 'Styling', 'newsplus' ) => array(
							array(
								'name' => 'inline_css',
								'label' => __( 'Custom CSS', 'newsplus' ),
								'type' => 'css',
								'options' => array(
									array(
										'screens' => "any",

										__( 'General', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'des' => __( 'This font family will apply to the post module container and it\'s child items.', 'newsplus' ) )
										),

										__( 'Heading', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Heading color', 'newsplus' ), 'selector' => '.entry-title > a' ),
											array( 'property' => 'color', 'label' => __( 'Heading hover color', 'newsplus' ), 'selector' => '.entry-title > a:hover' ),
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.entry-title', 'des' => __( 'This font family will apply to the main post heading.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.entry-title' ),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.entry-title' )
										),

										__( 'Subheading', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Heading color', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title > a', 'des' => __( 'Settings in this section applies to the headings of sub tiles in Tile template style', 'newsplus' ) ),
											array( 'property' => 'color', 'label' => __( 'Heading hover color', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title > a:hover' ),
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title', 'des' => __( 'This font family will apply to the smaller headings in sub tiles of tile tempate.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' ),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => 'li.grid-1x1 .entry-title' )
										),

										__( 'Excerpt', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'selector' => '.post-excerpt', 'des' => __( 'This font family will apply to the post excerpt.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ), 'selector' => '.post-excerpt' ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ), 'selector' => '.post-excerpt'),
											array( 'property' => 'color', 'label' => __( 'Color', 'newsplus' ), 'selector' => '.post-excerpt')
										),

										__( 'Meta', 'newsplus' ) => array(
											array( 'property' => 'background-color', 'label' => __( 'Category links background', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'color', 'label' => __( 'Category links color', 'newsplus' ), 'selector' => '.post-categories > li > a, .dark-bg .post-categories > li > a'),
											array( 'property' => 'background-color', 'label' => __( 'Category links hover background', 'newsplus' ), 'selector' => '.post-categories > li > a:hover, .dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Category links hover color', 'newsplus' ), 'selector' => '.post-categories > li > a:hover,.dark-bg .post-categories > li > a:hover, .post-categories > li > a.cat-toggle.active-link'),
											array( 'property' => 'color', 'label' => __( 'Meta text/link color', 'newsplus' ), 'selector' => '.meta-row, .meta-row a'),
											array( 'property' => 'color', 'label' => __( 'Meta links hover color', 'newsplus' ), 'selector' => '.meta-row a:hover'),
										),

										__( 'Review', 'newsplus' ) => array(
											array( 'property' => 'color', 'label' => __( 'Star background color', 'newsplus' ), 'selector' => '.review-star' ),
											array( 'property' => 'color', 'label' => __( 'Star foreground color', 'newsplus' ), 'selector' => '.review-result', 'des' => __( 'Click on important attribute flag for priority over default CSS.', 'newsplus' ) )
										),

										__( 'Custom', 'newsplus' ) => array(
											array( 'property' => 'custom', 'label' => __( 'Custom CSS', 'newsplus' ), 'des' => __( 'Applies to the main post module container.', 'newsplus' ) )
										)
									),
								)
							)
						),

						__( 'Social', 'newsplus' ) => array(
							array(
								'type' => 'checkbox',
								'label' =>  __( 'Social sharing', 'newsplus' ),
								'name' => 'sharing',
								'description' =>  __( 'Check to enable social sharing for post modules', 'newsplus' ),
								'options' => array( 'true' => __( 'Enable social sharing for post modules', 'newsplus' ) )
							),

							array(
								'type' => 'multiple',
								'label' => __( 'Social sharing buttons', 'newsplus' ),
								'name' => 'share_btns',
								'options' => array(
									'twitter' => __( 'Twitter', 'newsplus' ),
									'facebook' => __( 'Facebook', 'newsplus' ),
									'whatsapp' => __( 'Whatsapp', 'newsplus' ),
									'googleplus' => __( 'Google Plus', 'newsplus' ),
									'linkedin' => __( 'LinkedIn', 'newsplus' ),
									'pinterest' => __( 'Pinterest', 'newsplus' ),
									'vkontakte' => __( 'VK Ontakte', 'newsplus' ),
									'reddit' => __( 'Reddit', 'newsplus' ),
									'email' => __( 'E Mail', 'newsplus' )
								),
								'description' => __( 'Select social share buttons. Use Ctrl + Select or Command + select for mutiple selection.', 'newsplus' ),
								'value' => '',
								'relation' => array(
									'parent' => 'sharing',
									'show_when' => 'true'
								)
							)
						)
					)
				), // newsplus_grid_list

				'newsplus_news_ticker' => array(

					'name' => __( 'Posts News Ticker', 'newsplus' ),
					'description' => __( 'Display posts as news ticker', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-ellipsis-h',
					'title' => 'Posts News Ticker',
					'is_container' => false,
					'system_only' => false,
					'nested' => false,

					'params' => array(
						// Query tab
						'Query' => 	$wp_query_params,

						// Display tab
						'Display' => array(

							array(
								'label' => __( 'Title length', 'newsplus' ),
								'name' => 'title_length',
								'type' => 'number_slider',
								'options' => array(
									'min' => 1,
									'max' => 999,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '10',
								'description' => __( 'Provide new title length in words. E.g. 10', 'newsplus' )
							),

							array(
								'label' => __( 'Animation duration', 'newsplus' ),
								'name' => 'duration',
								'type' => 'number_slider',
								'options' => array(
									'min' => 1,
									'max' => 100000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '15000',
								'description' => __( 'Provide ticker animation duration in miliseconds', 'newsplus' )
							),

							array(
								'label' => __( 'Ticker label', 'newsplus' ),
								'name' => 'ticker_label',
								'type' => 'text',
								'value' =>  __( 'Breaking news', 'newsplus' ),
								'description' => __( 'Provide a label for news ticker. Leave blank for no label', 'newsplus' )
							),

							array(
								'name' => 'xclass',
								'label' => 'Extra CSS class',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Any extra class which you wish to add to the ticker contaner.', 'newsplus' )
							)
						),

						__( 'Styling', 'newsplus' ) => array(
							array(
								'name' => 'inline_css',
								'label' => __( 'Custom CSS', 'newsplus' ),
								'type' => 'css',
								'options' => array(
									array(
										'screens' => "any",

										__( 'General', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'des' => __( 'This font family will apply to the entire post ticker.', 'newsplus' ) )
										),

										__( 'Colors', 'newsplus' ) => array(
											array( 'property' => 'background-color', 'label' => __( 'Label background color', 'newsplus' ), 'selector' => '.ticker-label' ),
											array( 'property' => 'color', 'label' => __( 'Label color', 'newsplus' ), 'selector' => '.ticker-label' ),
											array( 'property' => 'background-color', 'label' => __( 'Ticker background', 'newsplus' ), 'selector' => '.np-news-ticker' ),
											array( 'property' => 'color', 'label' => __( 'Posts title color', 'newsplus' ), 'selector' => '.np-news-ticker a' ),
											array( 'property' => 'color', 'label' => __( 'Posts title hover color', 'newsplus' ), 'selector' => '.np-news-ticker a:hover' )
										),

										__( 'Custom', 'newsplus' ) => array(
											array( 'property' => 'custom', 'label' => __( 'Custom CSS', 'newsplus' ), 'des' => __( 'Applies to the main ticker container.', 'newsplus' ) )
										)
									),
								)
							)
						)
					)
				), // newsplus_news_ticker

 				'recipe_method' => array(
					'name' => __( 'Recipe Method', 'newsplus' ),
					'description' => __( 'Create recipe method step', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-settings',
					'title' => 'Recipe Method',
					'system_only'	=> true,
					'is_container' => true,
					'except_child'	=> 'newsplus_recipe',
					'params' => array(
						// General tab
						'General' => array(

							array(
								'name' => 'method_title',
								'label' => __( 'Recipe Method title', 'newsplus' ),
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide optional method title if you want to group instructions under a heading.', 'newsplus' )
							)
						)
					)
				),

 				'newsplus_recipe' => array(
					'name' => __( 'Recipe Generator', 'newsplus' ),
					'description' => __( 'Generate recipe content with schema support', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-cutlery',
					'title' => 'Recipe Generator',
					'is_container' => true,
					'system_only' => false,
					//'nested' => false,
					'views'        => array(
						'type'     => 'views_sections',
						'sections' => 'recipe_method',
						'display'  => 'vertical'
					),
					'params' => array(

						// Display tab
						'General' => array(

							array(
								'label' => __( 'Recipe Name', 'newsplus' ),
								'name' => 'name_src',
								'type' => 'dropdown',
								'options' => array(
									'post_title'   => __( 'Use Post Title', 'newsplus' ),
									'custom' => __( 'Custom Name', 'newsplus' )
								),
								'value' => 'post_title',
								'description' => __( 'Choose a name source for Recipe', 'newsplus' )
							),

							array(
								'label' => __( 'Custom Recipe Name', 'newsplus' ),
								'name' => 'name_txt',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide a name for the recipe', 'newsplus' ),
								'relation' => array(
									'parent'    => 'name_src',
									'show_when' => 'custom'
								)
							),

							array(
								'label' => __( 'Recipe image', 'newsplus' ),
								'type' => 'dropdown',
								'name' => 'img_src',
								'options' => array(
										'featured' => __( 'Use featured image', 'newsplus' ),
										'media_lib' => __( 'Select from media library', 'newsplus' ),
										'ext' => __( 'Use external url', 'newsplus' )
								),
								'value' => '',
								'description' => __( 'Select image source', 'newsplus' )
							),

							array(
								'name' => 'img_lib',
								'label' => __( 'Add recipe image', 'newsplus' ),
								'type' => 'attach_image_url',
								'description' => __( 'Add a recipe image', 'newsplus' ),
								'relation' => array(
									'parent'    => 'img_src',
									'show_when' => 'media_lib'
								)
							),

							array(
								'name' => 'img_ext',
								'label' => __( 'Add external image URL', 'newsplus' ),
								'type' => 'text',
								'description' => __( 'Add external image URL', 'newsplus' ),
								'relation' => array(
									'parent'    => 'img_src',
									'show_when' => 'ext'
								)
							),

							array(
								'label' => __( 'Image alt text', 'newsplus' ),
								'name' => 'img_alt',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide an alternative text for image', 'newsplus' )
							),

							array(
								'label' => __( 'Image caption', 'newsplus' ),
								'name' => 'img_caption',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide a caption for image', 'newsplus' )
							),

							array(
								'label' => __( 'Image width', 'newsplus' ),
								'name' => 'imgwidth',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image width (in px, without unit) for the recipe image.', 'newsplus' ),
									'relation' => array(
									'parent'    => 'img_src',
									'hide_when' => 'ext'
								)
							),


							array(
								'label' => __( 'Image height', 'newsplus' ),
								'name' => 'imgheight',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image height (in px, without unit) for the recipe image.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'img_src',
									'hide_when' => 'ext'
								)
							),

							array(
								'label' => __( 'Image quality', 'newsplus' ),
								'name' => 'imgquality',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '80',
								'description' => __( 'Provide image quality (in %, without unit) for the thumbnail image.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'img_src',
									'hide_when' => 'ext'
								)
							),

							array(
								'name' => 'imgcrop',
								'label' => __( 'Image Crop', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable hard crop mode', 'newsplus' )
								),
								'description' => __( 'Check to enable hard cropping of thumbnail image.', 'newsplus' ),
								'relation' => array(
									'parent'    => 'img_src',
									'hide_when' => 'ext'
								)
							),

							array(
								'label' => __( 'Image align', 'newsplus' ),
								'type' => 'dropdown',
								'name' => 'img_align',
								'options' => array(
										'none' => __( 'None', 'newsplus' ),
										'left' => __( 'Left', 'newsplus' ),
										'right' => __( 'Right', 'newsplus' ),
										'center' => __( 'Center', 'newsplus' )

								),
								'value' => '',
								'description' => __( 'Select image alignment. Image will be aligned with respect to the summary text.', 'newsplus' )
							),

							array(
								'label' => __( 'Recipe Summary', 'newsplus' ),
								'name' => 'summary',
								'type' => 'textarea',
								'value' => base64_encode( 'This is a recipe summary text. It can be a small excerpt about what you are going to cook.' ),
								'description' => __( 'Provide a short summary or description of your recipe', 'newsplus' )
							),

							array(
								'label' => __( 'Method Section Heading', 'newsplus' ),
								'name' => 'method_heading',
								'type' => 'text',
								'value' => __( 'Method', 'newsplus' ),
								'description' => __( 'Provide a heading for method section', 'newsplus' )
							),

							array(
								'name'        => 'enable_numbering',
								'label'       => 'Enable numbering',
								'type'        => 'checkbox',
								'description' => __( 'Check to enable auto numbering on method steps', 'newsplus' ),
								'options'     => array( 'true' => __( 'Enable auto numbering on method steps', 'newsplus' ) ),
								'value'		  => 'true'
							),

							array(
								'label' => __( 'Other notes', 'newsplus' ),
								'name' => 'other_notes',
								'type' => 'editor',
								'value' => base64_encode( 'This is an extra note from author. This can be any tip, suggestion or fact related to the recipe.' ),
								'description' => __( 'Provide extra notes to be shown at the end of recipe', 'newsplus' )
							),

							array(
								'name'        => 'json_ld',
								'label'       => __( 'Enable JSON LD microdata', 'newsplus' ),
								'type'        => 'checkbox',
								'description' => __( 'Enabling this option will add json ld schema data in recipe container.', 'newsplus' ),
								'options'     => array( 'true' => __( 'Check to enable json-ld microdata', 'newsplus' ) ),
								'value'		  => 'true'
							),

						),

						// Meta tab
						'Recipe Meta' => array(

							array(
								'label' => __( 'Recipe Cuisine', 'newsplus' ),
								'type' => 'multiple',
								'name' => 'recipe_cuisine',
								'options' => array(
										'American' => __( 'American', 'newsplus' ),
										'Chinese' => __( 'Chinese', 'newsplus' ),
										'French' => __( 'French', 'newsplus' ),
										'Indian' => __( 'Indian', 'newsplus' ),
										'Italian' => __( 'Italian', 'newsplus' ),
										'Japanese' => __( 'Japanese', 'newsplus' ),
										'Mediterranean' => __( 'Mediterranean', 'newsplus' ),
										'Mexican' => __( 'Mexican', 'newsplus' )
								),
								'value' => '',
								'description' => __( 'Select recipe cuisine from above list or use custom field below', 'newsplus' )
							),

							array(
								'label' => __( 'Other recipe cuisine', 'newsplus' ),
								'name' => 'recipe_cuisine_other',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide comma separated cuisines if not in above list. E.g. Rajasthani, Gujarati', 'newsplus' )
							),

							array(
								'label' => __( 'Recipe Category', 'newsplus' ),
								'type' => 'multiple',
								'name' => 'recipe_category',
								'options' => array(
										'Appetizer' => __( 'Appetizer', 'newsplus' ),
										'Breakfast' => __( 'Breakfast', 'newsplus' ),
										'Dessert' => __( 'Dessert', 'newsplus' ),
										'Drinks' => __( 'Drinks', 'newsplus' ),
										'Main Course' => __( 'Main Course', 'newsplus' ),
										'Salad' => __( 'Salad', 'newsplus' ),
										'Snack' => __( 'Snack', 'newsplus' ),
										'Soup' => __( 'Soup', 'newsplus' )
								),
								'value' => '',
								'description' => __( 'Select recipe categories from above list or use custom field below', 'newsplus' )
							),

							array(
								'label' => __( 'Other recipe category', 'newsplus' ),
								'name' => 'recipe_category_other',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide comma separated categories if not in above list. E.g. Lunch, Starter', 'newsplus' )
							),

							array(
								'label' => __( 'Cooking Method', 'newsplus' ),
								'name' => 'cooking_method',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide a cooking method. E.g. Roasting, Steaming', 'newsplus' )
							),

							array(
								'label' => __( 'Preparation Time (Minutes)', 'newsplus' ),
								'name' => 'prep_time',
								'type' => 'number_slider',
								'options' => array(
									'min' => 1,
									'max' => 1440,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '5',
								'description' => __( 'Provide preparation time (in minutes). E.g. 10', 'newsplus' )

							),

							array(
								'label' => __( 'Cooking Time (Minutes)', 'newsplus' ),
								'name' => 'cook_time',
								'type' => 'number_slider',
								'options' => array(
									'min' => 1,
									'max' => 1440,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '5',
								'description' => __( 'Provide cooking time (in minutes). E.g. 30', 'newsplus' )

							),

							array(
								'label' => __( 'Recipe Author', 'newsplus' ),
								'type' => 'dropdown',
								'name' => 'author_src',
								'options' => array(
										'post_author' => __( 'Use Post Author', 'newsplus' ),
										'custom' => __( 'Custom Author Name', 'newsplus' )
								),
								'value' => '',
								'description' => __( 'Select author name source for recipe', 'newsplus' )
							),

							array(
								'label' => __( 'Custom Author name', 'newsplus' ),
								'name' => 'author_name',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide name of author', 'newsplus' ),
								'relation' => array(
									'parent'    => 'author_src',
									'show_when' => 'custom'
								)
							),

							array(
								'label' => __( 'Author profile URL', 'newsplus' ),
								'name' => 'author_url',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The profile URL of recipe Author. Leave blank to use WordPress user URL.', 'newsplus' )
							)

						),

						// Ingredients tab
						'Ingredients' => array(

							array(
								'label' => __( 'Ingredients Heading', 'newsplus' ),
								'name' => 'ing_heading',
								'type' => 'text',
								'value' => __( 'Ingredients', 'newsplus' ),
								'description' => __( 'Provide a heading for ingredients section', 'newsplus' )
							),

							array(
								'type'			=> 'group',
								'label'			=> __( 'Ingredients', 'newsplus' ),
								'name'			=> 'ingredients',
								'description'	=> __( 'Add list items for each ingredient group', 'newsplus' ),
								'options'		=> array(
													'add_text' => __( 'Add new ingredient group', 'newsplus' )
												),

								'value' 		=> base64_encode( json_encode( array(
													'1' => array(
														'title' => 'For bread',
														'list' => base64_encode( '500 gm wheat floor' )
													),

													'2' => array(
														'title' => 'For cream and toppings',
														'list' => base64_encode( '2 cup milk cream' )
													)
												)
											)
										),

								'params' => array(

										array(
											'type' => 'text',
											'label' => __( 'Ingredients group title', 'newsplus' ),
											'name' => 'title',
											'description' => __( 'Provide a group title for ingredient list. E.g. Ingredients for curry', 'newsplus' ),
											'admin_label' => true,
										),

										array(
											'type' => 'textarea',
											'label' => __( 'Ingredients (one per line)', 'newsplus' ),
											'name' => 'list',
											'description' => __( 'Provide list of ingredients, one per line. E.g. 1 cup milk, skimmed', 'newsplus' ),
											'admin_label' => true
										)
								)
							)
						),

						// Meta tab
						'Nutrition' => array(

							array(
								'label' => __( 'Recipe Yield', 'newsplus' ),
								'name' => 'recipe_yield',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide a recipe yield. E.g. 1 Pizza, or 1 Bowl Rice', 'newsplus' )
							),

							array(
								'label' => __( 'Serving Size', 'newsplus' ),
								'name' => 'serving_size',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide a serving size per container. E.g. 1 Piece(20g), or 100g', 'newsplus' )
							),

							array(
								'label' => __( 'Calorie per serving (cal)', 'newsplus' ),
								'name' => 'calories',
								'type' => 'text',
								'value' => '',
								'description' => __( 'Provide approximate calories (without unit) per serving. E.g. 240', 'newsplus' )
							),

							array(
								'label' => __( 'Suitable for Diet', 'newsplus' ),
								'type' => 'multiple',
								'name' => 'suitable_for_diet',
								'options' => array(
										'Diabetic' => __( 'Diabetic', 'newsplus' ),
										'Gluten Free' => __( 'Gluten Free', 'newsplus' ),
										'Halal' => __( 'Halal', 'newsplus' ),
										'Hindu' => __( 'Hindu', 'newsplus' ),
										'Kosher' => __( 'Kosher', 'newsplus' ),
										'Low Calorie' => __( 'Low Calorie', 'newsplus' ),
										'Low Fat' => __( 'Low Fat', 'newsplus' ),
										'Low Lactose' => __( 'Low Lactose', 'newsplus' ),
										'Low Salt' => __( 'Low Salt', 'newsplus' ),
										'Vegan' => __( 'Vegan', 'newsplus' ),
										'Vegetarian' => __( 'Vegetarian', 'newsplus' ),
								),
								'value' => '',
								'description' => __( 'Select diet for which this recipe is suitable. Remove selection if not applicable.', 'newsplus' )
							),

							array(
								'label' => __( 'Total Fat', 'newsplus' ),
								'name' => 'total_fat',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Total Fat (g), without unit. Standard daily value is 78g', 'newsplus' )
							),

							array(
								'label' => __( 'Saturated Fat', 'newsplus' ),
								'name' => 'saturated_fat',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Saturated Fat (g), without unit. Standard daily value is 20g', 'newsplus' )
							),

							array(
								'label' => __( 'Trans Fat', 'newsplus' ),
								'name' => 'trans_fat',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Trans Fat (g), without unit. ', 'newsplus' )
							),

							array(
								'label' => __( 'Polyunsaturated Fat', 'newsplus' ),
								'name' => 'polyunsat_fat',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Polyunsaturated Fat (g), without unit. ', 'newsplus' )
							),

							array(
								'label' => __( 'Monounsaturated Fat', 'newsplus' ),
								'name' => 'monounsat_fat',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Monounsaturated Fat (g), without unit. ', 'newsplus' )
							),

							array(
								'label' => __( 'Cholesterol', 'newsplus' ),
								'name' => 'cholesterol',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Cholesterol (mg), without unit. Standard daily value is 300mg', 'newsplus' )
							),

							array(
								'label' => __( 'Sodium', 'newsplus' ),
								'name' => 'sodium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Sodium (mg), without unit. Standard daily value is 2300mg', 'newsplus' )
							),

							array(
								'label' => __( 'Total Carbohydrate', 'newsplus' ),
								'name' => 'carbohydrate',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Total Carbohydrate (g), without unit. Standard daily value is 275g', 'newsplus' )
							),

							array(
								'label' => __( 'Dietary Fiber', 'newsplus' ),
								'name' => 'fiber',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Dietary Fiber (g), without unit. Standard daily value is 28g', 'newsplus' )
							),

							array(
								'label' => __( 'Total Sugars', 'newsplus' ),
								'name' => 'sugar',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Total Sugars (g), without unit. ', 'newsplus' )
							),

							array(
								'label' => __( 'Added Sugars', 'newsplus' ),
								'name' => 'added_sugar',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Added Sugars (g), without unit. Standard daily value is 50g', 'newsplus' )
							),

							array(
								'label' => __( 'Sugar Alcohal', 'newsplus' ),
								'name' => 'sugar_alcohal',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Sugar Alcohal (g), without unit. ', 'newsplus' )
							),

							array(
								'label' => __( 'Protein', 'newsplus' ),
								'name' => 'protein',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Protein (g), without unit. Standard daily value is 50g', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin D (Cholecalciferol)', 'newsplus' ),
								'name' => 'vitamin_d',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin D (Cholecalciferol) (mcg), without unit. Standard daily value is 10mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Calcium', 'newsplus' ),
								'name' => 'calcium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Calcium (mg), without unit. Standard daily value is 1300mg', 'newsplus' )
							),

							array(
								'label' => __( 'Iron', 'newsplus' ),
								'name' => 'iron',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Iron (mg), without unit. Standard daily value is 18mg', 'newsplus' )
							),

							array(
								'label' => __( 'Potassium', 'newsplus' ),
								'name' => 'potassium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Potassium (mg), without unit. Standard daily value is 4700mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin A', 'newsplus' ),
								'name' => 'vitamin_a',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin A (mcg), without unit. Standard daily value is 900mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin C (Ascorbic Acid)', 'newsplus' ),
								'name' => 'vitamin_c',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin C (Ascorbic Acid) (mg), without unit. Standard daily value is 90mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin E (Tocopherol)', 'newsplus' ),
								'name' => 'vitamin_e',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin E (Tocopherol) (mg), without unit. Standard daily value is 15mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin K', 'newsplus' ),
								'name' => 'vitamin_k',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin K (mcg), without unit. Standard daily value is 120mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B1 (Thiamin)', 'newsplus' ),
								'name' => 'vitamin_b1',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B1 (Thiamin) (mg), without unit. Standard daily value is 1.2mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B2 (Riboflavin)', 'newsplus' ),
								'name' => 'vitamin_b2',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B2 (Riboflavin) (mg), without unit. Standard daily value is 1.3mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B3 (Niacin)', 'newsplus' ),
								'name' => 'vitamin_b3',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B3 (Niacin) (mg), without unit. Standard daily value is 16mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B6 (Pyridoxine)', 'newsplus' ),
								'name' => 'vitamin_b6',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B6 (Pyridoxine) (mg), without unit. Standard daily value is 1.3mg', 'newsplus' )
							),

							array(
								'label' => __( 'Folate', 'newsplus' ),
								'name' => 'folate',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Folate (mcg), without unit. Standard daily value is 400mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B12 (Cobalamine)', 'newsplus' ),
								'name' => 'vitamin_b12',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B12 (Cobalamine) (mcg), without unit. Standard daily value is 2.4mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Biotin', 'newsplus' ),
								'name' => 'biotin',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Biotin (mcg), without unit. Standard daily value is 30mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Choline', 'newsplus' ),
								'name' => 'choline',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Choline (mg), without unit. Standard daily value is 550 mg', 'newsplus' )
							),

							array(
								'label' => __( 'Vitamin B5 (Pantothenic acid)', 'newsplus' ),
								'name' => 'vitamin_b5',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Vitamin B5 (Pantothenic acid) (mg), without unit. Standard daily value is 5mg', 'newsplus' )
							),

							array(
								'label' => __( 'Phosphorus', 'newsplus' ),
								'name' => 'phosphorus',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Phosphorus (mg), without unit. Standard daily value is 1250mg', 'newsplus' )
							),

							array(
								'label' => __( 'Iodine', 'newsplus' ),
								'name' => 'iodine',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Iodine (mcg), without unit. Standard daily value is 150mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Magnesium', 'newsplus' ),
								'name' => 'magnesium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Magnesium (mg), without unit. Standard daily value is 420mg', 'newsplus' )
							),

							array(
								'label' => __( 'Zinc', 'newsplus' ),
								'name' => 'zinc',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Zinc (mg), without unit. Standard daily value is 11mg', 'newsplus' )
							),

							array(
								'label' => __( 'Selenium', 'newsplus' ),
								'name' => 'selenium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Selenium (mcg), without unit. Standard daily value is 55mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Copper', 'newsplus' ),
								'name' => 'copper',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Copper (mcg), without unit. Standard daily value is 900mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Manganese', 'newsplus' ),
								'name' => 'manganese',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Manganese (mg), without unit. Standard daily value is 2.3mg', 'newsplus' )
							),

							array(
								'label' => __( 'Chromium', 'newsplus' ),
								'name' => 'chromium',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Chromium (mcg), without unit. Standard daily value is 35mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Molybdenum', 'newsplus' ),
								'name' => 'molybdenum',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Molybdenum (mcg), without unit. Standard daily value is 45mcg', 'newsplus' )
							),

							array(
								'label' => __( 'Chloride', 'newsplus' ),
								'name' => 'chloride',
								'type' => 'text',
								'value' => '',
								'description' => __( 'The amount of Chloride (mg), without unit. Standard daily value is 2300mg', 'newsplus' )
							)
						),

						// Hide things tab
						'Hide' => array(

							array(
								'name'        => 'hide_name',
								'label'       => 'Recipe name',
								'type'        => 'checkbox',
								'description' => __( 'If checked, recipe name will not be displayed. It will be used for schema only', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide recipe name', 'newsplus' ) )
							),

							array(
								'name'        => 'hide_date',
								'label'       => 'Publish date',
								'type'        => 'checkbox',
								'description' => __( 'If checked, publsh date will not be displayed. It will be used for schema only', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide recipe publish date', 'newsplus' ) )
							),

							array(
								'name'        => 'hide_author',
								'label'       => 'Recipe Author',
								'type'        => 'checkbox',
								'description' => __( 'If checked, author name will not be displayed. It will be used for schema only', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide author name', 'newsplus' ) )
							),

							array(
								'name'        => 'hide_img',
								'label'       => 'Recipe image',
								'type'        => 'checkbox',
								'description' => __( 'If checked, recipe image will not be displayed. It will be used for schema only', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide recipe image', 'newsplus' ) )
							),

							array(
								'name'        => 'hide_summary',
								'label'       => 'Recipe summary',
								'type'        => 'checkbox',
								'description' => __( 'If checked, recipe summary will not be displayed. It will be used for schema only.', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide recipe summary', 'newsplus' ) )
							),

							array(
								'name'        => 'hide_nutrition',
								'label'       => 'Nutrition Facts',
								'type'        => 'checkbox',
								'description' => __( 'If checked, nutrition facts section will not be displayed.', 'newsplus' ),
								'options'     => array( 'true' => __( 'Hide nutrition facts', 'newsplus' ) )
							)

						)

					) // Nutrition tab

				), // newsplus_recipe

				'newsplus_image' => array(
					'name' => __( 'Image', 'newsplus' ),
					'description' => __( 'Display an image with custom sizes', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'fa-image',
					'title' => __( 'Image', 'newsplus' ),

					'params' => array(

						// Display tab
						'General' => array(
							array(
								'name' => 'src',
								'label' => __( 'Browse Image', 'newsplus' ),
								'type' => 'attach_image_url',
								'description' => __( 'Upload or select image from media library', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Alternative text', 'newsplus' ),
								'name' => 'alt',
								'value' => '',
								'description' => __( 'Provide an alternative text for image', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Image caption', 'newsplus' ),
								'name' => 'caption',
								'value' => '',
								'description' => __( 'Provide a caption for the image', 'newsplus' )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Image align', 'newsplus' ),
								'name' => 'align',
								'options' => array(
										'none' => esc_attr__( 'None', 'newsplus' ),
										'left' => esc_attr__( 'Left', 'newsplus' ),
										'right' => esc_attr__( 'Right', 'newsplus' ),
										'center' => esc_attr__( 'Center', 'newsplus' )
								),
								'value' => 'none',
								'description' => __( 'Select image alignment', 'newsplus' )
							),

							array(
								'label' => __( 'Image width', 'newsplus' ),
								'name' => 'imgwidth',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image width (in px, without unit) for the image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image height', 'newsplus' ),
								'name' => 'imgheight',
								'type' => 'number_slider',
								'options' => array(
									'min' => 10,
									'max' => 2000,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '',
								'description' => __( 'Provide image height (in px, without unit) for the image.', 'newsplus' )
							),

							array(
								'label' => __( 'Image quality', 'newsplus' ),
								'name' => 'imgquality',
								'type' => 'number_slider',
								'options' => array(
									'min' => 0,
									'max' => 100,
									'unit' => '',
									'ratio' => '1',
									'show_input' => true
								),
								'value' => '80',
								'description' => __( 'Select image quality (in %, without unit) for the image.', 'newsplus' )
							),

							array(
								'name' => 'imgcrop',
								'label' => __( 'Image Crop', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Enable hard crop mode', 'newsplus' )
								),
								'description' => __( 'Check to enable hard cropping of image.', 'newsplus' )
							),

							array(
								'name' => 'imgcolor',
								'label' => __( 'Colorize image', 'newsplus' ),
								'type' => 'color_picker',
								'admin_label' => true,
								'description' => __( 'Choose a color for colorizing image', 'newsplus' ),
								'value' => ''
							),

							array(
								'name'        => 'imggrayscale',
								'label'       => __( 'Image grayscale', 'newsplus' ),
								'type'        => 'checkbox',
								'description' => __( 'Check to convert image into grayscale', 'newsplus' ),
								'options'     => array( 'true' => __( 'Convert image to grayscale', 'newsplus' ) )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Image link to', 'newsplus' ),
								'name' => 'linkto',
								'options' => array(
										'none' => esc_attr__( 'None', 'newsplus' ),
										'media' => esc_attr__( 'Media file', 'newsplus' ),
										'custom' => esc_attr__( 'Custom link', 'newsplus' )
								),
								'value' => 'none',
								'description' => __( 'Select image alignment', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Custom link URL', 'newsplus' ),
								'name' => 'custom_url',
								'value' => '',
								'description' => __( 'Provide a custom link URL for image', 'newsplus' ),
								'relation' => array(
									'parent'    => 'linkto',
									'show_when' => 'custom'
								)
							),

							array(
								'type' => 'text',
								'label' => __( 'Link rel attribute', 'newsplus' ),
								'name' => 'linkrel',
								'value' => '',
								'description' => __( 'Provide a rel attribute for image link', 'newsplus' ),
								'relation' => array(
									'parent'    => 'linkto',
									'hide_when' => 'none'
								)
							),

							array(
								'type' => 'text',
								'label' => __( 'Image Title', 'newsplus' ),
								'name' => 'imgtitle',
								'value' => '',
								'description' => __( 'Provide a title for image link', 'newsplus' ),
								'relation' => array(
									'parent'    => 'linkto',
									'hide_when' => 'none'
								)
							),

							array(
								'name'        => 'target',
								'label'       => __( 'Open in new tab', 'newsplus' ),
								'type'        => 'checkbox',
								'description' => __( 'Check to open link in new tab', 'newsplus' ),
								'options'     => array( 'true' => __( 'Open link in new tab', 'newsplus' ) )
							),

							array(
								'type' => 'text',
								'label' => __( 'Extra CSS class', 'newsplus' ),
								'name' => 'xclass',
								'value' => '',
								'description' => __( 'Any extra class you wish to add to image container', 'newsplus' )
							)

						) // General
					) // Params
				), // newsplus_image

				'newsplus_title' => array(
					'name' => __( 'NewsPlus Title', 'newsplus' ),
					'description' => __( 'Content title headings in style', 'newsplus' ),
					'category' => 'NewsPlus',
					'icon' => 'sl-pencil',
					'title' => __( 'NewsPlus Title', 'newsplus' ),
					'is_container' => false,
					'system_only' => false,
					'nested' => false,
					'params' => array(
						__( 'General', 'newsplus' ) => array(
							array(
								'type' => 'text',
								'label' => __( 'Title Text', 'newsplus' ),
								'name' => 'text',
								'description' => __( 'Provide title text', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Link URL', 'newsplus' ),
								'name' => 'link',
								'description' => __( 'Provide a link URL for the text', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Link title attribute', 'newsplus' ),
								'name' => 'link_title',
								'description' => __( 'Provide a title attribute for the link', 'newsplus' )
							),

							array(
								'name' => 'target',
								'label' => __( 'Open link in new tab', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Open link in new tab', 'newsplus' )
								),
								'description' => __( 'Check to open link in new tab.', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Secondary Text', 'newsplus' ),
								'name' => 'sub_text',
								'description' => __( 'Provide a secondary text for the main text', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Secondary Link URL', 'newsplus' ),
								'name' => 'sub_link',
								'description' => __( 'Provide a link URL for the text', 'newsplus' )
							),

							array(
								'type' => 'text',
								'label' => __( 'Sub Link title attribute', 'newsplus' ),
								'name' => 'sub_link_title',
								'description' => __( 'Provide a title attribute for the secondary link', 'newsplus' )
							),

							array(
								'name' => 'sub_target',
								'label' => __( 'Open secondary link in new tab', 'newsplus' ),
								'type' => 'checkbox',
								'options' => array(
									'true' => __( 'Open secondary link in new tab', 'newsplus' )
								),
								'description' => __( 'Check to open secondary link in new tab.', 'newsplus' )
							),
						),

						__( 'Display', 'newsplus' ) => array(
							array(
								'type' => 'dropdown',
								'label' => __( 'Title Tag', 'newsplus' ),
								'name' => 'htag',
								'options' => array(
									'h1' => 'h1',
									'h2' => 'h2',
									'h3' => 'h3',
									'h4' => 'h4',
									'h5' => 'h5',
									'h6' => 'h6',
									'p' => 'p',
									'span' => 'span',
									'div' => 'div'
								),
								'value' => 'h2',
								'description' => __( 'Select a tag for title container', 'newsplus' )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Font Size', 'newsplus' ),
								'name' => 'hsize',
								'options' => array(
										'12' => __( '12px', 'newsplus' ),
										'13' => __( '13px', 'newsplus' ),
										'14' => __( '14px', 'newsplus' ),
										'16' => __( '16px', 'newsplus' ),
										'18' => __( '18px', 'newsplus' ),
										'20' => __( '20px', 'newsplus' ),
										'24' => __( '24px', 'newsplus' )
									),
								'value' => '14',
								'description' => __( 'Select a font size for the title', 'newsplus' )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Bottom Margin', 'newsplus' ),
								'name' => 'margin_btm',
								'options' => array(
									'theme_defined' => __( 'Theme Defined', 'newsplus' ),
									'0' => __( '0px', 'newsplus' ),
									'2' => __( '2px', 'newsplus' ),
									'4' => __( '4px', 'newsplus' ),
									'6' => __( '6px', 'newsplus' ),
									'8' => __( '8px', 'newsplus' ),
									'10' => __( '10px', 'newsplus' ),
									'12' => __( '12px', 'newsplus' ),
									'16' => __( '16px', 'newsplus' ),
									'20' => __( '20px', 'newsplus' ),
									'24' => __( '24px', 'newsplus' ),
									'30' => __( '30px', 'newsplus' ),
									'32' => __( '32px', 'newsplus' ),
									'36' => __( '36px', 'newsplus' ),
									'40' => __( '40px', 'newsplus' ),
									'50' => __( '50px', 'newsplus' ),
									'60' => __( '60px', 'newsplus' ),
									'70' => __( '70px', 'newsplus' )
								),
								'value' => 'theme_defined',
								'description' => __( 'Select a bottom margin for the label', 'newsplus' )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Title Style', 'newsplus' ),
								'name' => 'style',
								'options' => array(
									'plain' => __( 'Plain', 'newsplus' ),
									'flag' => __( 'Flag', 'newsplus' ),
									'label' => __( 'Label', 'newsplus' ),
									'bordered' => __( 'Bordered', 'newsplus' ),
									'bar' => __( 'Bar', 'newsplus' )
								),
								'value' => 'plain',
								'description' => __( 'Select a display style for title', 'newsplus' )
							),

							array(
								'type' => 'dropdown',
								'label' => __( 'Title Background Color', 'newsplus' ),
								'name' => 'color',
								'options' => array(
									'default' => __( 'Default', 'newsplus' ),
									'aqua' => __( 'Aqua', 'newsplus' ),
									'blue' => __( 'Blue', 'newsplus' ),
									'brown' => __( 'Brown', 'newsplus' ),
									'cyan' => __( 'Cyan', 'newsplus' ),
									'dark-blue' => __( 'Dark Blue', 'newsplus' ),
									'deep-orange' => __( 'Deep Orange', 'newsplus' ),
									'green' => __( 'Green', 'newsplus' ),
									'grey' => __( 'Grey', 'newsplus' ),
									'indigo' => __( 'Indigo', 'newsplus' ),
									'orange' => __( 'Orange', 'newsplus' ),
									'pink' => __( 'Pink', 'newsplus' ),
									'red' => __( 'Red', 'newsplus' ),
									'teal' => __( 'Teal', 'newsplus' )
								),
								'value' => 'flag',
								'description' => __( 'Select a background color for title', 'newsplus' )
							),

							array(
								'name'        => 'round_corner',
								'label'       => __( 'Enable round corners', 'newsplus' ),
								'type'        => 'checkbox',
								'description' => __( 'Check to enable round corners on the label', 'newsplus' ),
								'options'     => array( 'true' => __( 'Enable round corners on the label', 'newsplus' ) )
							),

							array(
								'type' => 'text',
								'label' => __( 'Extra CSS class', 'newsplus' ),
								'name' => 'xclass',
								'description' => __( 'Any extra class you wish to add to the label container', 'newsplus' )
							)
						),

						__( 'Styling', 'newsplus' ) => array(
							array(
								'name' => 'inline_css',
								'label' => __( 'Styling', 'newsplus' ),
								'type' => 'css',
								'options' => array(
									array(
										'screens' => "any",

										__( 'General', 'newsplus' ) => array(
											array( 'property' => 'font-family', 'label' => __( 'Font family', 'newsplus' ), 'des' => __( 'This font family will apply to the title.', 'newsplus' ) ),
											array( 'property' => 'font-size', 'label' => __( 'Font size', 'newsplus' ) ),
											array( 'property' => 'line-height', 'label' => __( 'Line height', 'newsplus' ) ),
											array( 'property' => 'font-weight', 'label' => __( 'Font weight', 'newsplus' ) ),
											array( 'property' => 'font-style', 'label' => __( 'Font style', 'newsplus' ) ),
											array( 'property' => 'text-align', 'label' => __( 'Text align', 'newsplus' ) ),
											array( 'property' => 'text-shadow', 'label' => __( 'Text shadow', 'newsplus' ) ),
											array( 'property' => 'text-transform', 'label' => __( 'Text transform', 'newsplus' ) ),
											array( 'property' => 'text-decoration', 'label' => __( 'Text decoration', 'newsplus' ) ),
											array( 'property' => 'letter-spacing', 'label' => __( 'Letter spacing', 'newsplus' ) ),
											array( 'property' => 'word-break', 'label' => __( 'Word break', 'newsplus' ) )
										),

										__( 'Custom', 'newsplus' ) => array(
											array( 'property' => 'custom', 'label' => __( 'Custom CSS', 'newsplus' ), 'des' => __( 'Applies to the main post module container.', 'newsplus' ) )
										)
									)
								)
							)
						)
					)
				) // newsplus_title
			)
		);
	} // newsplus_kc_map_shortcodes
} // function_exists
}
$newsplus_shortcodes = new NewsPlus_Shortcodes();