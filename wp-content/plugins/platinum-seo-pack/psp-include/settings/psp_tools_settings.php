<?php

/*
Plugin Name: Techblissonline Platinum SEO and Social Pack
Description: Tools management class
Text Domain: platinum-seo-pack 
Plugin URI: https://techblissonline.com/platinum-wordpress-seo-plugin/
Author: Rajesh - Techblissonline
Author URI: https://techblissonline.com/
*/

class PspToolSettings extends PspSettings {	
	 
	private static $obj_handle = null;	
	
	private $psp_helper;
	private $psp_settings_instance;
	private $sitename;
	private $sitedescription;
	
	private $plugin_settings_tabs = array();
	 
	private $psp_robots_settings_group = 'psp_robotstxt';
    private $psp_htaccess_settings_group = 'psp_htaccess';	
	private $psp_ga_settings_group = 'psp_analytics';
	
	protected $psp_plugin_options_key = 'psp-tools-by-techblissonline';
	private $psp_settings_tabs = array();

	public $robotstxt_file = '';
	public $htaccess_file  = '';
	public $sitemap_file  = '';
	
	public static function get_instance() {
	
		if ( null == self::$obj_handle ) {
			self::$obj_handle = new self;
		}
	
		return self::$obj_handle;
	
	} // end get_instance;	
	
	function __construct() {

		$psp_helper_instance = PspHelper::get_instance();		
		$this->psp_helper = $psp_helper_instance;
		
		//$psp_settings_instance = PspSettings::get_instance();		
		//$this->psp_settings_instance = $psp_settings_instance;
		
		$this->sitename = $psp_helper_instance->get_sitename();
		
		$this->psp_settings_tabs[$this->psp_ga_settings_group] = 'GA Tracking Code Editor';
		$this->psp_settings_tabs[$this->psp_robots_settings_group] = 'Robots.txt Editor';
		$this->psp_settings_tabs[$this->psp_htaccess_settings_group] = '.htaccess Editor';		
		
		add_action( 'admin_init', array( &$this, 'psp_tools_settings_init' ) );
		//add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
	}
	
	function psp_tools_settings_init() {		
		
		$tab = isset( $_GET['psptools'] ) ? sanitize_key($_GET['psptools']) : $this->psp_ga_settings_group;
		
		wp_enqueue_style("psp-settings-css", plugins_url( '/css/psp-settings.css', __FILE__ ));
		
		$this->robotstxt_file = get_home_path() . 'robots.txt';
		$this->htaccess_file  = get_home_path() . '.htaccess';
		$this->sitemap_file  = get_home_path() . 'sitemap.xml';
		
		$this->register_ga_settings();
		$this->register_robotstxt_settings();		
		$this->register_htaccess_settings();
		
	}
	
	/*
	 * Registers the google analytics tracking code and appends the
	 * key to the settings tabs array.
	 */
	private function register_ga_settings() {
		$this->psp_settings_tabs[$this->psp_ga_settings_group] = 'GA Tracking Code Editor';		
		$psp_ga_settings_name = "psp_ga_settings";		
		
		$psp_ga_settings = get_option($psp_ga_settings_name);					
		
		//register
		register_setting( $this->psp_ga_settings_group, $psp_ga_settings_name, array( &$this, 'sanitize_ga_settings' ) );
		//add Section
		add_settings_section( 'psp_section_ga', esc_html__('Google analytics Tracking', 'platinum-seo-pack' ), array( &$this, 'section_ga_desc' ), $this->psp_ga_settings_group );		

		//add fields	
		//Enable Google analytics tracking code addition by this plugin.
		$psp_ga_enable_field     = array (
            'label_for' 	=> 'psp_ga_enable',
            'option_name'   => $psp_ga_settings_name.'[ga_tc_enabled]',
			'option_value'  => isset($psp_ga_settings['ga_tc_enabled']) ? esc_attr($psp_ga_settings['ga_tc_enabled']) : '',
			'checkbox_label' => esc_html__( 'Yes', 'platinum-seo-pack' ),
			'option_description' => esc_html__( 'Check to add Google analytics Tracking Code with this plugin. If this is not checked, trscking code will not be added by this plugin.', 'platinum-seo-pack' ),
        );
		
		$psp_ga_enable_field_id = 'psp_ga_enable';		
		$psp_ga_enable_field_title = esc_html__('Add Tracking Code: ', 'platinum-seo-pack');	
		
		add_settings_field( $psp_ga_enable_field_id, $psp_ga_enable_field_title, array( &$this, 'psp_add_field_checkbox' ), $this->psp_ga_settings_group, 'psp_section_ga',  $psp_ga_enable_field);
		
		//tracking code entry textarea
		$psp_ga_tracking_code_field     = array (
            'label_for' 	=> 'psp_ga_tracking_code_id',
            'option_name'   => $psp_ga_settings_name.'[tracking_code]',
			'option_value'  => isset($psp_ga_settings['tracking_code']) ? html_entity_decode( $psp_ga_settings['tracking_code'], ENT_QUOTES) : '',
			'option_description'  => esc_html__( 'Here you may enter the google analytics tracking code for adding it across all pages of the site.', 'platinum-seo-pack' ),
        );		
		
		add_settings_field( 'psp_ga_tracking_code_id', esc_html__( 'Tracking Code:', 'platinum-seo-pack' ), array( &$this, 'psp_add_field_textarea_js' ), $this->psp_ga_settings_group, 'psp_section_ga', $psp_ga_tracking_code_field );
		
	}	
	
