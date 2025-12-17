<?php

class PspMain {

	private static $obj_handle = null;
	
	
	protected $psp_settings_handle;
	protected $psp_sitewide_settings = array();
	protected $psp_helper;
	protected $psp_social_handle;
	protected $seo_title = "";
	protected $psp_can_link = "";
	protected $psp_seo_meta_string = "";
	
	protected $psp_redirect_instance;
	
	public $psp_breadcrumb_settings = array();
	public $psp_ga_settings = array();

	public function __construct() {
		
		global $wp_filter;
		
		//create settings instance
		$psp_settings_instance = PspSettings::get_instance();
		$this->psp_settings_handle = $psp_settings_instance;
		
		$psp_helper_instance = PspHelper::get_instance();		
		$this->psp_helper = $psp_helper_instance;	

		$psp_social_instance = PspSocialMetas::get_instance();
		$this->psp_social_handle = $psp_social_instance;
		
		$psp_tools_instance = PspToolSettings::get_instance();
		$this->psp_redirect_instance = PspRedirections::get_instance();
	
		
		//get value for rss feeds indexing
		$psp_settings = array();
		$psp_settings = get_option("psp_sitewide_settings");
		$noindex_feeds = isset($psp_settings['noindex_rss_feeds']) ? $psp_settings['noindex_rss_feeds'] : '';
		//$noindex_feeds = $psp_settings['noindex_rss_feeds'];
		
		$this->psp_sitewide_settings = $psp_settings;
		
		//if ($psp_settings['rewrite_titles'] && $psp_settings['force_psp_titles']) {
			//do nothing;
		//} else {
		//	add_action('wp_head', array(&$this, 'echo_psp_tags'));
		//}
		add_action( 'admin_bar_menu', array(&$this, 'psp_admin_bar_menu'), 90 );
		/******
		$psp_use_psp_template_script = isset($psp_settings['use_psp_template_script']) ? $psp_settings['use_psp_template_script'] : '';
		if (!$psp_use_psp_template_script) {
			add_action('wp_head', array(&$this, 'psp_head'), -99 );
		}
		*****/
		$psp_rewrite_titles = isset($psp_settings['rewrite_titles']) ? $psp_settings['rewrite_titles'] : '';
		$psp_force_psp_titles = isset($psp_settings['force_psp_titles']) ? $psp_settings['force_psp_titles'] : '';
		if ($psp_rewrite_titles) {
		
			if ($psp_force_psp_titles) {
				add_action('get_header', array(&$this, 'apply_forced_seo_title'), 999);
			} else {
				add_action( 'get_header', array(&$this, 'psp_plugins_loaded'), 999 );
				//add_filter( 'wp_title', array(&$this,'psp_wp_title'), 99 );
			}
		} else {
		    add_action('wp_head', array(&$this,'psp_tags_renderer'), 1);
		}	
		
	
		if ($noindex_feeds) {
			add_action('commentsrss2_head', array(&$this,'noindex_feed'));
			add_action('rss_head', array(&$this,'noindex_feed'));
			add_action('rss2_head', array(&$this,'noindex_feed'));
		}
		
		$nofollow_login_reg_pages = isset($psp_settings['nofollow_loginregn_links']) ? $psp_settings['nofollow_loginregn_links'] : '';
		
		if ($nofollow_login_reg_pages) {
			add_filter('loginout',array(&$this,'nofollow_link'));
			add_filter('register',array(&$this,'nofollow_link'));
		}
		
		$psp_credits = isset($psp_settings['credits']) ? $psp_settings['credits'] : '';
		
		if ($psp_credits || get_option('psp_link_home')) {
			add_action('wp_footer', 'PspSettings::add_credits');
		}
		
		$nofollow_tag_pages = isset($psp_settings['nofollow_tag_links']) ? $psp_settings['nofollow_tag_links'] : '';
		
		if ($nofollow_tag_pages) {
			add_filter('the_tags', array(&$this,'nofollow_tagpages'));	//nofollow tag links generated using the template tag the_tags		
		}	
		//hide feed links
		$psp_hide_feed_links = isset($psp_settings['hide_feed_links']) ? $psp_settings['hide_feed_links'] : '';
		
		if ($psp_hide_feed_links) {
			remove_action('wp_head','feed_links_extra', 3);
			remove_action( 'wp_head', 'feed_links', 2 ); 
		}
		//hide rsd link
		$psp_hide_rsd_link = isset($psp_settings['hide_rsd_link']) ? $psp_settings['hide_rsd_link'] : '';
		
		if ($psp_hide_rsd_link) {
			remove_action( 'wp_head', 'rsd_link' );
		}
		//hide wp shortlink
		$psp_hide_wp_shortlink_wp_head = isset($psp_settings['hide_wp_shortlink_wp_head']) ? $psp_settings['hide_wp_shortlink_wp_head'] : '';
		
		if ($psp_hide_wp_shortlink_wp_head) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
		}
		//hide wlw manifest link
		$psp_hide_wlw_manifest_link = isset($psp_settings['hide_wlw_manifest_link']) ? $psp_settings['hide_wlw_manifest_link'] : '';
		
