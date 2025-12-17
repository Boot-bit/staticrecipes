<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adding helper functions.
 */
require get_stylesheet_directory() . '/includes/extras.php';

/**
 * After setup theme hook
 */
function blossom_diaries_theme_setup(){
    /*
     * Make chile theme available for translation.
     * Translations can be filed in the /languages/ directory.
     */
    load_child_theme_textdomain( 'blossom-diaries', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'blossom_diaries_theme_setup' );

function blossom_diaries_styles() {
    	$my_theme = wp_get_theme();
    	$version = $my_theme['Version'];
        
        wp_enqueue_style( 'blossom-diaries', get_stylesheet_directory_uri() . 'style.css', array(), $version );
        
        wp_enqueue_script( 'blossom-diaries', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), $version, true );
        
        wp_style_add_data( 'blossom-diaries', 'rtl', 'replace' );
        
        $array = array( 
            'rtl'       => is_rtl(),
            'animation' => get_theme_mod( 'slider_animation' ),
            'auto'      => get_theme_mod( 'slider_auto', true ),
        ); 
        wp_localize_script( 'blossom-diaries', 'blossom_diaries_data', $array );
}
add_action( 'wp_enqueue_scripts', 'blossom_diaries_styles');

function blossom_diaries_customizer_register( $wp_customize ) {

    $wp_customize->add_section( 'theme_info', array(
        'title'       => __( 'Demo & Documentation' , 'blossom-diaries' ),
        'priority'    => 6,
    ) );
    
    /** Important Links */
    $wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<p>';
    $theme_info .= sprintf( __( 'Demo Link: %1$sClick here.%2$s', 'blossom-diaries' ),  '<a href="' . esc_url( 'https://blossomthemes.com/theme-demo/?theme=blossom-diaries' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';
    $theme_info .= sprintf( __( 'Documentation Link: %1$sClick here.%2$s', 'blossom-diaries' ),  '<a href="' . esc_url( 'https://docs.blossomthemes.com/docs/blossom-feminine/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p>';

    $wp_customize->add_control( new Blossom_Feminine_Note_Control( $wp_customize,
        'theme_info_theme', 
            array(
                'section'     => 'theme_info',
                'description' => $theme_info
            )
        )
    );

    /** Remove Header Image */
    $wp_customize->remove_control( 'header_image' );
    /**Default Color Values*/
    $wp_customize->get_setting( 'primary_color' )->default = '#bc6c65';

    /**Secondary Color */
    $wp_customize->add_setting( 
        'secondary_color', array(
            'default'           => '#e76257',
            'sanitize_callback' => 'sanitize_hex_color'
        ) 
    );

    $wp_customize->add_control( 
        new WP_Customize_Color_Control( 
            $wp_customize, 
            'secondary_color', 
            array(
                'label'       => __( 'Secondary Color', 'blossom-diaries' ),
                'description' => __( 'Secondary color of the theme.', 'blossom-diaries' ),
                'section'     => 'colors',
                'priority'    => 5,                
            )
        )
    );

    /**Default Typography Values*/
    $wp_customize->get_setting('primary_font')->default   = 'Carlito';                                        
    $wp_customize->get_setting('secondary_font')->default = 'Joan';
    $wp_customize->get_setting('font_size')->default = 18;
    
}
add_action( 'customize_register', 'blossom_diaries_customizer_register', 40 );

/**
 * Overriding pluggable functions from the parent for the Blossom Diaries
 *
 * @return void
 */
/** Categories */
function blossom_feminine_categories() {
    $ed_cat_single = get_theme_mod( 'ed_category', false );
    // Hide category and tag text for pages.
    if ( 'post' === get_post_type() && !$ed_cat_single ) {
        /* translators: used between list items, there is a space after the comma */
        $categories_list = get_the_category_list( ' ' );
        if ( $categories_list ) {
            echo '<span class="cat-links is_underline" itemprop="about">' . $categories_list . '</span>';
        }
    }       
}

/** Header */
function blossom_feminine_header(){ ?>
    <header class="site-header header-layout-two" itemscope itemtype="https://schema.org/WPHeader">
        <div class="header-holder">
            <div class="header-m ">
                <div class="container">
                    <?php blossom_diaries_get_site_title_description(); ?>
                </div>
            </div>
        </div>
        <div class="header-b">
            <div class="container">
                <?php blossom_diaries_get_primary_nav(); ?>   
                <div class="right">
                    <?php blossom_feminine_social_links(); ?>                
                    <div class="tools">
                        <?php
                            blossom_diaries_get_search_form();
                            if( blossom_feminine_is_woocommerce_activated()) blossom_feminine_wc_cart_count();    
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php blossom_diaries_mobile_navigation(); ?>
    </header><!-- #masthead -->
	<?php
}

/** Slider Layout */
function blossom_feminine_banner(){
    
    $ed_slider = get_theme_mod( 'ed_slider', true );

    if( ( is_front_page() || is_home() ) && $ed_slider ){ 
        $slider_type    = get_theme_mod( 'slider_type', 'latest_posts' );
        $slider_cat     = get_theme_mod( 'slider_cat' );
        $posts_per_page = get_theme_mod( 'no_of_slides', 3 );
    
        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',            
            'ignore_sticky_posts' => true
        );
        
        if( $slider_type === 'cat' && $slider_cat ){
            $args['cat']            = $slider_cat; 
            $args['posts_per_page'] = -1;  
        }else{
            $args['posts_per_page'] = $posts_per_page;
        }
                
        $qry = new WP_Query( $args );
        
        if( $qry->have_posts() ){ ?>
            <div class="banner banner-layout-three" data-wow-delay="0.1s">
                <div id="banner-slider" class="owl-carousel slider-layout-three">
                    <?php while( $qry->have_posts() ){ $qry->the_post(); ?>
                    <div class="item">
                        <?php 
                        if( has_post_thumbnail() ){
                            the_post_thumbnail( 'blossom-feminine-slider' );    
                        }else{ 
                            blossom_feminine_get_fallback_svg( 'blossom-feminine-slider' ); 
                        }
                        ?>                    
                        <div class="banner-text">
                            <?php
                                blossom_feminine_categories();
                                the_title( '<h2 class="title is_underline"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' );
                            ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php
        }
        wp_reset_postdata();
    }
}

/** Typography */
function blossom_feminine_fonts_url(){
    $fonts_url = '';
    
    $primary_font       = get_theme_mod( 'primary_font', 'Carlito' );
    $ig_primary_font    = blossom_feminine_is_google_font( $primary_font );    
    $secondary_font     = get_theme_mod( 'secondary_font', 'Joan' );
    $ig_secondary_font  = blossom_feminine_is_google_font( $secondary_font );    
    $site_title_font    = get_theme_mod( 'site_title_font', array( 'font-family'=>'Playfair Display', 'variant'=>'700italic' ) );
    $ig_site_title_font = blossom_feminine_is_google_font( $site_title_font['font-family'] );
        
    /* Translators: If there are characters in your language that are not
    * supported by respective fonts, translate this to 'off'. Do not translate
    * into your own language.
    */
    $primary    = _x( 'on', 'Primary Font: on or off', 'blossom-diaries' );
    $secondary  = _x( 'on', 'Secondary Font: on or off', 'blossom-diaries' );
    $site_title = _x( 'on', 'Site Title Font: on or off', 'blossom-diaries' );
    
    
    if ( 'off' !== $primary || 'off' !== $secondary || 'off' !== $site_title ) {
        
        $font_families = array();
     
        if ( 'off' !== $primary && $ig_primary_font ) {
            $primary_variant = blossom_feminine_check_varient( $primary_font, 'regular', true );
            if( $primary_variant ){
                $primary_var = ':' . $primary_variant;
            }else{
                $primary_var = '';    
            }            
            $font_families[] = $primary_font . $primary_var;
        }
         
        if ( 'off' !== $secondary && $ig_secondary_font ) {
            $secondary_variant = blossom_feminine_check_varient( $secondary_font, 'regular', true );
            if( $secondary_variant ){
                $secondary_var = ':' . $secondary_variant;    
            }else{
                $secondary_var = '';
            }
            $font_families[] = $secondary_font . $secondary_var;
        }
        
        if ( 'off' !== $site_title && $ig_site_title_font ) {
            
            if( ! empty( $site_title_font['variant'] ) ){
                $site_title_var = ':' . blossom_feminine_check_varient( $site_title_font['font-family'], $site_title_font['variant'] );    
            }else{
                $site_title_var = '';
            }
            $font_families[] = $site_title_font['font-family'] . $site_title_var;
        }
        
        $font_families = array_diff( array_unique( $font_families ), array('') );
        
        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),            
        );
        
        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    }

    if( get_theme_mod( 'ed_localgoogle_fonts', false ) ) {
        $fonts_url = blossom_feminine_get_webfont_url( add_query_arg( $query_args, 'https://fonts.googleapis.com/css' ) );
    } 
     
    return esc_url_raw( $fonts_url );
}

/** Dyanmic CSS */
function blossom_feminine_dynamic_css(){
    
    $primary_font    = get_theme_mod( 'primary_font', 'Carlito' );
    $primary_fonts   = blossom_feminine_get_fonts( $primary_font, 'regular' );
    $secondary_font  = get_theme_mod( 'secondary_font', 'Joan' );
    $secondary_fonts = blossom_feminine_get_fonts( $secondary_font, 'regular' );
    $font_size       = get_theme_mod( 'font_size', 18 );
    
    $site_title_font      = get_theme_mod( 'site_title_font', array( 'font-family'=>'Playfair Display', 'variant'=>'700italic' ) );
    $site_title_fonts     = blossom_feminine_get_fonts( $site_title_font['font-family'], $site_title_font['variant'] );
    $site_title_font_size = get_theme_mod( 'site_title_font_size', 60 );
    
    $primary_color = get_theme_mod( 'primary_color', '#bc6c65' );
    $secondary_color = get_theme_mod( 'secondary_color', '#e76257' );
    $background_color = get_theme_mod( 'background_color', '#ffffff' );
    $font_color = '#4D4644';
    $heading_color = '#211815';
    
    $rgb = blossom_feminine_hex2rgb( blossom_feminine_sanitize_hex_color( $primary_color ) );
    $rgb2 = blossom_feminine_hex2rgb( blossom_feminine_sanitize_hex_color( $secondary_color ) );
    $rgb4 = blossom_feminine_hex2rgb( blossom_feminine_sanitize_hex_color( $font_color ) );
    $rgb5 = blossom_feminine_hex2rgb( blossom_feminine_sanitize_hex_color( $heading_color ) );
     
    echo "<style type='text/css' media='all'>"; ?>
     
    :root{ 
        --g-primary-color:<?php echo blossom_feminine_sanitize_hex_color($primary_color); ?>;
        --g-primary-color-rgb:<?php echo sprintf('%1$s, %2$s, %3$s', $rgb[0], $rgb[1], $rgb[2]); ?>;

        --g-secondary-color:<?php echo blossom_feminine_sanitize_hex_color($secondary_color); ?>;
        --g-secondary-color-rgb:<?php echo sprintf('%1$s, %2$s, %3$s', $rgb2[0], $rgb2[1], $rgb2[2]); ?>;

        --g-font-color          :<?php echo blossom_feminine_sanitize_hex_color($font_color); ?>;
        --g-font-color-rgb      :<?php echo sprintf('%1$s, %2$s, %3$s', $rgb4[0], $rgb4[1], $rgb4[2]); ?>;
        --g-heading-color       :<?php echo blossom_feminine_sanitize_hex_color($heading_color); ?>;
        --g-heading-color-rgb   :<?php echo sprintf('%1$s, %2$s, %3$s', $rgb5[0], $rgb5[1], $rgb5[2]); ?>;

        --g-background-color:<?php echo $background_color; ?>;

        --g-primary-font:<?php echo wp_kses_post($primary_fonts['font']); ?>;
        --g-secondary-font:<?php echo wp_kses_post($secondary_fonts['font']); ?>;
    }

    body,
    button,
    input,
    select,
    optgroup,
    textarea{
        font-family : <?php echo wp_kses_post( $primary_fonts['font'] ); ?>;
        font-size   : <?php echo absint( $font_size ); ?>px;
    }

    .site-title{
        font-size   : <?php echo absint( $site_title_font_size ); ?>px;
        font-family : <?php echo wp_kses_post( $site_title_fonts['font'] ); ?>;
        font-weight : <?php echo wp_kses_post( $site_title_fonts['weight'] ); ?>;
        font-style  : <?php echo wp_kses_post( $site_title_fonts['style'] ); ?>;
    }

    .site-footer{
        --foot-text-color   :#FFFFFF;
        --foot-bg-color     :#121212;
    }
    
    <?php if( blossom_feminine_is_woocommerce_activated() ) { ?>
        .woocommerce ul.products li.product .add_to_cart_button:hover,
        .woocommerce ul.products li.product .add_to_cart_button:focus,
        .woocommerce ul.products li.product .product_type_external:hover,
        .woocommerce ul.products li.product .product_type_external:focus,
        .woocommerce nav.woocommerce-pagination ul li a:hover,
        .woocommerce nav.woocommerce-pagination ul li a:focus,
        .woocommerce #secondary .widget_shopping_cart .buttons .button:hover,
        .woocommerce #secondary .widget_shopping_cart .buttons .button:focus,
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:hover,
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:focus,
        .woocommerce #secondary .widget_price_filter .ui-slider .ui-slider-range,
        .woocommerce div.product form.cart .single_add_to_cart_button:hover,
        .woocommerce div.product form.cart .single_add_to_cart_button:focus,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:hover,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:focus,
        .woocommerce .woocommerce-message .button:hover,
        .woocommerce .woocommerce-message .button:focus,
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:hover,
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:focus,
        .woocommerce-checkout .woocommerce .woocommerce-info,
        .header-t .tools .cart .count,
        .woocommerce ul.products li.product .added_to_cart:focus, 
        .woocommerce ul.products li.product .added_to_cart:hover{
            background: <?php echo blossom_feminine_sanitize_hex_color( $primary_color ); ?>;
        }

        .woocommerce nav.woocommerce-pagination ul li a{
            border-color: <?php echo blossom_feminine_sanitize_hex_color( $primary_color ); ?>;
        }

        .woocommerce nav.woocommerce-pagination ul li span.current{
            background: <?php echo blossom_feminine_sanitize_hex_color( $primary_color ); ?>;
            border-color: <?php echo blossom_feminine_sanitize_hex_color( $primary_color ); ?>;
        }

        .woocommerce div.product .entry-summary .product_meta .posted_in a:hover,
        .woocommerce div.product .entry-summary .product_meta .posted_in a:focus,
        .woocommerce div.product .entry-summary .product_meta .tagged_as a:hover,
        .woocommerce div.product .entry-summary .product_meta .tagged_as a:focus{
            color: <?php echo blossom_feminine_sanitize_hex_color( $primary_color ); ?>;
        }
            
    <?php } ?>

    <?php echo "</style>";
}

/** Footer */
function blossom_feminine_footer_bottom(){ ?>
    <div class="site-info">
        <div class="container">
            <?php
                blossom_feminine_get_footer_copyright();
                
                esc_html_e( ' Blossom Diaries | Developed By ', 'blossom-diaries' );
                echo '<a href="' . esc_url( 'https://blossomthemes.com/' ) .'" rel="nofollow" target="_blank">' . esc_html__( 'Blossom Themes', 'blossom-diaries' ) . '</a>.';
                
                printf( esc_html__( ' Powered by %s', 'blossom-diaries' ), '<a href="'. esc_url( 'https://wordpress.org/', 'blossom-diaries' ) .'" target="_blank">WordPress</a>.' );
                if ( function_exists( 'the_privacy_policy_link' ) ) {
                    the_privacy_policy_link();
                }
            ?>                    
        </div>
    </div>
    <?php
}

function blossom_feminine_body_classes( $classes ) {
    global $wp_query;
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
    
    if ( $wp_query->found_posts == 0 ) {
        $classes[] = 'no-post';
    }
    
    // Adds a class of custom-background-image to sites with a custom background image.
	if ( get_background_image() ) {
		$classes[] = 'custom-background-image custom-background';
	}
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
		$classes[] = 'custom-background-color custom-background';
	}
    
    if( is_search() && ! is_post_type_archive( 'product' ) ){
        $classes[] = 'search-result-page';   
    }

    if( is_single() || is_page() ){
        $classes[] = 'underline';
    }

    if( is_single()){
        $classes[] = ' single-lay-one';
    }

    if( is_home()){
        $classes[] = ' blog-layout-one';
    }
    
    $classes[] = blossom_feminine_sidebar_layout();
    
	return $classes;
}

function blossom_feminine_post_classes( $classes ){
    
    if( is_home() ){
        $classes[] = 'wow fadeIn image-hover-transition-effect';
    }
    
    if( is_search() ){
        $classes[] = 'search-post';
    }
    
    return $classes;
}

/**
 * Change Markup for breadcrumb
 */

function blossom_feminine_top_bar(){
    remove_action('blossom_feminine_top_bar','blossom_feminine_breadcrumb', 20);
    if( ! is_front_page() ){ ?>
    <div class="top-bar">
        <?php blossom_feminine_breadcrumb(); ?>
		<div class="container">
			<?php 
            /**
             * @hooked blossom_feminine_page_header - 15
            */
            do_action( 'blossom_feminine_top_bar' );
            ?>
		</div>
	</div>
    <?php
    }
}

function blossom_feminine_entry_header(){ ?>
    <header class="entry-header">
    <?php         
        if( is_archive() || ( is_search() && ( 'post' === get_post_type() ) ) ) echo '<div class="top">'; 

        blossom_feminine_categories();

        /**
         * Social sharing in archive.
        */
        if( is_archive() ) do_action( 'blossom_feminine_social_sharing' );
        
        if( is_archive() || ( is_search() && ( 'post' === get_post_type() ) ) ) echo '</div>';
        
        if( is_single() ){
            the_title( '<h1 class="entry-title" itemprop="headline">', '</h1>' );
        }else{
            the_title( '<h2 class="entry-title is_underline" itemprop="headline"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );    
        }
		
		if ( 'post' === get_post_type() ){ 
            echo '<div class="entry-meta">';
            blossom_feminine_posted_by();
            blossom_feminine_posted_on();                
            blossom_feminine_comment_count();	
            echo '</div><!-- .entry-meta -->';		
		}
        ?>
	</header><!-- .entry-header home-->
    <?php
}

function blossom_feminine_related_posts(){ 
    global $post;
    $ed_related_post = get_theme_mod( 'ed_related', true );
    $related_title   = get_theme_mod( 'related_post_title', __( 'You may also like...', 'blossom-diaries' ) );
    if( $ed_related_post ){
        $args = array(
            'post_type'             => 'post',
            'post_status'           => 'publish',
            'posts_per_page'        => 3,
            'ignore_sticky_posts'   => true,
            'post__not_in'          => array( $post->ID ),
            'orderby'               => 'rand'
        );
        $cats = get_the_category( $post->ID );
        if( $cats ){
            $c = array();
            foreach( $cats as $cat ){
                $c[] = $cat->term_id; 
            }
            $args['category__in'] = $c;
        }
        
        $qry = new WP_Query( $args );
        
        if( $qry->have_posts() ){ ?>
        <div class="related-post">
    		<?php if( $related_title ) echo '<h2 class="title">' . esc_html( $related_title ) . '</h2>'; ?>
    		<div class="row">
    			<?php 
                while( $qry->have_posts() ){ 
                    $qry->the_post(); ?>
                    <div class="post">
        				<div class="img-holder">
        					<a href="<?php the_permalink(); ?>">
                            <?php
                                if( has_post_thumbnail() ){
                                    the_post_thumbnail( 'blossom-feminine-related' );
                                }else{ 
                                    blossom_feminine_get_fallback_svg( 'blossom-feminine-related' );
                                }
                            ?>
                            </a>
        					<div class="text-holder">
        						<?php
                                    blossom_feminine_categories();
                                    the_title( '<h3 class="entry-title is_underline"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); 
                                ?>
        					</div>
        				</div>
        			</div>
        			<?php 
                }
                ?>
    		</div>
    	</div>
        <?php
        }
        wp_reset_postdata();  
    }
}