	function section_ga_desc() {echo '';}	
		
	function sanitize_ga_settings($settings) {
	     if ( isset( $settings['ga_tc_enabled'] ) ) {
			$settings['ga_tc_enabled'] = !is_null(filter_var($settings['ga_tc_enabled'],FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)) ? $settings['ga_tc_enabled'] : '';	
		} else {
		    $settings['ga_tc_enabled'] = '';
		}
		
		if ( isset( $settings['tracking_code'] ) ) {
    		$allowed_html = array(
    			'script' => array(
    				'src' => array(),
    				'async' => array(),
    			),    
    		);

		    $settings['tracking_code'] = wp_kses($settings['tracking_code'], $allowed_html);
		    $settings['tracking_code'] = sanitize_textarea_field(htmlentities ($settings['tracking_code']) );
			//$settings['tracking_code'] = base64_encode( $settings['tracking_code'] );
		}
		
		return $settings;
	}
	
	/*
	 * Registers the Robots.txt editor settings and appends the
	 * key to the plugin settings tabs array.
	 */
	private function register_robotstxt_settings() {
		$this->psp_settings_tabs[$this->psp_robots_settings_group] = 'Robots.txt Editor';		
		$psp_robotstxt_settings_name = "psp_robotstxt_settings";		
		$disabled = 0;
		$option_description = "";
		$psp_tab = isset( $_GET['psptools'] ) ? sanitize_key($_GET['psptools']) : '';
		$psp_admin_page = isset( $_GET['page'] ) ? sanitize_key($_GET['page']) : '';
		//$psp_settings_updated = isset( $_GET['settings-updated'] ) ? sanitize_key($_GET['settings-updated']) : '';
		
		$psp_robotstxt_settings = get_option($psp_robotstxt_settings_name);
		
		$robotstxt_content = "";
		
		$robotstxt_content = $this->getDefaultRobots();
		
		//register
		register_setting( $this->psp_robots_settings_group, $psp_robotstxt_settings_name, array( &$this, 'sanitize_robotstxt_settings' ) );
		//add Section
		add_settings_section( 'psp_section_robotstxt', esc_html__('Robots.txt Editor', 'platinum-seo-pack' ), array( &$this, 'section_robotstxt_desc' ), $this->psp_robots_settings_group );

		//add fields		
		
		$robotstxt_file    = $this->robotstxt_file; 
		$use_virtual_robots_file = isset($psp_robotstxt_settings['use_virtual_robots_file']) ? esc_attr($psp_robotstxt_settings['use_virtual_robots_file']) : '';
		
		if ( ! file_exists( $robotstxt_file )) { 			
			if(( isset($_GET['settings-updated']) && true == sanitize_key($_GET['settings-updated'])) && ($psp_admin_page == 'psp-tools-by-techblissonline' ) && ($psp_tab == $this->psp_robots_settings_group ) && !$use_virtual_robots_file){
				//echo "do nothing";
				add_settings_error('psp_robotstxt_settings',  'use_virtual_robots_file', 'A physical robots.txt file has been created,', 'updated');
			} else {
				//virtual robots.txt file
				$virtual_robots_field     = array (
					'label_for' 	=> 'psp_virtual_robots_id',
					'option_name'   => $psp_robotstxt_settings_name.'[use_virtual_robots_file]',
					'option_value'  => $use_virtual_robots_file,
					'checkbox_label' => esc_html__( 'Yes, use a virtual robots.txt file', 'platinum-seo-pack' ),
					'option_description'  => esc_html__( 'Checking this will not create a physical robots.txt file but your robots.txt content will be visible to all visitors including search engine bots when they try to access the robots.txt file in the root. Even if you keep this unchecked, a physical robots.txt file will be created by Techblissonline platinum seo when you hit the "Save Settings" button.However, This will happen only if the file is writeable to root. If the file is not writeable to root, the content that you see here will be presented as a virtual rotots.txt file & this is done by wordpress by default.', 'platinum-seo-pack' ),	
				);			
					
				//$virtual_robots_field_id = 'psp_'.$setting_name.'_use_virtual_robots_file';		
				$virtual_robots_field_title = esc_html__( 'Do you want to use the virtual robots.txt file created by wordpress? ', 'platinum-seo-pack' );	
				
				add_settings_field( 'psp_virtual_robots_id', $virtual_robots_field_title, array( &$this, 'psp_add_field_checkbox' ), $this->psp_robots_settings_group, 'psp_section_robotstxt', $virtual_robots_field );
			}
		} else {
			
			//if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == true){
				//echo "do nothing";
			//} else {
		
				$robotstxt_file_handle = fopen( $robotstxt_file, 'r' );

				$content = '';
				if ( filesize( $robotstxt_file ) > 0 ) {
					$content = fread( $robotstxt_file_handle, filesize( $robotstxt_file ) );
				}
				$robotstxt_content = esc_textarea( $content );
				//$psp_robotstxt_settings['content'] = esc_textarea( $content );
				fclose( $robotstxt_file_handle );
			}
			
			$option_description = "";
			if ( ! is_writable( $robotstxt_file ) ) {
				$disabled = 1;
				$option_description = esc_html__( 'Robots.txt file exists in the root but it is not writeable. Make sure that it is writeable for you to write into it here.', 'platinum-seo-pack' );
			}
			
		//}		
		
		$content_field     = array (
            'label_for' 	=> 'psp_robotstxt_content',
            'option_name'   => $psp_robotstxt_settings_name.'[content]',
			'option_value'  => $robotstxt_content,
			'disabled'  => $disabled,
			'option_description'  => $option_description,
        );
		
		add_settings_field( 'psp_robotstxt_content', '<a href="'.home_url().'/robots.txt">'.esc_html__('Robots.txt Content: ', 'platinum-seo-pack').'</a>', array( &$this, 'psp_add_field_textarea' ), $this->psp_robots_settings_group, 'psp_section_robotstxt', $content_field );		
		
	}
	
