<?php
/**
 * Functions to enhance the theme functionality
 */
/**
 * Site Title and Description
 *
 * @return void
 */
function blossom_diaries_get_site_title_description( $isSticky = false){
    $site_title       = get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description', 'display' );
    $header_text      = get_theme_mod( 'header_text', 1 );

    if( has_custom_logo() || $site_title || $site_description || $header_text ) :
        if( has_custom_logo() && ( $site_title || $site_description ) && $header_text ) {
            $branding_class = ' has-logo-text';
        }else{
            $branding_class = '';
        }?>
        <div class="site-branding<?php echo esc_attr( $branding_class ); ?>" itemscope itemtype="https://schema.org/Organization">  
            <?php 
            if( function_exists( 'has_custom_logo' ) && has_custom_logo() ){
                echo '<div class="site-logo">';
                the_custom_logo();
                echo '</div>';
            }  ?>

            <?php 
            if( ( $site_title || $site_description ) && $header_text ) :
                echo '<div class="site-title-wrap">';
                if( (is_front_page() && !$isSticky) || ( is_404() && !$isSticky) || (!is_front_page() && is_home() && !$isSticky) ){ ?>
                    <h1 class="site-title" itemprop="name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><?php bloginfo( 'name' ); ?></a></h1>
                    <?php 
                }else{ ?>
                    <p class="site-title" itemprop="name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><?php bloginfo( 'name' ); ?></a></p>
                <?php }
                
                $description = get_bloginfo( 'description', 'display' );
                if ( $description || is_customize_preview() ){ ?>
                    <p class="site-description" itemprop="description"><?php echo $description; ?></p>
                <?php }
                echo '</div>';
            endif; ?>
        </div>    
    <?php endif;
}

/**
 * Primary Navigation
 *
 * @return void
 */
function blossom_diaries_get_primary_nav(){ ?>
    <button aria-label="<?php esc_attr_e( 'primary menu toggle', 'blossom-diaries' ); ?>" class="primary-toggle-button"><i class="fa fa-bars"></i></button>
    <nav id="site-navigation" class="main-navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
        <div class="menu-header-menu-container primary-menu-list">
            <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'menu_class'     => 'menu',
                    'fallback_cb'    => 'blossom_feminine_primary_menu_fallback',
                ) );
            ?>
        </div>
    </nav><!-- #site-navigation -->
    <?php
}

/**
 * Search Form
 *
 * @return void
 */
function blossom_diaries_get_search_form(){ ?>
    <div class="form-section">
        <button aria-label="<?php esc_attr_e( 'search form toggle', 'blossom-diaries' ); ?>" class="btn-search" id="btn-search">
            <i class="fa fa-search" aria-hidden="true"></i>
        </button>
        <div class="form-holder">
            <?php get_search_form(); ?>
            <button class="btn-form-close close" data-toggle-target=".search-modal" data-toggle-body-class="showing-search-modal" data-set-focus=".search-modal .search-field" aria-expanded="false"></button>
        </div>
    </div>
    <?php
}

/**
 * Mobile Navigation
 *
 * @return void
 */
function blossom_diaries_mobile_navigation(){
    $ed_social_media = get_theme_mod( 'ed_social_links', true ); ?>
    
    <div class="mobile-header">
        <div class="header-main">
            <div class="container">
                <div class="header-center">
                    <?php blossom_diaries_get_site_title_description(true); ?>
                </div>
                <div class="mob-nav-site-branding-wrap">
                    <div class="toggle-btn-wrap">
                        <button aria-label="<?php esc_attr_e( 'Open', 'blossom-diaries' ); ?>" class="toggle-btn" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".close-main-nav-toggle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line x1="1" y1="6" x2="23" y2="6" stroke="#0D173B" stroke-width="2"/>
                            <line x1="1" y1="12" x2="23" y2="12" stroke="#0D173B" stroke-width="2"/>
                            <line x1="1" y1="18" x2="23" y2="18" stroke="#0D173B" stroke-width="2"/>
                        </svg>
                        </button>
                        <div class="mobile-header-popup">
                            <div class="header-bottom-slide mobile-menu-list main-menu-modal cover-modal" data-modal-target-string=".main-menu-modal">
                                <div class="header-bottom-slide-inner mobile-menu" aria-label="<?php esc_attr_e( 'Mobile', 'blossom-diaries' ); ?>">
                                    <div class="container">
                                        <div class="mobile-header-wrap">
                                            <button aria-label="<?php esc_attr_e( 'Close', 'blossom-diaries' ); ?>" class="close mobile-close close-main-nav-toggle" data-toggle-target=".main-menu-modal" data-toggle-body-class="showing-main-menu-modal" aria-expanded="false" data-set-focus=".main-menu-modal">
                                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22.5 7.5L7.5 22.5M7.5 7.5L22.5 22.5" stroke="#2B3237" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            </button> 
                                            <div class="tools">
                                                <?php if( blossom_feminine_is_woocommerce_activated()) blossom_feminine_wc_cart_count();
                                                blossom_diaries_get_search_form(); ?>
                                            </div> 
                                        </div>
                                        <div class="mobile-header-wrapper">
                                            <div class="header-left">
                                                <?php blossom_diaries_get_primary_nav(); ?>
                                            </div>
                                        </div>
                                        <?php if( $ed_social_media ){ ?>
                                            <div class="header-social-wrapper">
                                                <div class="header-social">
                                                    <?php blossom_feminine_social_links(); ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

/**
 * Blossomthemes newsletter compatibility
 *
 * @return void
 */
function blossom_diaries_add_inner_div(){
    return true;
}
add_filter( 'bt_newsletter_shortcode_inner_wrap_display', 'blossom_diaries_add_inner_div' );

function blossom_diaries_start_inner_div(){
    echo '<div class="container"><div class="newsletter-inner-wrapper">';
}
add_action( 'bt_newsletter_shortcode_inner_wrap_start', 'blossom_diaries_start_inner_div' );

function blossom_diaries_end_inner_div(){
    echo '</div></div>';
}
add_action( 'bt_newsletter_shortcode_inner_wrap_close', 'blossom_diaries_end_inner_div' );