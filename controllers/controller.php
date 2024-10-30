<?php

require_once plugin_dir_path(__FILE__) . '../models/model.php';

/**
 * Controller.
 * 
 * @since 1.0
 * 
 * @author Local Biz Commando
 */
class LBCLocalSEOController extends LBCLocalSEOCommon
{
	/**
	 * Path of the main file of the plugin.
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	private $_plg_main_file_path = '';
	
	/**
	 * Model.
	 *
	 * @var LBCLocalSEOModel object
	 *
	 * @since 1.0
	 */
	private $_model;
	
	/**
	 * Constructor.
	 * 
	 * @param string $plg_root_path Root path of the plugin.
	 * @param string $plg_root_url Root URL of the plugin.
	 * 
	 * @since 1.0
	 */
	public function __construct($plg_root_path, $plg_root_url)
	{
		$this->_set_plg_root_path($plg_root_path);
		$this->_set_plg_root_url($plg_root_url);
		$this->_plg_main_file_path = $this->_get_plg_root_path() . 'lbc-local-seo.php';
		
		$this->_model = new LBCLocalSEOModel();
		
		// init
		add_action('init', array($this, 'init'), 0);
		
		// admin init
		add_action('admin_init', array($this, 'admin_init'));
	}
	
	/**
	 * Activates the plugin.
	 * 
	 * @since 1.0
	 */
	public function activate()
	{
		global $wp_version;
		$required_wp_version = '3.5';
	
		if (version_compare($wp_version, $required_wp_version, '<')) {
			$err_msg = __('This plugin requires WordPress version', 'lbc-local-seo') .
						' ' . $required_wp_version . ' ' .
						__('or higher.', 'lbc-local-seo');
			
			$this->_die($err_msg);
		}
		
		$this->_model->save_default_settings();
		
		$this->_model->add_location_page();
	}
	
	/**
	 * Initialises the admin part of the plugin.
	 *
	 * @since 1.0.1
	 */
	public function admin_init()
	{
		// admin - enqueue CSS & JS files
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_css_js'));
		
		// admin - hook settings ajax callback
		add_action('wp_ajax_save_settings', array($this, 'execute_ajax'));
		add_action('wp_ajax_save_location_settings', array($this, 'execute_ajax'));
	}
	
	/**
	 * Initialises the plugin.
	 * 
	 * @since 1.0
	 */
	public function init()
	{
		// loading language file
		load_plugin_textdomain('lbc-local-seo', false, dirname(plugin_basename($this->_plg_main_file_path)) . '/languages/');
		
		// admin - add menus
		add_action('admin_menu', array($this, 'add_plg_menu'));

		// FE - add meta tags
		add_action('wp_head', array($this, 'add_meta_tags'), 0);
		
		// FE - enqueue CSS & JS files
		add_action('wp_enqueue_scripts', array($this, 'enqueue_fe_css_js'));
		
		// FE - filter - content
		add_filter('the_content', array($this, 'location_content_filter'));
	}
	
	// --------------------------------------------------------------
	
	/**
	 * Adds plugin menus to WP.
	 * 
	 * @since 1.0
	 */
	public function add_plg_menu()
	{
		if (!$this->_model->does_top_level_admin_menu_exist('lbc-local-seo-plg')) {
			// add top level menu
			add_menu_page(
				'LBC Local SEO',
				'LBC Local SEO',
				'manage_options',
				'lbc-local-seo-plg',
				array($this, 'view'),
				plugins_url('lbc-local-seo/images/lbc-page-icon.png')
			);
		}
		
		// location
		add_submenu_page(
			'lbc-local-seo-plg',
			__('Location', 'lbc-local-seo'),
			__('Location', 'lbc-local-seo'),
			'manage_options',
			'lbc-local-seo-plg-location',
			array($this, 'view')
		);
		
		// settings
		add_submenu_page(
			'lbc-local-seo-plg',
			__('Settings', 'lbc-local-seo'),
			__('Settings', 'lbc-local-seo'),
			'manage_options',
			'lbc-local-seo-plg-settings',
			array($this, 'view')
		);
		
		// user guide
		add_submenu_page(
			'lbc-local-seo-plg',
			__('User Guide', 'lbc-local-seo'),
			__('User Guide', 'lbc-local-seo'),
			'manage_options',
			'lbc-local-seo-plg-user-guide',
			array($this, 'view')
		);
	}
	