	function sanitize_robotstxt_settings($settings) {
	
		if ( isset( $settings['use_virtual_robots_file'] ) ) {
			$settings['use_virtual_robots_file'] = !is_null(filter_var($settings['use_virtual_robots_file'],FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)) ? $settings['use_virtual_robots_file'] : '';	
		}
		
		if( isset( $settings['content'] ) ) $settings['content'] = sanitize_textarea_field( $settings['content'] );	
		
		return $settings;
	}
	
	function section_robotstxt_desc() {
		$robotstxt_file    = $this->robotstxt_file; //;		
		if ( ! file_exists( $robotstxt_file ) ) {			
			echo '<p style="color: orange">'.esc_html__('A physical robots.txt file does not exist in the root.', 'platinum-seo-pack') . '</p>';			
		} else {
			if ( ! is_writable( $robotstxt_file ) ) {
				echo '<p style="color: orange">'.esc_html__('A physical robots.txt file exists in the root but it is not writeable. Ensure that it is writeable for you to edit it here.', 'platinum-seo-pack') . '</p>';
			}
		}
	}
	
	/**
     * Get the default robots.txt content.  This is copied as is from
     * WP's `do_robots` function
     * 
     * @since   1.0
     * @access  protected
     * @uses    get_option
     * @return  string The default robots.txt content
     */
    protected function getDefaultRobots() {
    
        $public = get_option('blog_public');

        $output = "User-agent: *\n";
        if ('0' == $public) {
            $output .= "Disallow: /\n";
        } else {
            $home_url =  site_url();
            $site_url = parse_url( site_url() );
			$path     = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
            $output  .= "Disallow: $path/wp-admin/\n";
            $output  .= "Allow: $path/wp-admin/admin-ajax.php\n";
            //$output  .= "\nSitemap: $home_url/sitemap.xml\n"; 
            $sitemap_file    = $this->sitemap_file; 
		    if ( file_exists( $sitemap_file )) { 
                $output  .= "\nSitemap: $home_url/sitemap.xml\n"; 
		    }
        }
        
        $psp_robotstxt_settings = get_option('psp_robotstxt_settings');
		$psp_virtual_robots_content = isset($psp_robotstxt_settings['content']) ? stripcslashes(esc_textarea($psp_robotstxt_settings['content'])) : '';
		$use_virtual_robots_file = isset($psp_robotstxt_settings['use_virtual_robots_file']) ? esc_attr($psp_robotstxt_settings['use_virtual_robots_file']) : '';
		
		$robotstxt_file    = $this->robotstxt_file; 
		
		if (file_exists($robotstxt_file)) $use_virtual_robots_file = '';
		
		if (!empty($psp_virtual_robots_content) && $use_virtual_robots_file) {
			$output = stripcslashes(esc_textarea(strip_tags($psp_virtual_robots_content)));
		}
			
        return $output;
    }
	