		if ($psp_hide_wlw_manifest_link) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}
		//hide index rel link
		$psp_hide_index_rel_link = isset($psp_settings['hide_index_rel_link']) ? $psp_settings['hide_index_rel_link'] : '';
		
		if ($psp_hide_index_rel_link) {
			remove_action( 'wp_head', 'index_rel_link' );
		}
		//hide adjacent posts rel link
		$psp_adjacent_posts_rel_link_wp_head = isset($psp_settings['hide_adjacent_posts_rel_link_wp_head']) ? $psp_settings['hide_adjacent_posts_rel_link_wp_head'] : '';
	
		
		if ($psp_adjacent_posts_rel_link_wp_head) {
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		}
		//hide parent post rel link
		$psp_hide_parent_post_rel_link = isset($psp_settings['hide_parent_post_rel_link']) ? $psp_settings['hide_parent_post_rel_link'] : '';
		
		
		if ($psp_hide_parent_post_rel_link) {
			remove_action( 'wp_head', 'parent_post_rel_link' );
		}
		//hide start post rel link
		$psp_hide_start_post_rel_link = isset($psp_settings['hide_start_post_rel_link']) ? $psp_settings['hide_start_post_rel_link'] : '';
	
		
		if ($psp_hide_start_post_rel_link) {
			remove_action( 'wp_head', 'start_post_rel_link' );
		}
		//hide wp generator
		$psp_hide_wp_generator = isset($psp_settings['hide_wp_generator']) ? $psp_settings['hide_wp_generator'] : '';
	
		
		if ($psp_hide_wp_generator) {
			remove_action( 'wp_head', 'wp_generator' ); 
		}	

		//Strip html in comment text
		$psp_comment_text_nohtml_kses = isset($psp_settings['comment_text_nohtml_kses']) ? $psp_settings['comment_text_nohtml_kses'] : '';
		//$psp_comment_text_nohtml_kses = $psp_settings['comment_text_nohtml_kses'];
		
		if ($psp_comment_text_nohtml_kses) {
			add_filter('comment_text', 'wp_filter_nohtml_kses');
		}
		
		//Strip html in comment text RSS
		$psp_comment_text_rss_nohtml_kses = isset($psp_settings['comment_text_rss_nohtml_kses']) ? $psp_settings['comment_text_rss_nohtml_kses'] : '';
		//$psp_comment_text_rss_nohtml_kses = $psp_settings['comment_text_rss_nohtml_kses'];
		
		if ($psp_comment_text_rss_nohtml_kses) {
			add_filter('comment_text_rss', 'wp_filter_nohtml_kses');
		}
		
		//Strip html in comment excerpt
		$psp_comment_excerpt_nohtml_kses = isset($psp_settings['comment_excerpt_nohtml_kses']) ? $psp_settings['comment_excerpt_nohtml_kses'] : '';
		//$psp_comment_excerpt_nohtml_kses = $psp_settings['comment_excerpt_nohtml_kses'];
		
		if ($psp_comment_excerpt_nohtml_kses) {
			add_filter('comment_excerpt', 'wp_filter_nohtml_kses');
		}
		
		//Make all links in comments not clickable
		$psp_comment_text_no_make_clickable = isset($psp_settings['comment_text_no_make_clickable']) ? $psp_settings['comment_text_no_make_clickable'] : '';
		
		if ($psp_comment_text_no_make_clickable) {
			remove_filter('comment_text', 'make_clickable', 9);
		}
		
		if ($psp_credits || get_option('psp_link_home') ) {
			add_action('wp_footer', 'PspSettings::add_credits');						
		}
		
		//add the redirect action
		//add_action( 'template_redirect', array( $this, 'psp_redirect' ), 1, 2);	// do the redirects
		//V2.0.8
		$callbacksforthetdaction = array();
		if ( has_action( 'template_redirect' ) ) {
			$callbacksforthetdaction = $wp_filter['template_redirect']->callbacks;
		}         
		
        $template_redirect_priority = 999;
		if (!empty($callbacksforthetdaction)) {
			$template_redirect_priority = max(array_keys($callbacksforthetdaction)) + 10;
		}
		
		add_action( 'template_redirect', array( $this, 'psp_redirect' ), 1, 2); // do the redirects
		//if($this->psp_redirect_instance) {
		add_action( 'template_redirect', array($this->psp_redirect_instance, 'psp_do_log_404'), $template_redirect_priority, 2); // do the 404 logging
		//}
		//V2.0.8
		
		//add breadcrumbs filter
		add_filter('psp_breadcrumb_trail_args', array(&$this,'psp_breadcrumb_trail_args'));
		
		//filter for virtual robots.txt file
		//require_once(ABSPATH . 'wp-admin/includes/file.php');
		//$robotstxt_file = get_home_path() . 'robots.txt';
		//if (!$robotstxt_file) add_filter('robots_txt', PspToolSettings::filter_virtual_robots(), 10, 2);
		$psp_robotstxt_settings = get_option("psp_robotstxt_settings");
		$psp_use_virtual_robots_file = isset($psp_robotstxt_settings['use_virtual_robots_file']) ? $psp_robotstxt_settings['use_virtual_robots_file'] : '';
		if($psp_use_virtual_robots_file) {
			add_filter('robots_txt', array($psp_tools_instance, 'psp_filter_virtual_robots'), 10, 2);
		}
		
		$psp_ga_settings = get_option("psp_ga_settings");
		$this->psp_ga_settings = $psp_ga_settings;
		$psp_add_ga_tracking_code = isset($psp_ga_settings['ga_tc_enabled']) ? $psp_ga_settings['ga_tc_enabled'] : '';
		if($psp_add_ga_tracking_code) {
			add_action( 'wp_head', array($this, 'add_tracking_code' ), 99 );
		}
		
		//disable 404 redirect guessing v2.0.8
		$psp_permalink_settings = get_option("psp_permalink_settings");
		$psp_disable_wp_404_redirect_guessing = isset($psp_permalink_settings['disable_wp_404_guess']) ? $psp_permalink_settings['disable_wp_404_guess'] : '';
		if($psp_disable_wp_404_redirect_guessing) {
			//add_filter( 'redirect_canonical', array($this, 'psp_disable_404_redirect_guessing' ) );
			remove_action( 'template_redirect', 'redirect_canonical' );
			add_action( 'template_redirect', array( $this, 'psp_redirect_canonical' ), 10, 2);
		}
		
		add_action('admin_notices', array($this, 'platinum_seo_admin_notice__success'));
		add_action('admin_init', array($this, 'platinum_seo_notice_dismissed'));
		//v2.0.8
	}
	//v2.0.8
	public function platinum_seo_notice_dismissed() {
        $user_id = get_current_user_id();
        if ( isset( $_GET['psp_ignore_notice'] ) &&
		'1' === $_GET['psp_ignore_notice'] ) {
    		$user_id = get_current_user_id();
    		// Add the meta so that the notice is permanently dismissed.
    		add_user_meta( $user_id, 'psp_ignore_notice_v_208', true, true );
        };
    }
    
    public function platinum_seo_admin_notice__success() {
        $user_id = get_current_user_id();
	    if ( get_user_meta( $user_id, 'psp_ignore_notice_v_208', true ) ) return;
        global $pagenow;
        $psp_pages = array('platinum-seo-social-pack-by-techblissonline', 'psp-social-by-techblissonline', 'psp-tools-by-techblissonline', 'pspp-licenses');
        if ('index.php' === $pagenow || ( $pagenow == 'admin.php' && in_array(sanitize_key($_GET['page']), $psp_pages))) {
    ?>
        <div class="notice notice-success is-dismissible">
            <strong><p><?php echo esc_html__( 'Thank you for using Platinum SEO Plugin!', 'platinum-seo-pack' ).'<a href="https://techblissonline.com/platinum-wordpress-seo-plugin/#what-is-new" target="_blank"> '.esc_html__( 'See What has Changed in this Version 2.0.8!', 'platinum-seo-pack' ).'</a>'.'<a href="'. esc_url( add_query_arg( [
			'psp_ignore_notice' => '1',
		] ) ) .'" style="float:right; display:block; border:none;">'.esc_html__( 'Dismiss permanently', 'platinum-seo-pack' ) .'</a>'; ?><br><?php echo esc_html__( 'Make sure to clear browser cache after you update to this version!', 'platinum-seo-pack' ) ?></p></strong>
        </div>
    <?php
        }
    }
	
	public function psp_disable_404_redirect_guessing( $url ) {
    	return ( is_404() ) ? false : $url;
    }
	//v2.0.8
	public static function get_instance() {
	
		if ( null == self::$obj_handle ) {
			self::$obj_handle = new self;
		}
	
		return self::$obj_handle;
	
	} // end get_instance;
	
	public function add_tracking_code() {
		if (isset($this->psp_ga_settings) && !empty($this->psp_ga_settings)) {
			$psp_ga_settings = $this->psp_ga_settings;
		} else {		
			$psp_ga_settings = get_option("psp_ga_settings");
			$this->psp_ga_settings = $psp_ga_settings;			
		}
		
		//$ga_tracking_code = (isset($psp_ga_settings['tracking_code']) && !empty($psp_ga_settings['tracking_code'])) ? html_entity_decode(base64_decode($psp_ga_settings['tracking_code']),ENT_QUOTES) : '';
		//if (!empty($psp_ga_settings['tracking_code'])) $ga_tracking_code = base64_decode($psp_ga_settings['tracking_code']);
		if (!empty($psp_ga_settings['tracking_code'])) $ga_tracking_code = $psp_ga_settings['tracking_code'];
		if (!empty($ga_tracking_code)) {
		    //$ga_tracking_code = base64_decode($ga_tracking_code);
			echo html_entity_decode(esc_html(wp_unslash($ga_tracking_code)),ENT_QUOTES). "\r\n";
		}
		//echo $ga_tracking_code;
	}
	
	public function psp_breadcrumb_trail_args($args) {
	    
	    //get settings for breadcrumbs
		
		global $post;
		
		$psp_pt_instance = PspPtsSeoMetas::get_instance();
		$psp_breadcrumb_settings = array();
		
		$psp_settings = $this->psp_sitewide_settings;
		$canonical = $psp_settings['use_canonical'];
		
		if (isset($this->psp_seo_meta_string) && !empty($this->psp_seo_meta_string)) {
		    $seo_meta_string = $this->psp_seo_meta_string;
		} else {
		    $seo_meta_string = $psp_pt_instance->get_pt_seo_metas($post, $canonical);
		    $this->psp_seo_meta_string = $seo_meta_string;
		}
		
		//get taxonomy whose terms are to be included in breadcrumb trail
		$preferred_taxonomy_for_bc = $psp_pt_instance->get_preferred_taxonomy_for_bc();
		if (empty($preferred_taxonomy_for_bc)) {
			$default_taxonomy_for_bc = $psp_pt_instance->get_default_taxonomy_for_bc();
			$preferred_taxonomy_for_bc = $default_taxonomy_for_bc;
		}
		
		$post_type_name = $psp_pt_instance->post_type_name;
		if (!empty($preferred_taxonomy_for_bc) && !empty($post_type_name)) {
			//$taxonomy_for_bc = array($post_type_name => $preferred_taxonomy_for_bc);
			$taxonomy_for_bc = array('post_taxonomy' => array($post_type_name => $preferred_taxonomy_for_bc));
			$psp_breadcrumb_settings = array_merge($taxonomy_for_bc, $psp_breadcrumb_settings);
		}
		$this->psp_breadcrumb_settings = $psp_breadcrumb_settings;
		
		//get settings for breadcrunbs finished;
		return array_merge($args, $this->psp_breadcrumb_settings);

	}
	
	function psp_plugins_loaded() {
	    
	    global $wp_filter;
	    $callbacksforthefilter = array();
	    
	    if (current_theme_supports('title-tag')) {
            // do something special when title-tag is supported...
            //add_filter( 'pre_get_document_title', array(&$this,'psp_wp_title'), 999, 1 );
            //$callbacksforthefilter = $wp_filter['pre_get_document_title']->callbacks;
            //$wp_title_filter_priority = max(array_keys($callbacksforthefilter)) + 10;			
			//if ( ! isset( $wp_filter['pre_get_document_title'] ) ) {
            //    $wp_filter['pre_get_document_title'] = new WP_Hook();
            //}
			$callbacksforthefilter = array();
			if ( has_filter( 'pre_get_document_title' ) ) {
				$callbacksforthefilter = $wp_filter['pre_get_document_title']->callbacks;
			}            
            $wp_title_filter_priority = 999;
			if (!empty($callbacksforthefilter)) {
				$wp_title_filter_priority = max(array_keys($callbacksforthefilter)) + 10;
			}
            add_filter( 'pre_get_document_title', array(&$this,'psp_wp_title'), $wp_title_filter_priority, 1 );
            remove_action('wp_head','_wp_render_title_tag', 1);
            add_action('wp_head', array(&$this,'psp_wp_title_renderer'), 1);
        } else {
            //add_filter( 'wp_title', array(&$this,'psp_wp_title'), 999, 1 );
            $this->apply_forced_seo_title();
            /*****
            $callbacksforthefilter = $wp_filter['wp_title']->callbacks;
            $wp_title_filter_priority = max(array_keys($callbacksforthefilter)) + 10;
            add_filter( 'wp_title', array(&$this,'psp_wp_title'), $wp_title_filter_priority );
            *****/
        }
		
	} //psp_plugins_loaded
	
	function noindex_feed() {

		echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />'."\r\n";
	}
	
	function nofollow_link($output) {

		return str_replace('<a ','<a rel="nofollow" ',$output);

	}
	
	function nofollow_tagpages($output) {

		$output = str_replace('rel="tag"','rel="nofollow tag"',$output);
		return $output;
	}
	
	function apply_forced_seo_title() {
	    //echo ".";
		ob_start(array(&$this, 'callback_for_title_rewrite'));
		//$this->psp_head();
		//ob_end_flush();
	}
	
	public function callback_for_title_rewrite($content) {

		$seo_title = $this->psp_wp_title();
		if (!empty($seo_title)) {
		
			//$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , '<title>' . esc_html($seo_title) . '</title>' );
			$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , esc_html($seo_title) );
			$psp_wp_title = '<title>' . esc_html($psp_wp_title) . '</title>';
			
			$content = preg_replace( '/<title>.*?<\/title>/i', $this->psp_helper->psp_tracer("START"), $content );
		    $content = str_replace( $this->psp_helper->psp_tracer("START"), $this->psp_helper->psp_tracer("START") . $psp_wp_title . "\r\n". $this->echo_psp_tags(false), $content );
		}
		return $content;
		
	}	
	
	function psp_wp_title($title = "") {
	
		//main title function
		global $post;
		
		//Add meta tags to the head
		if (is_feed()) {
			return;
		}
		
		$psp_pt_instance = PspPtsSeoMetas::get_instance();
		$psp_ho_instance = PspHomeOthersSeoMetas::get_instance();
		$psp_tax_instance = PspTaxSeoMetas::get_instance();		
		
		//$canonical = get_option('psp_canonical');
		$psp_settings = $this->psp_sitewide_settings;
		//$canonical = $psp_settings['use_canonical'];
		$seo_title = "";
	    $seo_title = $this->seo_title;
		
		if (is_front_page()) {		
			if (empty($seo_title)) $seo_title = $psp_ho_instance->get_home_psp_title();			
		} else if (is_singular() || (is_home() && !is_front_page())) {
			if (is_home() && !is_front_page()) {
				$posts_page_id = get_option('page_for_posts');
				if ($posts_page_id) $post = get_post($posts_page_id); 
			}
			if (empty($seo_title)) $seo_title = $psp_pt_instance->get_pt_psp_title($post);	
		//} else if (is_page()) {	
		//	$seo_title = $psp_pt_instance->get_page_psp_title($post);		
		} else if (is_category()) {
			if (empty($seo_title)) $seo_title = $psp_tax_instance->get_cat_psp_title();			
		} else if (is_tag()) {
			if (empty($seo_title)) $seo_title = $psp_tax_instance->get_tag_psp_title();			
		} else if (is_tax()) {
		    if (empty($seo_title)) $seo_title = $psp_tax_instance->get_tax_psp_title();
		} else if (is_search()) {
			$seo_title = $psp_ho_instance->get_search_psp_title();
		} else if (is_author()) {
			$seo_title = $psp_ho_instance->get_author_archive_psp_title();
		} else if (is_date()) {
			$seo_title = $psp_ho_instance->get_date_archive_psp_title();
		} else if (is_post_type_archive()) {
			if ( class_exists( 'WooCommerce' ) ) {
				$shop_page = get_page_by_path( 'shop' );
				if (isset($shop_page) && !empty($shop_page) && empty($seo_title)) $seo_title = $psp_pt_instance->get_pt_psp_title($shop_page);				
			} else {
				if (empty($seo_title)) $seo_title = $psp_ho_instance->get_pt_archive_psp_title();
			}
			//$seo_title = $psp_ho_instance->get_pt_archive_psp_title();
		} else if (is_404()) {
			$seo_title = $psp_ho_instance->get_404_psp_title();
		}
		if (!empty($seo_title)) $title = $seo_title;
		return $title;
		
	}
	
	/***
	function psp_wp_title_renderer() {
	    
	    if ( ! current_theme_supports( 'title-tag' ) ) {
            return;
        }
        //$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , '<title>' . esc_html(wp_get_document_title()) . '</title>' );
		$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , esc_html(wp_get_document_title()) );
		$psp_wp_title = '<title>' . esc_html($psp_wp_title) . '</title>';
		
        $this->psp_helper->psp_tracer("START", true);        
        echo $psp_wp_title . "\r\n";
        $this->psp_head();
        //$this->psp_helper->psp_tracer("END", true);
	   
	}
	***/
	function psp_wp_title_renderer() {		
	    
	    if ( ! current_theme_supports( 'title-tag' ) ) {
            return;
        }
		global $post;
		$wp_post_meta_data_arr = array();
		if ($post) {
			$wp_post_meta_data_arr = get_post_meta($post->ID);
		}
		$psp_post_disablers = !empty($wp_post_meta_data_arr['_techblissonline_psp_disable_flags'][0]) ? unserialize($wp_post_meta_data_arr['_techblissonline_psp_disable_flags'][0]) : array();
		
		$psp_disable = !empty($psp_post_disablers['disable_psp']) ? htmlspecialchars(stripcslashes($psp_post_disablers['disable_psp'])) : '';
		
		if ($psp_disable) {
	    		
			//add_filter( 'pre_get_document_title', array(&$this,'psp_wp_title'), $wp_title_filter_priority, 1 );
            //remove_action('wp_head','_wp_render_title_tag', 1);
			remove_filter('pre_get_document_title', array(&$this,'psp_wp_title'));
			//add_action('wp_head','_wp_render_title_tag');
			$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , esc_html(wp_get_document_title()) );
			$psp_wp_title = '<title>' . esc_html($psp_wp_title) . '</title>';
			echo $psp_wp_title . "\r\n";
		} else {
			//$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , '<title>' . esc_html(wp_get_document_title()) . '</title>' );
			$psp_wp_title = apply_filters( 'psp_wp_render_title_tag' , esc_html(wp_get_document_title()) );
			$psp_wp_title = '<title>' . esc_html($psp_wp_title) . '</title>';
			
			$this->psp_helper->psp_tracer("START", true);        
			echo $psp_wp_title . "\r\n";
			$this->psp_head();
			//$this->psp_helper->psp_tracer("END", true);
		}
	   
	}
	
	function psp_tags_renderer() {

		global $post;
		$wp_post_meta_data_arr = array();
		if ($post) {
			$wp_post_meta_data_arr = get_post_meta($post->ID);
		}
		$psp_post_disablers = !empty($wp_post_meta_data_arr['_techblissonline_psp_disable_flags'][0]) ? unserialize($wp_post_meta_data_arr['_techblissonline_psp_disable_flags'][0]) : array();
		
		$psp_disable = !empty($psp_post_disablers['disable_psp']) ? htmlspecialchars(stripcslashes($psp_post_disablers['disable_psp'])) : '';
		
		if (!$psp_disable) {
	    		
			$this->psp_helper->psp_tracer("START", true);       
			$this->psp_head();
			//$this->psp_helper->psp_tracer("END", true);
		}
	   
	}
	
	function psp_redirect() {
	
		//main title function
		global $post;	
		$psp_redirect_to_url = "";
		$psp_redirect_status_code = "";
		
		
		if (is_front_page()) {		

		} else if (is_singular() || (is_home() && !is_front_page())) {
			$post_id = '';
		    if ($post) $post_id = $post->ID;
			if (is_home() && !is_front_page()) {
				$posts_page_id = get_option('page_for_posts');
				if ($posts_page_id) {
					$post_id = $posts_page_id;
				}
			}
			if (!empty($post_id)) {
				$psp_redirect_to_url = get_post_meta($post_id, '_techblissonline_psp_redirect_to_url', true); 
				$psp_redirect_status_code = get_post_meta($post_id, '_techblissonline_psp_redirect_status_code', true);	
			}
		} else if (is_category()) {
			$current_cat_obj = get_category(get_query_var('cat'));
			if ( is_object( $current_cat_obj ) && isset( $current_cat_obj->cat_ID ) ) $cat_id = $current_cat_obj->cat_ID;
			if(!empty($cat_id)) $term_meta = get_option( "psp_category_seo_metas_$cat_id");
			$psp_redirect_to_url = isset($term_meta['redirect_to_url']) ? $term_meta['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($term_meta['redirect_status_code']) ? $term_meta['redirect_status_code'] : ''; 
		} else if (is_tag()) {
			$current_tag_obj = get_term_by('slug',get_query_var('tag'),'post_tag');			
			if ( is_object( $current_tag_obj ) && isset( $current_tag_obj->term_id ) ) $term_id = $current_tag_obj->term_id;
			if(!empty($term_id)) $term_meta = get_option( "psp_taxonomy_seo_metas_$term_id");
			$psp_redirect_to_url = isset($term_meta['redirect_to_url']) ? $term_meta['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($term_meta['redirect_status_code']) ? $term_meta['redirect_status_code'] : '';  
			
		} else if (is_tax()) {
			$term_object = get_term_by( 'slug', get_query_var('term'), get_query_var( 'taxonomy' ) );
			if ( is_object( $term_object ) && isset( $term_object->term_id ) ) $term_id = $term_object->term_id;
			if(!empty($term_id)) $term_meta = get_option( "psp_taxonomy_seo_metas_$term_id");
			$psp_redirect_to_url = isset($term_meta['redirect_to_url']) ? $term_meta['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($term_meta['redirect_status_code']) ? $term_meta['redirect_status_code'] : ''; 
		} else if (is_author()) {
			$author_archive_settings = get_option("psp_author_archive_settings");
			$psp_redirect_to_url = isset($author_archive_settings['redirect_to_url']) ? $author_archive_settings['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($author_archive_settings['redirect_status_code']) ? $author_archive_settings['redirect_status_code'] : ''; 
			
		} else if (is_date()) {
			$date_archive_settings = get_option("psp_date_archive_settings");
			$psp_redirect_to_url = isset($date_archive_settings['redirect_to_url']) ? $date_archive_settings['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($date_archive_settings['redirect_status_code']) ? $date_archive_settings['redirect_status_code'] : ''; 
			
		} else if (is_post_type_archive()) {
			$posttype_archive_settings = get_option("psp_posttype_archive_settings");
			$psp_redirect_to_url = isset($posttype_archive_settings['redirect_to_url']) ? $posttype_archive_settings['redirect_to_url'] : ''; 
			$psp_redirect_status_code = isset($posttype_archive_settings['redirect_status_code']) ? $posttype_archive_settings['redirect_status_code'] : ''; 
			
		} else if (is_404()) {
			//$this->psp_handle_404();
			if($this->psp_redirect_instance) $this->psp_redirect_instance->psp_handle_404();
		}
		$psp_redirect_to_url = esc_url_raw($psp_redirect_to_url);	
		//do wp redirect here
		if (!empty($psp_redirect_to_url)) {
			if (empty($psp_redirect_status_code)) $psp_redirect_status_code = "302";
			wp_safe_redirect($psp_redirect_to_url,$psp_redirect_status_code);
			exit();
		}
	}
	
	function psp_head() {
		$this->echo_psp_tags(true);
	}
	
	function echo_psp_tags($echo=true) {
	
		global $post;
		//global $wp_query;
		//$post = $wp_query->get_queried_object();
	
		//Add meta tags to the head
		if (is_feed()) {
			return;
		}
		
		$taxonomy_for_bc = array();
		
		$psp_pt_instance = PspPtsSeoMetas::get_instance();
		$psp_ho_instance = PspHomeOthersSeoMetas::get_instance();
		$psp_tax_instance = PspTaxSeoMetas::get_instance();	

        $psp_breadcrumb_settings = get_option('psp_breadcrumb_settings');
		
		//$canonical = get_option('psp_canonical');
		$psp_settings = $this->psp_sitewide_settings;
		$canonical = $psp_settings['use_canonical'];
		
		if ($canonical) remove_action('wp_head', 'rel_canonical');
		
		if (is_front_page()) {		
			$seo_meta_string = $psp_ho_instance->get_home_seo_metas($canonical);
			$this->psp_set_social_metas($psp_ho_instance);	
			$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
			if (!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
		} else if (is_singular() || (is_home() && !is_front_page())) {
			if (is_home() && !is_front_page()) {
				$posts_page_id = get_option('page_for_posts');
				if ($posts_page_id) $post = get_post($posts_page_id); 
			}
			if (isset($this->psp_seo_meta_string) && !empty($this->psp_seo_meta_string)) {
			    $seo_meta_string = $this->psp_seo_meta_string;
			} else {
			    $seo_meta_string = $psp_pt_instance->get_pt_seo_metas($post, $canonical);
			    $this->psp_seo_meta_string = $seo_meta_string;
			}
			$this->psp_set_social_metas($psp_pt_instance);
			$this->psp_social_handle->psp_set_post_image($post);
			$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
			if (!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
		} else if (is_category()) {
			$seo_meta_string = $psp_tax_instance->get_cat_seo_metas($canonical);			
			$this->psp_set_social_metas($psp_tax_instance);	
			$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
			if (!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
		} else if (is_tag()) {
			$seo_meta_string = $psp_tax_instance->get_tag_seo_metas($canonical);			
			$this->psp_set_social_metas($psp_tax_instance);	
			$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
			if (!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
		} else if (is_tax()) {
			$seo_meta_string = $psp_tax_instance->get_tax_seo_metas($canonical);
			$this->psp_set_social_metas($psp_tax_instance);	
			$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
			if (!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
		} else if (is_search()) {
			$seo_meta_string = $psp_ho_instance->get_search_seo_metas($canonical);
		} else if (is_author()) {
			$seo_meta_string = $psp_ho_instance->get_author_seo_metas($canonical);
		} else if (is_date()) {
			$seo_meta_string = $psp_ho_instance->get_date_archives_seo_metas($canonical);
		} else if (is_post_type_archive()) {
			if ( class_exists( 'WooCommerce' ) ) {
				$shop_page = get_page_by_path( 'shop' );
				if (isset($shop_page) && !empty($shop_page)) $seo_meta_string = $psp_pt_instance->get_pt_seo_metas($shop_page, $canonical);
				$this->psp_set_social_metas($psp_pt_instance);
				$this->psp_social_handle->psp_set_post_image($shop_page);
				$social_meta_string = $this->psp_social_handle->psp_get_social_metas();
				if(!empty($social_meta_string)) $seo_meta_string .= "\r\n".$social_meta_string;
			} else {
				$seo_meta_string = $psp_ho_instance->get_pt_archives_seo_metas($canonical);
				$this->psp_set_social_metas($psp_ho_instance);
			}
			//$seo_meta_string = $psp_ho_instance->get_pt_archives_seo_metas($canonical);
		} else if (is_404()) {
			$seo_meta_string = $psp_ho_instance->get_404_seo_metas();
		}
		
		$seo_scripts = $this->psp_settings_handle->psp_jsonld_for_google();
		if (!empty($seo_scripts)) $seo_meta_string .= "\r\n".$seo_scripts;
		
		//$echo_seo_meta_string = $this->psp_helper->psp_tracer("START").$seo_meta_string.$this->psp_helper->psp_tracer("END");
		$echo_seo_meta_string = $seo_meta_string.$this->psp_helper->psp_tracer("END");
		if ($echo) {
			echo $echo_seo_meta_string;
		} else {
			return $echo_seo_meta_string;;
		}
	}

	//set social metas
	public function psp_set_social_metas($psp_type_instance) {
	
		if (is_front_page()) {			
		
			//$this->psp_social_handle->psp_social_metas = $psp_type_instance->home_social_meta;
                        //get social metas for front page
            $seo_title = "";
			$psp_pt_instance = PspPtsSeoMetas::get_instance();
			$front_page_id = get_option('page_on_front');
			if ($front_page_id) {
			    $post = get_post($front_page_id); 
			    $seo_title = $psp_pt_instance->get_pt_psp_title($post);
			}
			$this->psp_social_handle->psp_social_metas = $psp_pt_instance->psp_current_ptype_social_meta;

			$seo_title = $psp_type_instance->get_home_psp_title();
			$this->seo_title = $seo_title;
			$this->psp_social_handle->psp_seo_title = $psp_type_instance->home_title;
			$this->psp_social_handle->psp_seo_description = $psp_type_instance->home_description;
			$this->psp_social_handle->psp_can_link = $psp_type_instance->home_can_link;
			$this->psp_social_handle->psp_type = "Website";
			$this->psp_can_link = $psp_type_instance->home_can_link;
			
		} else if (is_singular() || (is_home() && !is_front_page())) {
			global $post;
			if (is_home() && !is_front_page()) {
				$posts_page_id = get_option('page_for_posts');
				if ($posts_page_id) $post = get_post($posts_page_id); 
			}
			$seo_title = $psp_type_instance->get_pt_psp_title($post);
			$this->seo_title = $seo_title;
		
			$this->psp_social_handle->psp_social_metas = $psp_type_instance->psp_current_ptype_social_meta;
			$this->psp_social_handle->psp_seo_title = $psp_type_instance->post_type_title;
			$this->psp_social_handle->psp_seo_description = $psp_type_instance->post_type_description;
			$this->psp_social_handle->psp_can_link = $psp_type_instance->post_type_can_link;
			$this->psp_social_handle->psp_type = $psp_type_instance->post_type_name;
			$this->psp_can_link = $psp_type_instance->post_type_can_link;
		} else if (is_post_type_archive()) {
			if ( class_exists( 'WooCommerce' ) ) {
				$shop_page = get_page_by_path( 'shop' );
				if (isset($shop_page) && !empty($shop_page)) {
					$seo_title = $psp_type_instance->get_pt_psp_title($shop_page);
					$this->seo_title = $seo_title;
					$this->psp_social_handle->psp_social_metas = $psp_type_instance->psp_current_ptype_social_meta;
					$this->psp_social_handle->psp_seo_title = $psp_type_instance->post_type_title;
					$this->psp_social_handle->psp_seo_description = $psp_type_instance->post_type_description;
					$this->psp_social_handle->psp_can_link = $psp_type_instance->post_type_can_link;
					$this->psp_can_link = $psp_type_instance->post_type_can_link;
					$this->psp_social_handle->psp_type = $psp_type_instance->post_type_name;
				}
			} else {
				//$seo_meta_string = $psp_ho_instance->get_pt_archives_seo_metas($canonical);
				$seo_title = $psp_type_instance->get_pt_archive_psp_title();
				$this->seo_title = $seo_title;
				$this->psp_social_handle->psp_seo_title = $seo_title;
				$this->psp_social_handle->psp_can_link = $psp_type_instance->archive_can_link;
				$this->psp_can_link = $psp_type_instance->archive_can_link;
			}
		} else if (is_tax() || is_category() || is_tag()) {
			if (is_category()) $seo_title = $psp_type_instance->get_cat_psp_title();
			if (is_tag()) $seo_title = $psp_type_instance->get_tag_psp_title();
			if (is_tax()) $seo_title = $psp_type_instance->get_tax_psp_title();
			$this->seo_title = $seo_title;
			$this->psp_social_handle->psp_social_metas = $psp_type_instance->term_social_meta;
			$this->psp_social_handle->psp_seo_title = $psp_type_instance->taxonomy_title;
			$this->psp_social_handle->psp_seo_description = $psp_type_instance->taxonomy_description;
			$this->psp_social_handle->psp_can_link = $psp_type_instance->taxonomy_can_link;
			$this->psp_social_handle->psp_type = $psp_type_instance->taxonomy_name;
			$this->psp_can_link = $psp_type_instance->taxonomy_can_link;
		}
	}
	

	function psp_admin_bar_menu() {
		// If the current user can't write posts, this is all of no use, so let's not output an admin menu
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		global $wp_admin_bar, $post;

		$focuskw = '';	
		$seo_url = get_admin_url( null, 'admin.php?page=platinum-seo-social-pack-by-techblissonline' );

		if ( ( is_singular() || ( is_admin() && in_array( $GLOBALS['pagenow'], array(
						'post.php',
						'post-new.php',
					), true ) ) ) && isset( $post ) && is_object( $post ) 
		) {
			//$focuskw    = WPSEO_Meta::get_value( 'focuskw', $post->ID );		
		}
		
		$args = array();
		
		array_push($args, array(
			'id'     => 'psp-menu',
			'title' => __( 'Platinum SEO', 'platinum-seo-pack' ),
			'href'  => $seo_url,
		));				
		
		array_push($args, array(
			'parent' => 'psp-menu',
			'id'     => 'psp-kwresearch',
			'title'  => __( 'Keyword Research', 'platinum-seo-pack' ),
			'#',
			'meta'   => array( 'title' => 'Do keyword research for this page\'s content' ),
		));		
		
		//sort($args);
		for($a=0;$a<sizeOf($args);$a++)
		{
			$wp_admin_bar->add_node($args[$a]);
		}
		
		$args = array();
		
		array_push($args, array(
			'parent' => 'psp-kwresearch',
			'id'     => 'psp-adwordsexternal',
			'title'  => __( 'AdWords External', 'platinum-seo-pack' ),
			'href'   => 'http://adwords.google.com/keywordplanner',
			'meta'   => array( 'target' => '_blank', 'title' => 'Go to Adwords External' ),
		));
		
		array_push($args, array(
			'parent' => 'psp-kwresearch',
			'id'     => 'psp-googleinsights',
			'title'  => __( 'Google Insights', 'platinum-seo-pack' ),
			'href'   => 'http://www.google.com/insights/search/#q=' . urlencode( $focuskw ) . '&cmpt=q',
			'meta'   => array( 'target' => '_blank', 'title' => 'Go to Go to google Insights' ),
		));
	
		//sort($args);
		for($a=0;$a<sizeOf($args);$a++)
		{
			$wp_admin_bar->add_node($args[$a]);
		}
		
		$psp_settings = $this->psp_sitewide_settings;
		//echo "is premium ".$psp_settings['premium'];
		
		$psp_premium_valid = isset($psp_settings['premium']) && !empty($psp_settings['premium']) ? $psp_settings['premium'] : '';
		$psp_premium_status = isset($psp_settings['psp_premium_license_key_status']) ? $psp_settings['psp_premium_license_key_status'] : '';

		if ($psp_premium_valid) {
			//$current_page_url = $this->psp_can_link;
			$current_page_url = '';//get_permalink($post);
			if (empty($current_page_url) && is_home()) {
			    $current_page_url = esc_url(home_url());
			    $current_page_url = $this->psp_helper->paged_link($current_page_url);
			} else if (is_tax() || is_category() || is_tag()) {
			    
			    $psp_current_id = get_queried_object()->term_id;
			    $current_page_url = get_term_link($psp_current_id);
			    $current_page_url = $this->psp_helper->paged_link($current_page_url);
			   
			} else {
			    $current_page_url = get_permalink($post);
			}
			
			if ( $current_page_url ) {
			
				$args = array();
				
				array_push($args, array(
					'parent' => 'psp-menu',
					'id'     => 'psp-page-analysis',
					'title'  => __( 'Analyze this page', 'platinum-seo-pack' ),
					'#',
					'meta'   => array( 'title' => 'Analyse the SEO factors for this page' ),
				));
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-seoanalysis',
					'title'  => __( 'Analyze your onpage SEO', 'platinum-seo-pack' ),
					'href'   => '//techblissonline.com/tools/seo-analysis/?url=' . urlencode( $current_page_url ) . '&utm_source=techblissonline-pspd&utm_medium=wp&utm_content=topbar&utm_campaign=pspd-satool',
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-kwdensity',
					'title'  => __( 'Check Keyword Density', 'platinum-seo-pack' ),
					'href'   => '//techblissonline.com/tools/keyword-density-analysis-tool/?url=' . urlencode( $current_page_url ) . '&utm_source=techblissonline-pspd&utm_medium=wp&utm_content=topbar&utm_campaign=pspd-kdtool',
					'meta'   => array( 'target' => '_blank' ),
				) );
			
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-inlinks-ose',
					'title'  => __( 'Check Inlinks (OSE)', 'platinum-seo-pack' ),
					'href'   => '//ahrefs.com/backlink-checker?site=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				));				
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-cache',
					'title'  => __( 'Check Google Cache', 'platinum-seo-pack' ),
					'href'   => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-header',
					'title'  => __( 'Check Headers', 'platinum-seo-pack' ),
					'href'   => '//quixapp.com/headers/?r=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-richsnippets',
					'title'  => __( 'Check Rich Snippets', 'platinum-seo-pack' ),
					'href'   => '//www.google.com/webmasters/tools/richsnippets?q=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				));
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-facebookdebug',
					'title'  => __( 'Facebook Debugger', 'platinum-seo-pack' ),
					'href'   => '//developers.facebook.com/tools/debug/og/object?q=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				));
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-pinterestvalidator',
					'title'  => __( 'Pinterest Rich Pins Validator', 'platinum-seo-pack' ),
					'href'   => '//developers.pinterest.com/rich_pins/validator/?link=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-htmlvalidation',
					'title'  => __( 'HTML Validator', 'platinum-seo-pack' ),
					'href'   => '//validator.w3.org/check?uri=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				));
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-cssvalidation',
					'title'  => __( 'CSS Validator', 'platinum-seo-pack' ),
					'href'   => '//jigsaw.w3.org/css-validator/validator?uri=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-pagespeed',
					'title'  => __( 'Google Page Speed Test', 'platinum-seo-pack' ),
					'href'   => '//developers.google.com/speed/pagespeed/insights/?url=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-modernie',
					'title'  => __( 'Modern IE Site Scan', 'platinum-seo-pack' ),
					'href'   => '//www.modern.ie/en-us/report#' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				array_push($args, array(
					'parent' => 'psp-page-analysis',
					'id'     => 'psp-mobile-friendly',
					'title'  => __( 'Mobile-Friendly Test', 'platinum-seo-pack' ),
					'href'   => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . urlencode( $current_page_url ),
					'meta'   => array( 'target' => '_blank' ),
				) );
				
				//sort($args);
				for($a=0;$a<sizeOf($args);$a++)
				{
					$wp_admin_bar->add_node($args[$a]);
				}
				
			}
		}

		$is_admin = current_user_can( 'manage_options' );

		if ( ! $is_admin && is_multisite() ) {		
			$is_admin = is_super_admin();
		}
		
		if ( $is_admin ) {
		
			$args = array();
			
			array_push($args, array(
				'parent' => 'psp-menu',
				'id'     => 'psp-settings',
				'title'  => __( 'Platinum SEO Settings', 'platinum-seo-pack' ),
				'meta'   => array( 'title' => 'Quickly navigate to Platinum SEO Settings pages' ),
			) );
			
			array_push($args, array(
				'parent' => 'psp-settings',
				'id'     => 'psp-general',
				'title'  => __( 'SEO', 'platinum-seo-pack' ),
				'href'   => admin_url( 'admin.php?page=platinum-seo-social-pack-by-techblissonline' ),
			) );
			
			array_push($args, array(
				'parent' => 'psp-settings',
				'id'     => 'psp-tools',
				'title'  => __( 'SEO - Tools', 'platinum-seo-pack' ),
				'href'   => admin_url( 'admin.php?page=psp-seo-tools-by-techblissonline' ),
			) );
					
			//sort($args);
			for($a=0;$a<sizeOf($args);$a++)
			{
				$wp_admin_bar->add_node($args[$a]);
			}
			
		}
	}

    public function psp_plugin_upgrade( $upgrader_object, $options ) {

    	delete_option("psp_tools_plugin_url");
    	add_option("psp_tools_plugin_url", "https://techblissonline.com/tools/platinum-seo-wordpress-premium/","", "no");
    
    }
	
	// Refresh rules on activation/deactivation/category changes
	//register_activation_hook(__FILE__, 'psp_activate');
	public function psp_activate() {
		
		$psp_settings_instance = $this->psp_settings_handle;
		
		//General settings
		$psp_settings = array();
		$psp_settings = get_option("psp_sitewide_settings");
		
		//if (empty($psp_settings)) {
		if ( !isset($psp_settings['separator']) || empty($psp_settings['separator']) ) 
		    $psp_settings['separator'] = '–';
		if ( !isset($psp_settings['rewrite_titles']) || empty($psp_settings['rewrite_titles']) ) $psp_settings['rewrite_titles'] = 1;
		if ( !isset($psp_settings['paged_title_format']) || empty($psp_settings['paged_title_format']) ) $psp_settings['paged_title_format'] = "%sep% Page %page%";
		//if ( !isset($psp_settings['noindex_subpages']) || empty($psp_settings['noindex_subpages']) ) $psp_settings['noindex_subpages'] = 1;
		if ( !isset($psp_settings['noindex_rss_feeds']) || empty($psp_settings['noindex_rss_feeds']) ) $psp_settings['noindex_rss_feeds'] = 1;
		if ( !isset($psp_settings['use_meta_noodp']) || empty($psp_settings['use_meta_noodp']) ) $psp_settings['use_meta_noodp'] = 1;
		if ( !isset($psp_settings['use_meta_noydir']) || empty($psp_settings['use_meta_noydir']) ) $psp_settings['use_meta_noydir'] = 1;
		if ( !isset($psp_settings['autogenerate_description']) || empty($psp_settings['autogenerate_description']) ) $psp_settings['autogenerate_description'] = 1;
		if ( !isset($psp_settings['use_canonical']) || empty($psp_settings['use_canonical']) ) $psp_settings['use_canonical'] = 1;
		if ( !isset($psp_settings['hide_metabox_advanced']) || empty($psp_settings['hide_metabox_advanced']) ) $psp_settings['hide_metabox_advanced'] = 1;
			//$psp_settings['sitelinks_search_box'] = 1;
			//$psp_settings['sitelinks_searchbox_target'] = trailingslashit(get_home_url()).'?s={search_term}';
			
			delete_option("psp_sitewide_settings");
			add_option("psp_sitewide_settings", $psp_settings);			
		//}
		
		$psp_other_settings = array();
		$psp_other_settings = get_option("psp_other_settings");
		
		if ( !isset($psp_other_settings['sitelinks_search_box']) || empty($psp_other_settings['sitelinks_search_box']) ) $psp_other_settings['sitelinks_search_box'] = 1;
		if ( !isset($psp_other_settings['sitelinks_searchbox_target']) || empty($psp_other_settings['sitelinks_searchbox_target']) ) $psp_other_settings['sitelinks_searchbox_target'] = trailingslashit(get_home_url()).'?q=search_term';
		
		delete_option("psp_other_settings");
		add_option("psp_other_settings", $psp_other_settings, "", "no");
		
		add_option("psp_tools_plugin_url", "https://techblissonline.com/tools/platinum-seo-wordpress-premium/","", "no");
			
		//search result pages
		$psp_search_settings = array();
		$psp_search_settings = get_option("psp_search_result_settings");
		
		//if (empty($psp_search_settings)) {
		if ( !isset($psp_search_settings['title']) || empty($psp_search_settings['title']) )	$psp_search_settings['title'] = "%search% %sep% %site_name%";
		if ( !isset($psp_search_settings['robots']) || empty($psp_search_settings['robots']) ) $psp_search_settings['robots'] = 1;
			
			delete_option("psp_search_result_settings");
			add_option("psp_search_result_settings", $psp_search_settings, "", "no");
		//}
		//404 page
		$psp_404_settings = array();
		$psp_404_settings = get_option("psp_404_page_settings");
		
		//if (empty($psp_404_settings)) {
		if ( !isset($psp_404_settings['title']) || empty($psp_404_settings['title']) ) $psp_404_settings['title'] = "%title_404% %sep% %site_name%";
		if ( !isset($psp_404_settings['robots']) || empty($psp_404_settings['robots']) ) $psp_404_settings['robots'] = 1;
			
			delete_option("psp_404_page_settings");
			add_option("psp_404_page_settings", $psp_404_settings, "", "no");
		//}
		
		//category settings
		$psp_category_settings = array();
		$psp_category_settings = get_option("psp_category_settings");
		
		//if (empty($psp_category_settings)) {
		if ( !isset($psp_category_settings['title']) || empty($psp_category_settings['title']) ) $psp_category_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_category_settings['description']) || empty($psp_category_settings['description']) ) $psp_category_settings['description'] = "%seo_description%";
			
			delete_option("psp_category_settings");
			add_option("psp_category_settings", $psp_category_settings, "", "no");
		//}
		//tag settings
		$psp_tag_settings = array();
		$psp_tag_settings = get_option("psp_tag_settings");
		
		//if (empty($psp_tag_settings)) {
		if ( !isset($psp_tag_settings['title']) || empty($psp_tag_settings['title']) ) $psp_tag_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_tag_settings['description']) || empty($psp_tag_settings['description']) ) $psp_tag_settings['description'] = "%seo_description%";
			
			delete_option("psp_tag_settings");
			add_option("psp_tag_settings", $psp_tag_settings, "", "no");
		//}
		//post format settings
		$psp_post_format_settings = array();
		$psp_post_format_settings = get_option("psp_post_format_settings");
		
		//if (empty($psp_post_format_settings)) {
		if ( !isset($psp_post_format_settings['title']) || empty($psp_post_format_settings['title']) ) $psp_post_format_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_post_format_settings['description']) || empty($psp_post_format_settings['description']) ) $psp_post_format_settings['description'] = "%seo_description%";
			
			delete_option("psp_post_format_settings");
			add_option("psp_post_format_settings", $psp_post_format_settings, "", "no");
		//}
		//custom taxonomy settings
		$cust_taxonomies = $psp_settings_instance->custom_taxonomies;	
		$psp_tax_settings = array();
		foreach($cust_taxonomies as $cust_taxonomy) {
			$psp_settings_name = "psp_".$cust_taxonomy."_settings";
			$psp_tax_settings = get_option($psp_settings_name);
			
			//if (empty($psp_tax_settings)) {
			if ( !isset($psp_tax_settings['title']) || empty($psp_tax_settings['title']) ) $psp_tax_settings['title'] = "%seo_title% %sep% %site_name%";
			if ( !isset($psp_tax_settings['description']) || empty($psp_tax_settings['description']) ) $psp_tax_settings['description'] = "%seo_description%";
				
				delete_option($psp_settings_name);
				add_option($psp_settings_name, $psp_tax_settings, "", "no");
				
			//}
		}
		//post settings
		$psp_post_settings = array();
		$psp_post_settings = get_option("psp_post_settings");
		
		//if (empty($psp_post_settings)) {
		if ( !isset($psp_post_settings['title']) || empty($psp_post_settings['title']) ) $psp_post_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_post_settings['description']) || empty($psp_post_settings['description']) ) $psp_post_settings['description'] = "%seo_description%";
			
			delete_option("psp_post_settings");
			add_option("psp_post_settings", $psp_post_settings, "", "no");
		//}
		//page settings
		$psp_page_settings = array();
		$psp_page_settings = get_option("psp_page_settings");
		
		//if (empty($psp_page_settings)) {
		if ( !isset($psp_page_settings['title']) || empty($psp_page_settings['title']) ) $psp_page_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_page_settings['description']) || empty($psp_page_settings['description']) ) $psp_page_settings['description'] = "%seo_description%";
			
			delete_option("psp_page_settings");
			add_option("psp_page_settings", $psp_page_settings, "", "no");
		//}
		//attachment settings
		$psp_attachment_settings = array();
		$psp_attachment_settings = get_option("psp_attachment_settings");
		
		//if (empty($psp_attachment_settings)) {
		if ( !isset($psp_attachment_settings['title']) || empty($psp_attachment_settings['title']) ) $psp_attachment_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_attachment_settings['description']) || empty($psp_attachment_settings['description']) ) $psp_attachment_settings['description'] = "%seo_description%";
			
			delete_option("psp_attachment_settings");
			add_option("psp_attachment_settings", $psp_attachment_settings, "", "no");
		//}
		//custom post type settings
		$cust_post_types = get_post_types( array ( '_builtin' => FALSE ) );	
		$psp_cust_posttype_settings = array();
		
		foreach($cust_post_types as $cust_post_type) {
			$psp_settings_name = "psp_".$cust_post_type."_settings";
			$psp_cust_posttype_settings = get_option($psp_settings_name);
			//if (empty($psp_cust_posttype_settings)) {
			if ( !isset($psp_cust_posttype_settings['title']) || empty($psp_cust_posttype_settings['title']) ) $psp_cust_posttype_settings['title'] = "%seo_title% %sep% %site_name%";
			if ( !isset($psp_cust_posttype_settings['description']) || empty($psp_cust_posttype_settings['description']) ) $psp_cust_posttype_settings['description'] = "%seo_description%";
				
				delete_option($psp_settings_name);
				add_option($psp_settings_name, $psp_cust_posttype_settings, "", "no");
				
			//}
		}
		//date archive settings
		$psp_date_archive_settings = array();
		$psp_date_archive_settings = get_option("psp_date_archive_settings");
		//if (empty($psp_date_archive_settings)) {
		if ( !isset($psp_date_archive_settings['title']) || empty($psp_date_archive_settings['title']) ) $psp_date_archive_settings['title'] = "%title_date% %sep% %site_name%";
		if ( !isset($psp_date_archive_settings['robots']) || empty($psp_date_archive_settings['robots']) ) $psp_date_archive_settings['robots'] = 1;
			
			delete_option("psp_date_archive_settings");
			add_option("psp_date_archive_settings", $psp_date_archive_settings, "", "no");
		//}
		//author archive settings
		$psp_author_archive_settings = array();
		$psp_author_archive_settings = get_option("psp_author_archive_settings");
		//if (empty($psp_author_archive_settings)) {
		if ( !isset($psp_author_archive_settings['title']) || empty($psp_author_archive_settings['title']) ) $psp_author_archive_settings['title'] = "%title_author% %sep% %site_name%";
		if ( !isset($psp_author_archive_settings['robots']) || empty($psp_author_archive_settings['robots']) ) $psp_author_archive_settings['robots'] = 1;
			
			delete_option("psp_author_archive_settings");
			add_option("psp_author_archive_settings", $psp_author_archive_settings, "", "no");
		//}
		//post type archive settings
	
		$psp_post_type_archive_settings = array();
		$psp_post_type_archive_settings = get_option("psp_posttype_archive_settings");
		//if (empty($psp_post_type_archive_settings)) {
		if ( !isset($psp_post_type_archive_settings['title']) || empty($psp_post_type_archive_settings['title']) ) $psp_post_type_archive_settings['title'] = "%seo_title% %sep% %site_name%";
		if ( !isset($psp_post_type_archive_settings['robots']) || empty($psp_post_type_archive_settings['robots']) ) $psp_post_type_archive_settings['robots'] = 1;
			
			delete_option("psp_posttype_archive_settings");
			add_option("psp_posttype_archive_settings", $psp_post_type_archive_settings, "", "no");
		//}
		//Home Settings
		$psp_home_settings = array();
		$psp_home_settings = get_option("psp_home_settings");
		if ( !isset($psp_home_settings['title']) || empty($psp_home_settings['title']) ) $psp_home_settings['title'] = "";
		
			delete_option("psp_home_settings");
			add_option("psp_home_settings", $psp_home_settings, "", "no");
		//	social settings
		$psp_social_settings = array();
		$psp_social_settings = get_option("psp_social_settings");
		if ( !isset($psp_social_settings['fb_site_name']) || empty($psp_social_settings['fb_site_name']) ) $psp_social_settings['fb_site_name'] = "";
		
			delete_option("psp_social_settings");
			add_option("psp_social_settings", $psp_social_settings, "", "no");
			
		//permalink settings	
		$psp_permalink_settings = array();
		$psp_permalink_settings = get_option("psp_permalink_settings");
		//if ( !empty($psp_permalink_settings) && (!isset($psp_permalink_settings['category']) || empty($psp_permalink_settings['category'])) ) $psp_permalink_settings['category'] = "";
		if ( !isset($psp_permalink_settings['category']) || empty($psp_permalink_settings['category']) ) $psp_permalink_settings['category'] = "";
		
			delete_option("psp_permalink_settings");
			add_option("psp_permalink_settings", $psp_permalink_settings, "", "no");
			
		//GA Settings
		$psp_ga_settings = array();
		$psp_ga_settings = get_option("psp_ga_settings");
		if ( !isset($psp_ga_settings['ga_tc_enabled']) || empty($psp_ga_settings['ga_tc_enabled']) ) $psp_ga_settings['ga_tc_enabled'] = "";
		//if ( !isset($psp_ga_settings['tracking_code']) || empty($psp_ga_settings['tracking_code']) ) $psp_ga_settings['tracking_code'] = "";
		delete_option("psp_ga_settings");
		add_option("psp_ga_settings", $psp_ga_settings, "", "no");
			
		//robots.txt settings	
		$psp_robotstxt_settings = array();
		$psp_robotstxt_settings = get_option("psp_robotstxt_settings");
		if ( !isset($psp_robotstxt_settings['content']) || empty($psp_robotstxt_settings['content']) ) $psp_robotstxt_settings['content'] = "";
		if ( !isset($psp_robotstxt_settings['use_virtual_robots_file']) || empty($psp_robotstxt_settings['use_virtual_robots_file']) ) $psp_robotstxt_settings['use_virtual_robots_file'] = 1;
			delete_option("psp_robotstxt_settings");
			add_option("psp_robotstxt_settings", $psp_robotstxt_settings, "", "no");
			
		//.htaccess settings	
		$psp_htaccess_settings = array();
		$psp_htaccess_settings = get_option("psp_htaccess_settings");
		if ( !isset($psp_htaccess_settings['content']) || empty($psp_htaccess_settings['content']) ) $psp_htaccess_settings['content'] = "";
		
			delete_option("psp_htaccess_settings");
			add_option("psp_htaccess_settings", $psp_htaccess_settings, "", "no");
		//breadcrumb settings	
		$psp_breadcrumb_settings = array();
		$psp_breadcrumb_settings = get_option("psp_breadcrumb_settings");
		//if (empty($psp_breadcrumb_settings)) {
		//if ( empty($psp_breadcrumb_settings['use_defaults']) ) $psp_breadcrumb_settings['use_defaults'] = 1;
		if ( !isset($psp_breadcrumb_settings['container']) || empty($psp_breadcrumb_settings['container']) ) $psp_breadcrumb_settings['container'] = "div";
		if ( !isset($psp_breadcrumb_settings['separator']) || empty($psp_breadcrumb_settings['separator']) ) $psp_breadcrumb_settings['separator'] = "&gt;";
		if ( !isset($psp_breadcrumb_settings['show_on_front']) || empty($psp_breadcrumb_settings['show_on_front']) ) $psp_breadcrumb_settings['show_on_front'] = 1;
		if ( !isset($psp_breadcrumb_settings['echo']) || empty($psp_breadcrumb_settings['echo']) ) $psp_breadcrumb_settings['echo'] = 1;
		
		
		if ( !isset($psp_breadcrumb_settings['labels']['browse']) || empty($psp_breadcrumb_settings['labels']['browse']) ) $psp_breadcrumb_settings['labels']['browse'] = "Browse";
		if ( !isset($psp_breadcrumb_settings['labels']['home']) || empty($psp_breadcrumb_settings['labels']['home']) ) $psp_breadcrumb_settings['labels']['home'] = "Home";
		if ( !isset($psp_breadcrumb_settings['labels']['error_404']) || empty($psp_breadcrumb_settings['labels']['error_404']) ) $psp_breadcrumb_settings['labels']['error_404'] = "404 Not Found";
			
			delete_option("psp_breadcrumb_settings");
			add_option("psp_breadcrumb_settings", $psp_breadcrumb_settings, "", "no");
		//}
		
	}	

	//register_deactivation_hook(__FILE__, 'psp_deactivate');
	public function psp_deactivate() {			
		$psp_settings_instance = $this->psp_settings_handle;		
		//remove_filter('category_rewrite_rules', array($psp_settings_class, 'psp_category_rewrite_rules'));	

		$cust_taxonomies = $psp_settings_instance->custom_taxonomies;		

		$psp_permalink_settings = get_option('psp_permalink_settings');
		
		if (isset($psp_permalink_settings['category']) && $psp_permalink_settings['category']) {			
			remove_filter( 'category_rewrite_rules', array( $psp_settings_instance, 'psp_category_rewrite_rules' )); 
		}			
		//remove the custom rewrite rules
		foreach($cust_taxonomies as $cust_taxonomy) {
		
			if (isset($psp_permalink_settings[$cust_taxonomy]) && $psp_permalink_settings[$cust_taxonomy]) {
				$psp_filter = $cust_taxonomy."_rewrite_rules";
				remove_filter( $psp_filter, array( $psp_settings_instance, 'psp_category_rewrite_rules' )); //remove the custom rewrite rules				
			}
		}
		
		$psp_settings_instance->psp_refresh_rewrite_rules();		
	}
	
	public function psp_db_install() {
    	global $wpdb;
    	global $psp_db_version;
    
    	$psp_redirections_tbl = $wpdb->prefix . 'psp_redirections';
		$psp_redirections_log = $wpdb->prefix . 'psp_redirections_log';
		$psp_404_log = $wpdb->prefix . "psp_404_log";
    	
    	$charset_collate = $wpdb->get_charset_collate();
        if ( get_site_option( 'psp_db_version' ) == $psp_db_version ) {
            return;
        }
        
    	if($wpdb->get_var("show tables like '$psp_redirections_tbl'") != $psp_redirections_tbl) 
    	{
    		$psp_sql = "CREATE TABLE $psp_redirections_tbl (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,				
    			source_url varchar(255) DEFAULT '' NOT NULL,
    			dest_url varchar(255) DEFAULT '' NOT NULL,
    			redir_code varchar(55) DEFAULT '' NOT NULL,
				log_redirect varchar(10) DEFAULT '' NOT NULL,
				created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    			PRIMARY KEY  (id)
    		) $charset_collate;";
    
    		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    		dbDelta( $psp_sql );
    	}
		
		if($wpdb->get_var("show tables like '$psp_redirections_log'") != $psp_redirections_log) 
    	{
    		$psp_sql = "CREATE TABLE $psp_redirections_log (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,
				source_uri varchar(255) DEFAULT '' NOT NULL,
    			source_url varchar(255) DEFAULT '' NOT NULL,
    			dest_url varchar(255) DEFAULT '' NOT NULL,
    			redir_code varchar(55) DEFAULT '' NOT NULL,
				referrer varchar(255) DEFAULT '' NOT NULL,
				user_agent varchar(255) DEFAULT '' NOT NULL, 
				ipaddress varchar(255) DEFAULT '' NOT NULL,
				created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    			PRIMARY KEY  (id)
    		) $charset_collate;";
    
    		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    		dbDelta( $psp_sql );
    	}
		
		if($wpdb->get_var("show tables like '$psp_404_log'") != $psp_404_log) 
    	{
    		$psp_sql = "CREATE TABLE $psp_404_log (
    			id mediumint(9) NOT NULL AUTO_INCREMENT,
				source_uri varchar(255) DEFAULT '' NOT NULL,
    			source_url varchar(255) DEFAULT '' NOT NULL,
				referrer varchar(255) DEFAULT '' NOT NULL,
				status varchar(20) DEFAULT '' NOT NULL,
				user_agent varchar(255) DEFAULT '' NOT NULL, 
				ipaddress varchar(255) DEFAULT '' NOT NULL,
    			total_hits mediumint(9) DEFAULT 1 NOT NULL,
    			created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
				last_logged TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    			PRIMARY KEY  (id)
    		) $charset_collate;";
    
    		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    		dbDelta( $psp_sql );
    	}
    
    	add_option( 'psp_db_version', $psp_db_version );
    	
    }
	//same as redirect_canonical in WordPress Core but redirect_guess_404_permalink() has been disabled
	//this function replaces redirect_canonical function in WordPress template_redirect hooks
	//this is done because redirect_guess_404_permalink() does not have any filters (as of WordPress 5.4) for plugin to hook into
	public function psp_redirect_canonical( $requested_url = null, $do_redirect = true ) {
    	global $wp_rewrite, $is_IIS, $wp_query, $wpdb, $wp;
    
    	if ( isset( $_SERVER['REQUEST_METHOD'] ) && ! in_array( strtoupper( $_SERVER['REQUEST_METHOD'] ), array( 'GET', 'HEAD' ) ) ) {
    		return;
    	}
    
    	// If we're not in wp-admin and the post has been published and preview nonce
    	// is non-existent or invalid then no need for preview in query.
    	if ( is_preview() && get_query_var( 'p' ) && 'publish' == get_post_status( get_query_var( 'p' ) ) ) {
    		if ( ! isset( $_GET['preview_id'] )
    			|| ! isset( $_GET['preview_nonce'] )
    			|| ! wp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . (int) $_GET['preview_id'] ) ) {
    			$wp_query->is_preview = false;
    		}
    	}
    
    	if ( is_trackback() || is_search() || is_admin() || is_preview() || is_robots() || is_favicon() || ( $is_IIS && ! iis7_supports_permalinks() ) ) {
    		return;
    	}
    
    	if ( ! $requested_url && isset( $_SERVER['HTTP_HOST'] ) ) {
    		// Build the URL in the address bar.
    		$requested_url  = is_ssl() ? 'https://' : 'http://';
    		$requested_url .= $_SERVER['HTTP_HOST'];
    		$requested_url .= $_SERVER['REQUEST_URI'];
    	}
    
    	$original = @parse_url( $requested_url );
    	if ( false === $original ) {
    		return;
    	}
    
    	$redirect     = $original;
    	$redirect_url = false;
    
    	// Notice fixing.
    	if ( ! isset( $redirect['path'] ) ) {
    		$redirect['path'] = '';
    	}
    	if ( ! isset( $redirect['query'] ) ) {
    		$redirect['query'] = '';
    	}
    
    	/*
    	 * If the original URL ended with non-breaking spaces, they were almost
    	 * certainly inserted by accident. Let's remove them, so the reader doesn't
    	 * see a 404 error with no obvious cause.
    	 */
    	$redirect['path'] = preg_replace( '|(%C2%A0)+$|i', '', $redirect['path'] );
    
    	// It's not a preview, so remove it from URL.
    	if ( get_query_var( 'preview' ) ) {
    		$redirect['query'] = remove_query_arg( 'preview', $redirect['query'] );
    	}
    
    	$id = get_query_var( 'p' );
    
    	if ( is_feed() && $id ) {
    		$redirect_url = get_post_comments_feed_link( $id, get_query_var( 'feed' ) );
    		if ( $redirect_url ) {
    			$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type', 'feed' ), $redirect_url );
    			$redirect['path']  = parse_url( $redirect_url, PHP_URL_PATH );
    		}
    	}
    
    	if ( is_singular() && 1 > $wp_query->post_count && $id ) {
    
    		$vars = $wpdb->get_results( $wpdb->prepare( "SELECT post_type, post_parent FROM $wpdb->posts WHERE ID = %d", $id ) );
    
    		if ( ! empty( $vars[0] ) ) {
    			$vars = $vars[0];
    			if ( 'revision' == $vars->post_type && $vars->post_parent > 0 ) {
    				$id = $vars->post_parent;
    			}
    
    			$redirect_url = get_permalink( $id );
    			if ( $redirect_url ) {
    				$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type' ), $redirect_url );
    			}
    		}
    	}
    
    	// These tests give us a WP-generated permalink.
    	if ( is_404() ) {
    
    		// Redirect ?page_id, ?p=, ?attachment_id= to their respective URLs.
    		$id            = max( get_query_var( 'p' ), get_query_var( 'page_id' ), get_query_var( 'attachment_id' ) );
    		$redirect_post = $id ? get_post( $id ) : false;
    		if ( $redirect_post ) {
    			$post_type_obj = get_post_type_object( $redirect_post->post_type );
    			if ( $post_type_obj->public && 'auto-draft' != $redirect_post->post_status ) {
    				$redirect_url      = get_permalink( $redirect_post );
    				$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type' ), $redirect_url );
    			}
    		}
    
    		if ( get_query_var( 'day' ) && get_query_var( 'monthnum' ) && get_query_var( 'year' ) ) {
    			$year  = get_query_var( 'year' );
    			$month = get_query_var( 'monthnum' );
    			$day   = get_query_var( 'day' );
    			$date  = sprintf( '%04d-%02d-%02d', $year, $month, $day );
    			if ( ! wp_checkdate( $month, $day, $year, $date ) ) {
    				$redirect_url      = get_month_link( $year, $month );
    				$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'year', 'monthnum', 'day' ), $redirect_url );
    			}
    		} elseif ( get_query_var( 'monthnum' ) && get_query_var( 'year' ) && 12 < get_query_var( 'monthnum' ) ) {
    			$redirect_url      = get_year_link( get_query_var( 'year' ) );
    			$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'year', 'monthnum' ), $redirect_url );
    		}
            /***commenting guessing***
    		if ( ! $redirect_url ) {
    			$redirect_url = redirect_guess_404_permalink();
    			if ( $redirect_url ) {
    				$redirect['query'] = _remove_qs_args_if_not_in_url( $redirect['query'], array( 'page', 'feed', 'p', 'page_id', 'attachment_id', 'pagename', 'name', 'post_type' ), $redirect_url );
    			}
    		}
            ****/
    		if ( get_query_var( 'page' ) && $wp_query->post &&
    			false !== strpos( $wp_query->post->post_content, '<!--nextpage-->' ) ) {
    			$redirect['path']  = rtrim( $redirect['path'], (int) get_query_var( 'page' ) . '/' );
    			$redirect['query'] = remove_query_arg( 'page', $redirect['query'] );
    			$redirect_url      = get_permalink( $wp_query->post->ID );
    		}
    	} elseif ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) {
    		// Rewriting of old ?p=X, ?m=2004, ?m=200401, ?m=20040101.
    		if ( is_attachment() &&
    			! array_diff( array_keys( $wp->query_vars ), array( 'attachment', 'attachment_id' ) ) &&
    			! $redirect_url ) {
    			if ( ! empty( $_GET['attachment_id'] ) ) {
    				$redirect_url = get_attachment_link( get_query_var( 'attachment_id' ) );
    				if ( $redirect_url ) {
    					$redirect['query'] = remove_query_arg( 'attachment_id', $redirect['query'] );
    				}
    			} else {
    				$redirect_url = get_attachment_link();
    			}
    		} elseif ( is_single() && ! empty( $_GET['p'] ) && ! $redirect_url ) {
    			$redirect_url = get_permalink( get_query_var( 'p' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( array( 'p', 'post_type' ), $redirect['query'] );
    			}
    		} elseif ( is_single() && ! empty( $_GET['name'] ) && ! $redirect_url ) {
    			$redirect_url = get_permalink( $wp_query->get_queried_object_id() );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( 'name', $redirect['query'] );
    			}
    		} elseif ( is_page() && ! empty( $_GET['page_id'] ) && ! $redirect_url ) {
    			$redirect_url = get_permalink( get_query_var( 'page_id' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( 'page_id', $redirect['query'] );
    			}
    		} elseif ( is_page() && ! is_feed() && 'page' == get_option( 'show_on_front' ) && get_queried_object_id() == get_option( 'page_on_front' ) && ! $redirect_url ) {
    			$redirect_url = home_url( '/' );
    		} elseif ( is_home() && ! empty( $_GET['page_id'] ) && 'page' == get_option( 'show_on_front' ) && get_query_var( 'page_id' ) == get_option( 'page_for_posts' ) && ! $redirect_url ) {
    			$redirect_url = get_permalink( get_option( 'page_for_posts' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( 'page_id', $redirect['query'] );
    			}
    		} elseif ( ! empty( $_GET['m'] ) && ( is_year() || is_month() || is_day() ) ) {
    			$m = get_query_var( 'm' );
    			switch ( strlen( $m ) ) {
    				case 4: // Yearly.
    					$redirect_url = get_year_link( $m );
    					break;
    				case 6: // Monthly.
    					$redirect_url = get_month_link( substr( $m, 0, 4 ), substr( $m, 4, 2 ) );
    					break;
    				case 8: // Daily.
    					$redirect_url = get_day_link( substr( $m, 0, 4 ), substr( $m, 4, 2 ), substr( $m, 6, 2 ) );
    					break;
    			}
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( 'm', $redirect['query'] );
    			}
    			// Now moving on to non ?m=X year/month/day links.
    		} elseif ( is_day() && get_query_var( 'year' ) && get_query_var( 'monthnum' ) && ! empty( $_GET['day'] ) ) {
    			$redirect_url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( array( 'year', 'monthnum', 'day' ), $redirect['query'] );
    			}
    		} elseif ( is_month() && get_query_var( 'year' ) && ! empty( $_GET['monthnum'] ) ) {
    			$redirect_url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( array( 'year', 'monthnum' ), $redirect['query'] );
    			}
    		} elseif ( is_year() && ! empty( $_GET['year'] ) ) {
    			$redirect_url = get_year_link( get_query_var( 'year' ) );
    			if ( $redirect_url ) {
    				$redirect['query'] = remove_query_arg( 'year', $redirect['query'] );
    			}
    		} elseif ( is_author() && ! empty( $_GET['author'] ) && preg_match( '|^[0-9]+$|', $_GET['author'] ) ) {
    			$author = get_userdata( get_query_var( 'author' ) );
    			if ( ( false !== $author ) && $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE $wpdb->posts.post_author = %d AND $wpdb->posts.post_status = 'publish' LIMIT 1", $author->ID ) ) ) {
    				$redirect_url = get_author_posts_url( $author->ID, $author->user_nicename );
    				if ( $redirect_url ) {
    					$redirect['query'] = remove_query_arg( 'author', $redirect['query'] );
    				}
    			}
    		} elseif ( is_category() || is_tag() || is_tax() ) { // Terms (tags/categories).
    
    			$term_count = 0;
    			foreach ( $wp_query->tax_query->queried_terms as $tax_query ) {
    				$term_count += count( $tax_query['terms'] );
    			}
    
    			$obj = $wp_query->get_queried_object();
    			if ( $term_count <= 1 && ! empty( $obj->term_id ) ) {
    				$tax_url = get_term_link( (int) $obj->term_id, $obj->taxonomy );
    				if ( $tax_url && ! is_wp_error( $tax_url ) ) {
    					if ( ! empty( $redirect['query'] ) ) {
    						// Strip taxonomy query vars off the URL.
    						$qv_remove = array( 'term', 'taxonomy' );
    						if ( is_category() ) {
    							$qv_remove[] = 'category_name';
    							$qv_remove[] = 'cat';
    						} elseif ( is_tag() ) {
    							$qv_remove[] = 'tag';
    							$qv_remove[] = 'tag_id';
    						} else {
    							// Custom taxonomies will have a custom query var, remove those too.
    							$tax_obj = get_taxonomy( $obj->taxonomy );
    							if ( false !== $tax_obj->query_var ) {
    								$qv_remove[] = $tax_obj->query_var;
    							}
    						}
    
    						$rewrite_vars = array_diff( array_keys( $wp_query->query ), array_keys( $_GET ) );
    
    						// Check to see if all the query vars are coming from the rewrite, none are set via $_GET.
    						if ( ! array_diff( $rewrite_vars, array_keys( $_GET ) ) ) {
    							// Remove all of the per-tax query vars.
    							$redirect['query'] = remove_query_arg( $qv_remove, $redirect['query'] );
    
    							// Create the destination URL for this taxonomy.
    							$tax_url = parse_url( $tax_url );
    							if ( ! empty( $tax_url['query'] ) ) {
    								// Taxonomy accessible via ?taxonomy=...&term=... or any custom query var.
    								parse_str( $tax_url['query'], $query_vars );
    								$redirect['query'] = add_query_arg( $query_vars, $redirect['query'] );
    							} else {
    								// Taxonomy is accessible via a "pretty URL".
    								$redirect['path'] = $tax_url['path'];
    							}
    						} else {
    							// Some query vars are set via $_GET. Unset those from $_GET that exist via the rewrite.
    							foreach ( $qv_remove as $_qv ) {
    								if ( isset( $rewrite_vars[ $_qv ] ) ) {
    									$redirect['query'] = remove_query_arg( $_qv, $redirect['query'] );
    								}
    							}
    						}
    					}
    				}
    			}
    		} elseif ( is_single() && strpos( $wp_rewrite->permalink_structure, '%category%' ) !== false ) {
    			$cat = get_query_var( 'category_name' );
    			if ( $cat ) {
    				$category = get_category_by_path( $cat );
    				if ( ( ! $category || is_wp_error( $category ) ) || ! has_term( $category->term_id, 'category', $wp_query->get_queried_object_id() ) ) {
    					$redirect_url = get_permalink( $wp_query->get_queried_object_id() );
    				}
    			}
    		}
    
    			// Post paging.
    		if ( is_singular() && get_query_var( 'page' ) ) {
    			if ( ! $redirect_url ) {
    				$redirect_url = get_permalink( get_queried_object_id() );
    			}
    
    			$page = get_query_var( 'page' );
    			if ( $page > 1 ) {
    				if ( is_front_page() ) {
    					$redirect_url = trailingslashit( $redirect_url ) . user_trailingslashit( "$wp_rewrite->pagination_base/$page", 'paged' );
    				} else {
    					$redirect_url = trailingslashit( $redirect_url ) . user_trailingslashit( $page, 'single_paged' );
    				}
    			}
    				$redirect['query'] = remove_query_arg( 'page', $redirect['query'] );
    		}
    
    			// Paging and feeds.
    		if ( get_query_var( 'paged' ) || is_feed() || get_query_var( 'cpage' ) ) {
    			while ( preg_match( "#/$wp_rewrite->pagination_base/?[0-9]+?(/+)?$#", $redirect['path'] ) || preg_match( '#/(comments/?)?(feed|rss|rdf|atom|rss2)(/+)?$#', $redirect['path'] ) || preg_match( "#/{$wp_rewrite->comments_pagination_base}-[0-9]+(/+)?$#", $redirect['path'] ) ) {
    				// Strip off paging and feed.
    				$redirect['path'] = preg_replace( "#/$wp_rewrite->pagination_base/?[0-9]+?(/+)?$#", '/', $redirect['path'] ); // Strip off any existing paging.
    				$redirect['path'] = preg_replace( '#/(comments/?)?(feed|rss2?|rdf|atom)(/+|$)#', '/', $redirect['path'] ); // Strip off feed endings.
    				$redirect['path'] = preg_replace( "#/{$wp_rewrite->comments_pagination_base}-[0-9]+?(/+)?$#", '/', $redirect['path'] ); // Strip off any existing comment paging.
    			}
    
    			$addl_path = '';
    			if ( is_feed() && in_array( get_query_var( 'feed' ), $wp_rewrite->feeds ) ) {
    				$addl_path = ! empty( $addl_path ) ? trailingslashit( $addl_path ) : '';
    				if ( ! is_singular() && get_query_var( 'withcomments' ) ) {
    					$addl_path .= 'comments/';
    				}
    				if ( ( 'rss' == get_default_feed() && 'feed' == get_query_var( 'feed' ) ) || 'rss' == get_query_var( 'feed' ) ) {
    					$addl_path .= user_trailingslashit( 'feed/' . ( ( get_default_feed() == 'rss2' ) ? '' : 'rss2' ), 'feed' );
    				} else {
    					$addl_path .= user_trailingslashit( 'feed/' . ( ( get_default_feed() == get_query_var( 'feed' ) || 'feed' == get_query_var( 'feed' ) ) ? '' : get_query_var( 'feed' ) ), 'feed' );
    				}
    				$redirect['query'] = remove_query_arg( 'feed', $redirect['query'] );
    			} elseif ( is_feed() && 'old' == get_query_var( 'feed' ) ) {
    				$old_feed_files = array(
    					'wp-atom.php'         => 'atom',
    					'wp-commentsrss2.php' => 'comments_rss2',
    					'wp-feed.php'         => get_default_feed(),
    					'wp-rdf.php'          => 'rdf',
    					'wp-rss.php'          => 'rss2',
    					'wp-rss2.php'         => 'rss2',
    				);
    				if ( isset( $old_feed_files[ basename( $redirect['path'] ) ] ) ) {
    					$redirect_url = get_feed_link( $old_feed_files[ basename( $redirect['path'] ) ] );
    					wp_redirect( $redirect_url, 301 );
    					die();
    				}
    			}
    
    			if ( get_query_var( 'paged' ) > 0 ) {
    				$paged             = get_query_var( 'paged' );
    				$redirect['query'] = remove_query_arg( 'paged', $redirect['query'] );
    				if ( ! is_feed() ) {
    					if ( $paged > 1 && ! is_single() ) {
    						$addl_path = ( ! empty( $addl_path ) ? trailingslashit( $addl_path ) : '' ) . user_trailingslashit( "$wp_rewrite->pagination_base/$paged", 'paged' );
    					} elseif ( ! is_single() ) {
    						$addl_path = ! empty( $addl_path ) ? trailingslashit( $addl_path ) : '';
    					}
    				} elseif ( $paged > 1 ) {
    					$redirect['query'] = add_query_arg( 'paged', $paged, $redirect['query'] );
    				}
    			}
    
    			if ( get_option( 'page_comments' ) && (
    			( 'newest' == get_option( 'default_comments_page' ) && get_query_var( 'cpage' ) > 0 ) ||
    			( 'newest' != get_option( 'default_comments_page' ) && get_query_var( 'cpage' ) > 1 )
    			) ) {
    				$addl_path         = ( ! empty( $addl_path ) ? trailingslashit( $addl_path ) : '' ) . user_trailingslashit( $wp_rewrite->comments_pagination_base . '-' . get_query_var( 'cpage' ), 'commentpaged' );
    				$redirect['query'] = remove_query_arg( 'cpage', $redirect['query'] );
    			}
    
    			$redirect['path'] = user_trailingslashit( preg_replace( '|/' . preg_quote( $wp_rewrite->index, '|' ) . '/?$|', '/', $redirect['path'] ) ); // Strip off trailing /index.php/.
    			if ( ! empty( $addl_path ) && $wp_rewrite->using_index_permalinks() && strpos( $redirect['path'], '/' . $wp_rewrite->index . '/' ) === false ) {
    				$redirect['path'] = trailingslashit( $redirect['path'] ) . $wp_rewrite->index . '/';
    			}
    			if ( ! empty( $addl_path ) ) {
    				$redirect['path'] = trailingslashit( $redirect['path'] ) . $addl_path;
    			}
    			$redirect_url = $redirect['scheme'] . '://' . $redirect['host'] . $redirect['path'];
    		}
    
    		if ( 'wp-register.php' == basename( $redirect['path'] ) ) {
    			if ( is_multisite() ) {
    				/** This filter is documented in wp-login.php */
    				$redirect_url = apply_filters( 'wp_signup_location', network_site_url( 'wp-signup.php' ) );
    			} else {
    				$redirect_url = wp_registration_url();
    			}
    
    			wp_redirect( $redirect_url, 301 );
    			die();
    		}
    	}
    
    	// Tack on any additional query vars.
    	$redirect['query'] = preg_replace( '#^\??&*?#', '', $redirect['query'] );
    	if ( $redirect_url && ! empty( $redirect['query'] ) ) {
    		parse_str( $redirect['query'], $_parsed_query );
    		$redirect = @parse_url( $redirect_url );
    
    		if ( ! empty( $_parsed_query['name'] ) && ! empty( $redirect['query'] ) ) {
    			parse_str( $redirect['query'], $_parsed_redirect_query );
    
    			if ( empty( $_parsed_redirect_query['name'] ) ) {
    				unset( $_parsed_query['name'] );
    			}
    		}
    
    		$_parsed_query = array_combine(
    			rawurlencode_deep( array_keys( $_parsed_query ) ),
    			rawurlencode_deep( array_values( $_parsed_query ) )
    		);
    		$redirect_url  = add_query_arg( $_parsed_query, $redirect_url );
    	}
    
    	if ( $redirect_url ) {
    		$redirect = @parse_url( $redirect_url );
    	}
    
    	// www.example.com vs. example.com
    	$user_home = @parse_url( home_url() );
    	if ( ! empty( $user_home['host'] ) ) {
    		$redirect['host'] = $user_home['host'];
    	}
    	if ( empty( $user_home['path'] ) ) {
    		$user_home['path'] = '/';
    	}
    
    	// Handle ports.
    	if ( ! empty( $user_home['port'] ) ) {
    		$redirect['port'] = $user_home['port'];
    	} else {
    		unset( $redirect['port'] );
    	}
    
    	// Trailing /index.php.
    	$redirect['path'] = preg_replace( '|/' . preg_quote( $wp_rewrite->index, '|' ) . '/*?$|', '/', $redirect['path'] );
    
    	$punctuation_pattern = implode(
    		'|',
    		array_map(
    			'preg_quote',
    			array(
    				' ',
    				'%20',  // Space.
    				'!',
    				'%21',  // Exclamation mark.
    				'"',
    				'%22',  // Double quote.
    				"'",
    				'%27',  // Single quote.
    				'(',
    				'%28',  // Opening bracket.
    				')',
    				'%29',  // Closing bracket.
    				',',
    				'%2C',  // Comma.
    				'.',
    				'%2E',  // Period.
    				';',
    				'%3B',  // Semicolon.
    				'{',
    				'%7B',  // Opening curly bracket.
    				'}',
    				'%7D',  // Closing curly bracket.
    				'%E2%80%9C', // Opening curly quote.
    				'%E2%80%9D', // Closing curly quote.
    			)
    		)
    	);
    
    	// Remove trailing spaces and end punctuation from the path.
    	$redirect['path'] = preg_replace( "#($punctuation_pattern)+$#", '', $redirect['path'] );
    
    	if ( ! empty( $redirect['query'] ) ) {
    		// Remove trailing spaces and end punctuation from certain terminating query string args.
    		$redirect['query'] = preg_replace( "#((^|&)(p|page_id|cat|tag)=[^&]*?)($punctuation_pattern)+$#", '$1', $redirect['query'] );
    
    		// Clean up empty query strings.
    		$redirect['query'] = trim( preg_replace( '#(^|&)(p|page_id|cat|tag)=?(&|$)#', '&', $redirect['query'] ), '&' );
    
    		// Redirect obsolete feeds.
    		$redirect['query'] = preg_replace( '#(^|&)feed=rss(&|$)#', '$1feed=rss2$2', $redirect['query'] );
    
    		// Remove redundant leading ampersands.
    		$redirect['query'] = preg_replace( '#^\??&*?#', '', $redirect['query'] );
    	}
    
    	// Strip /index.php/ when we're not using PATHINFO permalinks.
    	if ( ! $wp_rewrite->using_index_permalinks() ) {
    		$redirect['path'] = str_replace( '/' . $wp_rewrite->index . '/', '/', $redirect['path'] );
    	}
    
    	// Trailing slashes.
    	if ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() && ! is_404() && ( ! is_front_page() || ( is_front_page() && ( get_query_var( 'paged' ) > 1 ) ) ) ) {
    		$user_ts_type = '';
    		if ( get_query_var( 'paged' ) > 0 ) {
    			$user_ts_type = 'paged';
    		} else {
    			foreach ( array( 'single', 'category', 'page', 'day', 'month', 'year', 'home' ) as $type ) {
    				$func = 'is_' . $type;
    				if ( call_user_func( $func ) ) {
    					$user_ts_type = $type;
    					break;
    				}
    			}
    		}
    		$redirect['path'] = user_trailingslashit( $redirect['path'], $user_ts_type );
    	} elseif ( is_front_page() ) {
    		$redirect['path'] = trailingslashit( $redirect['path'] );
    	}
    
    	// Strip multiple slashes out of the URL.
    	if ( strpos( $redirect['path'], '//' ) > -1 ) {
    		$redirect['path'] = preg_replace( '|/+|', '/', $redirect['path'] );
    	}
    
    	// Always trailing slash the Front Page URL.
    	if ( trailingslashit( $redirect['path'] ) == trailingslashit( $user_home['path'] ) ) {
    		$redirect['path'] = trailingslashit( $redirect['path'] );
    	}
    
    	// Ignore differences in host capitalization, as this can lead to infinite redirects.
    	// Only redirect no-www <=> yes-www.
    	if ( strtolower( $original['host'] ) == strtolower( $redirect['host'] ) ||
    		( strtolower( $original['host'] ) != 'www.' . strtolower( $redirect['host'] ) && 'www.' . strtolower( $original['host'] ) != strtolower( $redirect['host'] ) ) ) {
    		$redirect['host'] = $original['host'];
    	}
    
    	$compare_original = array( $original['host'], $original['path'] );
    
    	if ( ! empty( $original['port'] ) ) {
    		$compare_original[] = $original['port'];
    	}
    
    	if ( ! empty( $original['query'] ) ) {
    		$compare_original[] = $original['query'];
    	}
    
    	$compare_redirect = array( $redirect['host'], $redirect['path'] );
    
    	if ( ! empty( $redirect['port'] ) ) {
    		$compare_redirect[] = $redirect['port'];
    	}
    
    	if ( ! empty( $redirect['query'] ) ) {
    		$compare_redirect[] = $redirect['query'];
    	}
    
    	if ( $compare_original !== $compare_redirect ) {
    		$redirect_url = $redirect['scheme'] . '://' . $redirect['host'];
    		if ( ! empty( $redirect['port'] ) ) {
    			$redirect_url .= ':' . $redirect['port'];
    		}
    		$redirect_url .= $redirect['path'];
    		if ( ! empty( $redirect['query'] ) ) {
    			$redirect_url .= '?' . $redirect['query'];
    		}
    	}
    
    	if ( ! $redirect_url || $redirect_url == $requested_url ) {
    		return;
    	}
    
    	// Hex encoded octets are case-insensitive.
    	if ( false !== strpos( $requested_url, '%' ) ) {
    		if ( ! function_exists( 'lowercase_octets' ) ) {
    			/**
    			 * Converts the first hex-encoded octet match to lowercase.
    			 *
    			 * @since 3.1.0
    			 * @ignore
    			 *
    			 * @param array $matches Hex-encoded octet matches for the requested URL.
    			 * @return string Lowercased version of the first match.
    			 */
    			function lowercase_octets( $matches ) {
    				return strtolower( $matches[0] );
    			}
    		}
    		$requested_url = preg_replace_callback( '|%[a-fA-F0-9][a-fA-F0-9]|', 'lowercase_octets', $requested_url );
    	}
    
    	/**
    	 * Filters the canonical redirect URL.
    	 *
    	 * Returning false to this filter will cancel the redirect.
    	 *
    	 * @since 2.3.0
    	 *
    	 * @param string $redirect_url  The redirect URL.
    	 * @param string $requested_url The requested URL.
    	 */
    	$redirect_url = apply_filters( 'redirect_canonical', $redirect_url, $requested_url );
    
    	// Yes, again -- in case the filter aborted the request.
    	if ( ! $redirect_url || strip_fragment_from_url( $redirect_url ) == strip_fragment_from_url( $requested_url ) ) {
    		return;
    	}
    
    	if ( $do_redirect ) {
    		// Protect against chained redirects.
    		if ( ! redirect_canonical( $redirect_url, false ) ) {
    			wp_redirect( $redirect_url, 301 );
    			exit();
    		} else {
    			// Debug.
    			// die("1: $redirect_url<br />2: " . redirect_canonical( $redirect_url, false ) );
    			return;
    		}
    	} else {
    		return $redirect_url;
    	}
    }

	public function psp_loaded_filter() {
        add_filter('status_header', array($this->psp_redirect_instance, 'psp_log_404'), 10, 4);
    }
}
?>