	/**
	 * Enqueues admin CSS.
	 * 
	 * @param string $hook
	 * 
	 * @since 1.0
	 */
	public function enqueue_admin_css_js($hook)
	{
		wp_register_style('lbc-style', plugins_url('lbc-local-seo/css/lbc.css'), array(), '1.0');
		wp_enqueue_style('lbc-style');
		
		// for location
		if ($hook == 'lbc-local-seo_page_lbc-local-seo-plg-location') {
			wp_enqueue_media();
				
			wp_register_style('lbc-jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.css', array(), '1.10.2');
			wp_enqueue_style('lbc-jquery-style');
			
			$admin_style_dependencies = array('lbc-jquery-style');
			$admin_script_dependencies = array('jquery');
		}
		else {
			$admin_style_dependencies = array();
			$admin_script_dependencies = array();
		}
		
		// for settings, location and user guide
		if (in_array($hook, array('toplevel_page_lbc-local-seo-plg', 'lbc-local-seo_page_lbc-local-seo-plg-settings', 'lbc-local-seo_page_lbc-local-seo-plg-location', 'lbc-local-seo_page_lbc-local-seo-plg-user-guide'))) {
			wp_register_style('lbc-admin-style', plugins_url('lbc-local-seo/css/admin.css'), $admin_style_dependencies, '1.0');
			wp_enqueue_style('lbc-admin-style');
		}
		
		// for location
		if ($hook == 'lbc-local-seo_page_lbc-local-seo-plg-location') {
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-datepicker');
				
			$settings = $this->_model->get_settings();
			$google_maps_api_param_str = '?';
			if (isset($settings['google_maps_api_key']) &&
				$settings['google_maps_api_key'] != '')
			{
				$google_maps_api_param_str .= 'key=' . $settings['google_maps_api_key'] . '&';
			}
			$google_maps_api_param_str .= 'sensor=false';
			wp_register_script(
				'google-maps',
				'https://maps.googleapis.com/maps/api/js' . $google_maps_api_param_str,
				array(),
				false,
				true
			);
			wp_enqueue_script('google-maps');
		}
		
		// for settings and location
		if (in_array($hook, array('toplevel_page_lbc-local-seo-plg', 'lbc-local-seo_page_lbc-local-seo-plg-settings', 'lbc-local-seo_page_lbc-local-seo-plg-location'))) {
			wp_register_script('lbc-admin-script', plugins_url('lbc-local-seo/js/admin.js'), $admin_script_dependencies, '1.0', true);
			wp_enqueue_script('lbc-admin-script');
		}
		
		// for settings
		if (in_array($hook, array('toplevel_page_lbc-local-seo-plg', 'lbc-local-seo_page_lbc-local-seo-plg-settings'))) {
			// ajax - save settings
			wp_localize_script(
				'lbc-admin-script',
				'settings_ajax_object',
				array(
					'ajax_url'	=> admin_url('admin-ajax.php'),
					'action'	=> 'save_settings',
					'nonce'		=> wp_create_nonce('lbc-local-seo-ajax-save-settings'),
					'texts'		=> $this->_model->get_ajax_msgs()
				)
			);
		}
		
		// for location
		if ($hook == 'lbc-local-seo_page_lbc-local-seo-plg-location') {
			// ajax - save location settings
			wp_localize_script(
				'lbc-admin-script',
				'location_settings_ajax_object',
				array(
					'ajax_url'			=> admin_url('admin-ajax.php'),
					'action'			=> 'save_location_settings',
					'nonce'				=> wp_create_nonce('lbc-local-seo-ajax-save-location-settings'),
					'texts'				=> $this->_model->get_ajax_msgs(),
					'meta_desc_max_len'	=> LBC_LS_META_DESC_MAX_LEN
				)
			);
		}
	}
	
	/**
	 * Adds meta tags to the plugin's pages (locations page, single locations).
	 *
	 * @since 1.1
	 */
	public function add_meta_tags()
	{
		$settings = $this->_model->get_settings();
		
		if (is_page($settings['location_page_slug'])) {
			$this->_model->echo_location_page_meta_html();
		}
	}
	
	/**
	 * Enqueues FE CSS.
	 * 
	 * @since 1.0
	 */
	public function enqueue_fe_css_js()
	{
		$settings = $this->_model->get_settings();
		
		if (is_page($settings['location_page_slug'])) {
			wp_register_style('lbc-local-seo-fe-style', plugins_url('lbc-local-seo/css/fe.css'), array(), '1.0');
			wp_enqueue_style('lbc-local-seo-fe-style');
		
			$google_maps_api_param_str = '?';
			if (isset($settings['google_maps_api_key']) &&
				$settings['google_maps_api_key'] != '')
			{
				$google_maps_api_param_str .= 'key=' . $settings['google_maps_api_key'] . '&';
			}
			$google_maps_api_param_str .= 'sensor=false';
			wp_register_script(
				'google-maps',
				'https://maps.googleapis.com/maps/api/js' . $google_maps_api_param_str,
				array(),
				false,
				true
			);
			wp_enqueue_script('google-maps');
		
			wp_register_script('lbc-local-seo-fe-script', plugins_url('lbc-local-seo/js/fe.js'), array('jquery'), '1.0', true);
			wp_enqueue_script('lbc-local-seo-fe-script');
			
			$location_settings = $this->_model->get_location_settings();
			wp_localize_script(
				'lbc-local-seo-fe-script',
				'location_settings_object',
				array(
					'latitude'	=> $location_settings['latitude'],
					'longitude'	=> $location_settings['longitude'],
					'title'		=> $location_settings['name']
				)
			);
		}
	}
	