	public function psp_filter_virtual_robots($robots_content, $public)
	{
		if ('0' == $public) {
			//$robots_content = getDefaultRobots();
        } else {
			$psp_robotstxt_settings = get_option('psp_robotstxt_settings');
			$psp_virtual_robots_content = isset($psp_robotstxt_settings['content']) ? stripcslashes(esc_textarea($psp_robotstxt_settings['content'])) : '';
			if ($psp_virtual_robots_content) {
				$robots_content = stripcslashes(esc_textarea(strip_tags($psp_virtual_robots_content)));
			}
		}
		
		return $robots_content;
	}
	
	/*
	 * Registers the .htaccess editor settings and appends the
	 * key to the plugin settings tabs array.
	 */
	private function register_htaccess_settings() {
		$this->psp_settings_tabs[$this->psp_htaccess_settings_group] = '.htaccess Editor';		
		$psp_htaccess_settings_name = "psp_htaccess_settings";

		$disabled = 0;
		
		$psp_htaccess_settings = get_option($psp_htaccess_settings_name);
		$htaccess_content = isset($psp_htaccess_settings['content']) ? stripcslashes(esc_textarea(html_entity_decode($psp_htaccess_settings['content']))) : '';
		
		//wp_enqueue_script( 'psp-hta', plugins_url( '/js/cm-hta.js', __FILE__ ),array( 'jquery' ), false, true);
		//$cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/nginx', 'codemirror'=> array('autoRefresh' => true)));
        //wp_localize_script('psphta', 'cm_settings', $cm_settings);
			
		//wp_enqueue_script( 'psp-input-toggler', plugins_url( '/js/pspinputtypetoggler.js', __FILE__ ), array( 'jquery' ) );
		//register
		register_setting( $this->psp_htaccess_settings_group, $psp_htaccess_settings_name, array( &$this, 'sanitize_htaccess_settings' ) );
		//add Section
		add_settings_section( 'psp_section_htaccess', esc_html__('.htaccess Editor', 'platinum-seo-pack' ), array( &$this, 'section_htaccess_desc' ), $this->psp_htaccess_settings_group );

		//add fields		
		
		$htaccess_file    = $this->htaccess_file; 
		
		if ( ! file_exists( $htaccess_file ) ) {		
			//echo "do nothing";
			add_settings_error('psp_htaccess_settings', esc_html( 'psp_htaccess_content' ), '.htaccess file does not seem to exist in the root! Ensure that you have one to write into it here.', 'error');
		} else {
		
			if( isset($_GET['settings-updated']) && true == sanitize_key($_GET['settings-updated'])){
				//echo "do nothing";
			} else {
		
				$htaccess_file_handle = fopen( $htaccess_file, 'r' );

				$content = '';
				if ( filesize( $htaccess_file ) > 0 ) {
					$content = fread( $htaccess_file_handle, filesize( $htaccess_file ) );
				}
				$htaccess_content = esc_textarea( $content );
				//$psp_robotstxt_settings['content'] = esc_textarea( $content );
				fclose( $htaccess_file_handle );
				
			}
			$option_description = "";
			if ( ! is_writable( $htaccess_file ) ) {
				$disabled = 1;
				$option_description = esc_html__( '.htaccess file exists in the root but it is not writeable. Make sure that it is writeable for you to write into it here.', 'platinum-seo-pack' );
			}			
		
			$content_field     = array (
				'label_for' 	=> 'psp_htaccess_content',
				'option_name'   => $psp_htaccess_settings_name.'[content]',
				'option_value'  => $htaccess_content,
				'disabled'  => $disabled,
				'option_description'  => $option_description,
			);
			
			add_settings_field( 'psp_htaccess_content', esc_html__('.htaccess Content: ', 'platinum-seo-pack'), array( &$this, 'psp_add_field_textarea' ), $this->psp_htaccess_settings_group, 'psp_section_htaccess', $content_field );
		}
		
	}
	
	function sanitize_htaccess_settings($settings) {
	
		if( isset( $settings['content'] ) ) $settings['content'] = sanitize_textarea_field( wp_slash(htmlentities($settings['content'])) );	
		
		return $settings;
	}
	
	//function section_htaccess_desc() {echo ''; }
	function section_htaccess_desc() {
		$htaccess_file    = $this->htaccess_file; //;		
		if ( ! file_exists( $htaccess_file ) ) {			
			echo '<p style="color: orange">'.esc_html__('A .htaccess file does not exist in the root! Ensure that you have not accidentally deleted it!', 'platinum-seo-pack') . '</p>';			
		} else {
			if ( ! is_writable( $htaccess_file ) ) {
				echo '<p style="color: orange">'.esc_html__('A .htaccess file exists in the root but it is not writeable. Ensure that it is writeable for you to edit it here.', 'platinum-seo-pack') . '</p>';
			}
		}
	}
	
	/*
	 * Callback for adding a textarea.
	 */
	function psp_add_field_textarea(array $args) {
	
		$option_name   = isset($args['option_name']) ? $args['option_name'] : '';
		$id     = isset($args['label_for']) ? $args['label_for'] : '';
		$option_value     = isset($args['option_value']) ? esc_textarea( html_entity_decode(($args['option_value']) )) : '';
        $option_description     = isset($args['option_description']) ? esc_html( $args['option_description'] ) : '';
		$option_disabled = "";
		if (isset($args['disabled']) && $args['disabled']) $option_disabled = "disabled='disabled'";
	
		echo "<textarea id='".esc_attr($id)."' name='".esc_attr($option_name)."' ".esc_html($option_disabled)." rows='50' style='width:99%' type='textarea'>{$option_value}</textarea><br /><p class='description'>".esc_html($option_description)."</p>";
	
		//echo "<textarea rows='4' id='".$this->psp_home_settings_key['description']."' name='".$this->psp_home_settings_key['description']."'>".stripcslashes($this->psp_home_settings['description'])."</textarea>";			
	}
	
	/*
	 * Callback for adding a textarea.
	 */
	function psp_add_field_textarea_js(array $args) {
	
		$option_name   = isset($args['option_name']) ? $args['option_name'] : '';
		$id     = isset($args['label_for']) ? $args['label_for'] : '';
		
		//$option_value     = isset($args['option_value']) ? esc_textarea(html_entity_decode(base64_decode( $args['option_value'] ), ENT_QUOTES)) : '';
		//$option_value     = isset($args['option_value']) ? esc_textarea(html_entity_decode( $args['option_value'], ENT_QUOTES)) : '';
		$option_value     = isset($args['option_value']) ? esc_textarea($args['option_value']) : '';
		
        $option_description     = isset($args['option_description']) ? esc_html( $args['option_description'] ) : '';
		$option_disabled = "";
		if (isset($args['disabled']) && $args['disabled']) $option_disabled = "disabled='disabled'";
	
		echo "<textarea id='".esc_attr($id)."' name='".esc_attr($option_name)."' ".esc_html($option_disabled)." rows='50' style='width:99%' type='textarea'>{$option_value}</textarea><br /><p class='description'>".esc_html($option_description)."</p>";
	
		//echo "<textarea rows='4' id='".$this->psp_home_settings_key['description']."' name='".$this->psp_home_settings_key['description']."'>".stripcslashes($this->psp_home_settings['description'])."</textarea>";			
	}
	