	// --------------------------------------------------------------
	
	/**
	 * Executes the ajax request.
	 * 
	 * @since 1.0
	 */
	public function execute_ajax()
	{
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'save_settings':
					if (wp_verify_nonce($_POST['nonce'], 'lbc-local-seo-ajax-save-settings') == false) {
						echo 0;
					}
					else {
						echo ($this->_model->save_settings($_POST) === true) ? 1 : 0;
					}
				break;
				
				case 'save_location_settings':
					if (wp_verify_nonce($_POST['nonce'], 'lbc-local-seo-ajax-save-location-settings') == false) {
						echo 0;
					}
					else {
						echo ($this->_model->save_location_settings($_POST) === true) ? 1 : 0;
					}
				break;
				
				default:
					echo 0;
			}
		}
		die(); // to exclude the extra 0 from the ajax return, WP ajax dies with returning 0
	}
	
	/**
	 * Views the the settings on the admin site.
	 * 
	 * @since 1.0
	 */
	public function view()
	{
		if (!current_user_can('manage_options'))  {
			$this->_die(
				__('You do not have sufficient permissions to access this page.', 'lbc-local-seo'),
				admin_url(),
				__('Admin home', 'lbc-local-seo')
			);
		}
		
		switch ($_GET['page']) {
			case 'lbc-local-seo-plg-location':
				$view_data = array();
				$view_data['opening_hours_formats'] = $this->_model->get_opening_hours_formats();
				$view_data['business_types'] = $this->_model->get_business_types();
				$view_data['settings'] = $this->_model->get_settings();
				$view_data['location_settings'] = $this->_model->get_location_settings($view_data['settings']['opening_hours_format']);
				if ($view_data['location_settings']['meta_desc'] == '{LBC_LS_LOCATION_META_DESC}') {
					$view_data['location_settings']['meta_desc'] = '';
				}
				$view_data['is_logo_accessible'] = $this->_is_url_accessible($view_data['location_settings']['logo']);
				$view_data['countries'] = $this->_model->get_countries();
				$view_data['currencies'] = $this->_model->get_currencies();
				$view_data['price_ranges'] = $this->_model->get_price_ranges();
				$view_data['opening_hours'] = $this->_model->get_opening_hours($view_data['settings']['opening_hours_format']);
				$view_data['social_icons'] = $this->_model->get_social_icons();
				$view_data['plg_root_path'] = $this->_get_plg_root_path();
				$view_data['plg_root_url'] = $this->_get_plg_root_url();
				
				$view_data['adverts'] = $this->_model->get_adverts();
					
				require_once($this->_get_plg_root_path() . 'views/location-settings.php');
			break;
			
			case 'lbc-local-seo-plg':
			case 'lbc-local-seo-plg-settings':
				$view_data = array();
				$view_data['addr_formats'] = $this->_model->get_address_formats();
				$view_data['opening_hours_formats'] = $this->_model->get_opening_hours_formats();
				$view_data['settings'] = $this->_model->get_settings();
				$view_data['plg_root_path'] = $this->_get_plg_root_path();
				$view_data['plg_root_url'] = $this->_get_plg_root_url();
				
				$view_data['adverts'] = $this->_model->get_adverts();
				
				require_once($this->_get_plg_root_path() . 'views/settings.php');
			break;
			
			case 'lbc-local-seo-plg-user-guide':
				$view_data = array();
				$view_data['plg_root_path'] = $this->_get_plg_root_path();
				$view_data['plg_root_url'] = $this->_get_plg_root_url();
				
				$view_data['adverts'] = $this->_model->get_adverts();
				
				require_once($this->_get_plg_root_path() . 'views/user-guide.php');
			break;
		}
	}
	
	/**
	 * Replacing the content of the location page based on the location settings.
	 * 
	 * @param string $content
	 * 
	 * @since 1.0
	 * 
	 * @return string Modified content for location page, otherwise the original content.
	 */
	public function location_content_filter($content)
	{
		$settings = $this->_model->get_settings();
		
		if (is_page($settings['location_page_slug'])) {
			$content = '';
			
			$view_data = array();
			$view_data['settings'] = $this->_model->get_settings();
			$view_data['location_settings'] = $this->_model->get_location_settings();
			$view_data['addr_params'] = $this->_model->get_addr_params();
			$view_data['countries'] = $this->_model->get_countries();
			$view_data['opening_hours'] = $this->_model->get_opening_hours($view_data['settings']['opening_hours_format']);
			$view_data['social_icons'] = $this->_model->get_social_icons();
			$view_data['page_title'] = get_the_title();
			
			$content = $this->_model->get_view('location', $view_data);
		}
		
		return $content;
	}
}