	/*
	 * renders Plugin settings page, checks
	 * for the active tab and replaces key with the related
	 * settings key. Uses the plugin_options_tabs method
	 * to render the tabs.
	 */
	function psp_tools_options_page() {
		$tab = isset( $_GET['psptools'] ) ? sanitize_key($_GET['psptools']) : $this->psp_ga_settings_group;
		if ($tab == $this->psp_robots_settings_group) {
			$psp_button = "submitrobotstxt";
			$psp_nonce_field = "psp-robotstxt-nonce";
			$psp_nonce_name = "psp-robotstxt";
		    $psp_cm_text_settings['codeEditor'] = wp_enqueue_code_editor(array('codemirror'=> array('autoRefresh' => true)));
		    wp_localize_script('psp-meta-box', 'psp_cm_text_settings', $psp_cm_text_settings);
		   wp_enqueue_script( 'psp-cm', plugins_url( '/js/cm.js', __FILE__ ),array( 'jquery' ), false, true);
		} elseif ($tab == $this->psp_htaccess_settings_group) {
			$psp_button = "submithtaccess";
			$psp_nonce_field = "psp-htaccess-nonce";
			$psp_nonce_name = "psp-htaccess";
			$psp_cm_hta_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/nginx', 'codemirror'=> array('autoRefresh' => true)));
			wp_localize_script('psp-meta-box', 'psp_cm_hta_settings', $psp_cm_hta_settings);
			wp_enqueue_script( 'psp-hta', plugins_url( '/js/cm-hta.js', __FILE__ ),array( 'jquery' ), false, true);
		} elseif ($tab == $this->psp_ga_settings_group) {
			$psp_button = "submitanalyticscode";
			$psp_nonce_field = "psp-ga-nonce";
			$psp_nonce_name = "psp-ga";
			$psp_cm_ga_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/javascript', 'codemirror'=> array('autoRefresh' => true)));
			wp_localize_script('psp-meta-box', 'psp_cm_ga_settings', $psp_cm_ga_settings);
			wp_enqueue_script( 'psp-cmjs', plugins_url( '/js/cmjs.js', __FILE__ ),array( 'jquery' ), false, true);
		}
		//if ( isset( $_POST['submitrobotstxt'] ) ) {
		if((isset($_GET['settings-updated']) && true == sanitize_key($_GET['settings-updated'])) && ($tab == $this->psp_robots_settings_group )){
			if ( ! current_user_can( 'manage_options' ) ) {
				die( esc_html__( 'You cannot edit the robots.txt file.', 'platinum-seo-pack' ) );
			}
			
			//check_admin_referer( 'psp-robotstxt' );
			$psp_settings = get_option("psp_robotstxt_settings");
			$use_virtual_robots_file = isset($psp_settings['use_virtual_robots_file']) ? esc_attr($psp_settings['use_virtual_robots_file']) : '';
			$robotscontent = isset($psp_settings['content']) ? stripcslashes( esc_textarea($psp_settings['content'] )) : '';
			
			$robotstxt_file = $this->robotstxt_file; //;

			if (!$use_virtual_robots_file) {
			    
			    if (empty($robotscontent)) {
			        $robotscontent = $this->getDefaultRobots();
			    }
			
				if ( file_exists( $robotstxt_file )) {
					
					if ( is_writable( $robotstxt_file ) ) {
						$robotstxt_filehandle = fopen( $robotstxt_file, 'w+' );
						fwrite( $robotstxt_filehandle, $robotscontent );
						fclose( $robotstxt_filehandle );
						$msg = esc_html__( 'Updated Robots.txt', 'platinum-seo-pack' );
					}
				} else {
					if ( is_writable( get_home_path() ) ) {
						$robotstxt_filehandle = fopen( $robotstxt_file, 'x' );
						fwrite( $robotstxt_filehandle, $robotscontent );
						fclose( $robotstxt_filehandle );
						$msg = esc_html__( 'Created Robots.txt', 'platinum-seo-pack' );
					}					
				}
			}
		}
		
		//if ( isset( $_POST['submithtaccess'] ) ) {
		if((isset($_GET['settings-updated']) && true == sanitize_key($_GET['settings-updated'])) && ($tab == $this->psp_htaccess_settings_group )){
			if ( ! current_user_can( 'manage_options' ) ) {
				die( esc_html__( 'You cannot edit the .htaccess file.', 'platinum-seo-pack' ) );
			}
			
			$psp_htaccess_settings = get_option("psp_htaccess_settings");			
			$htaccesscontent = isset($psp_htaccess_settings['content']) ? stripcslashes( html_entity_decode($psp_htaccess_settings['content']) ) : '';
			
			$htaccess_file = $this->htaccess_file; //;			
			
			if ( file_exists( $htaccess_file )) {
				
				if ( is_writable( $htaccess_file ) ) {
					$htaccess_filehandle = fopen( $htaccess_file, 'w+' );
					fwrite( $htaccess_filehandle, $htaccesscontent );
					fclose( $htaccess_filehandle );
					$msg = esc_html__( 'Updated .htaccess file', 'platinum-seo-pack' );
				}
			} else {
				//do nothing.				
			}
			
		}
		?>
		<div class="wrap">		
			<h1 style='line-height:30px;'><?php esc_html_e('Techblissonline Platinum SEO Pack Tools', 'platinum-seo-pack') ?></h1>
			<p style="color: red"><?php esc_html_e('You need to click the "Save Settings" button to save the changes you made to each individual tab before moving on to the next tab.', 'platinum-seo-pack') ?></p>		
			<?php $this->psp_tools_tabs(); ?>
			<form name="platinum-seo-form" method="post" action="options.php">
				<?php wp_nonce_field( $psp_nonce_field, $psp_nonce_name );?>
				<?php settings_fields( $tab ); ?>
				<?php settings_errors(); ?>
				<?php do_settings_sections( $tab ); ?>
				<?php submit_button('Save Settings', 'primary', $psp_button); ?>
			</form>
		
			<div class="sidebar-cta">
			<h2>   
				<a class="bookmarkme" href="<?php echo 'https://techblissonline.com/tools/'; ?>" target="_blank"><img src="<?php echo esc_url(PSP_PLUGIN_URL).'images/techblissonline-logo.png'; ?>" class="img-responsive" alt="Techblissonline Platinum SEO Wordpress Tools"/></a>
			</h2>
			    <div class="container bg-info" id="tools" style="width:100%">
                    <div class="row"><div class="h3 col-sm-12"><a class="btn-primary col-sm-12" href="https://techblissonline.com/tools/platinum-seo-wordpress-premium/" target="_blank">Platinum SEO Premium for wordpress</a></div><div class="h3 col-sm-12"><a class="btn-success col-sm-12" href="https://techblissonline.com/tools/" target="_blank">Techblissonline Platinum SEO Audit and Analysis Tools</a></div></div>     
                </div>
				<a href="https://techblissonline.com/tools/" target="_blank">Be our Patreon and enjoy these premium Wordpress SEO tools for just $9</a>
				<div class="container" style="width:100%"><a href="https://techblissonline.com/tools/" target="_blank"><span class="col-sm-12 dashicons dashicons-thumbs-up dashicons-psp"></span></a></div>
			</div>
		</div>
		<?php
	}
	
	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * psp_options_page method.
	 */
	function psp_tools_tabs() {
		$current_tab = isset( $_GET['psptools'] ) ? sanitize_key($_GET['psptools']) : $this->psp_ga_settings_group;
		//$current_tab = $active_tab;
		wp_enqueue_style("psp-settings-bs-css", plugins_url( '/css/psp-settings-bs.css', __FILE__ ));
		wp_enqueue_style("psp-htmlsettings-css", plugins_url( '/css/psp-html-settings.css', __FILE__ ));
		//screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->psp_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			$psp_icon = '';
			if ($tab_key == $this->psp_ga_settings_group) {
				$psp_icon = '<span class="dashicons dashicons-analytics"></span> ';
			} 
			if ($tab_key == $this->psp_robots_settings_group) {
				$psp_icon = '<span class="dashicons dashicons-admin-page"></span> ';
			}
			if ($tab_key == $this->psp_htaccess_settings_group) {
				$psp_icon = '<span class="dashicons dashicons-admin-page"></span> ';
			} 
			echo '<a style="text-decoration:none" class="nav-tab ' . esc_attr($active) . '" href="?page=' . esc_attr($this->psp_plugin_options_key) . '&psptools=' . esc_attr($tab_key) . '">' . $psp_icon. esc_attr($tab_caption) . '</a>';	
		}
		echo '</h2>';
	}
}