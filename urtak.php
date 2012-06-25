<?php
/*
 Plugin Name: Urtak
 Plugin URI: http://urtak.com
 Description: Urtak is collaborative polling - everyone can ask questions. It's easy to engage a great number of people in a structured conversation that produces thousands of responses.
 Version: 2.0.0-RC1
 Author: Urtak, Inc.
 Author URI: http://urtak.com
 */

if(!class_exists('UrtakPlugin')) {
	class UrtakPlugin {
		/// CONSTANTS

		//// VERSION
		const VERSION = '2.0.0-RC1';

		//// KEYS
		const POST_META_KEY = '_urtak_post_meta';
		const SETTINGS_KEY = '_urtak_settings';

		//// SLUGS
		const TOP_LEVEL_PAGE_SLUG = 'urtak';
		const SUB_LEVEL_INSIGHTS_SLUG = 'urtak';
		const SUB_LEVEL_SETTINGS_SLUG = 'urtak-settings';

		//// CACHE
		const CACHE_PERIOD = 86400; // 24 HOURS

		/// DATA STORAGE
		private static $admin_page_hooks = array();
		private static $default_meta = array();
		private static $default_settings = array();
		private static $urtak_api = null;

		public static function init() {
			self::add_actions();
			self::add_filters();
			self::initialize_defaults();
			self::register_shortcodes();
		}

		private static function add_actions() {
			if(is_admin()) {
				add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_administrative_resources'));
				add_action('admin_menu', array(__CLASS__, 'add_administrative_interface_items'));
				add_action('admin_notices', array(__CLASS__, 'show_credentials_notice'));
				add_action('add_meta_boxes_post', array(__CLASS__, 'add_meta_boxes'));
				add_action('manage_posts_custom_column', array(__CLASS__, 'add_posts_columns_output'), 10, 2);
				add_action('save_post', array(__CLASS__, 'save_post_meta'), 10, 2);
				add_action('wp_dashboard_setup', array(__CLASS__, 'add_dashboard_widget'));
			}

			add_action('admin_bar_menu', array(__CLASS__, 'add_admin_bar_items'), 35);
			add_action('init', array(__CLASS__, 'initialize_api_object'));
		}

		private static function add_filters() {
			add_filter('manage_edit-post_columns', array(__CLASS__, 'add_posts_columns'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_plugin_links'));
		}

		private static function initialize_defaults() {
			// Empty credentials to stat
			self::$default_settings['credentials'] = array();

			// We want the Urtaks to appear by default, so let's append them
			self::$default_settings['placement'] = 'append';

			// We want Urtaks to support community moderation by default so that we get more questions and responses
			self::$default_settings['moderation'] = 'community';

			// Most users will use English
			self::$default_settings['language'] = 'en';

			// Remove the noise that comments generate
			self::$default_settings['disable-comments'] = 'yes';
		}

		private static function register_shortcodes() {

		}

		/// CALLBACKS

		public static function add_admin_bar_items($wp_admin_bar) {
			if(is_admin() && self::has_pending_questions()) {
				$wp_admin_bar->add_menu( array(
					'id' => 'urtak',
					'title' => sprintf(__('+%s'), number_format_i18n(self::get_pending_questions_count())),
					'href' => add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), admin_url('admin.php'))
				));
			}
		}

		public static function add_administrative_interface_items() {
			self::$admin_page_hooks[] = $top_level = add_menu_page(__('Urtak Insights'), __('Urtak'), 'manage_options', self::TOP_LEVEL_PAGE_SLUG, array(__CLASS__, 'display_insights_page'), plugins_url('resources/backend/img/urtak-logo-15.png', __FILE__), 56);
			self::$admin_page_hooks[] = $sub_level_insights = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Insights'), __('Insights'), 'manage_options', self::SUB_LEVEL_INSIGHTS_SLUG, array(__CLASS__, 'display_insights_page'));
			self::$admin_page_hooks[] = $sub_level_settings = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Settings'), __('Settings'), 'manage_options', self::SUB_LEVEL_SETTINGS_SLUG, array(__CLASS__, 'display_settings_page'));

			add_action("load-{$sub_level_settings}", array(__CLASS__, 'process_settings_actions'));

			add_meta_box('urtak-at-a-glance', __('At a Glance'), array(__CLASS__, 'display_meta_box__at_a_glance'), 'urtak', 'top');
			add_meta_box('urtak-top-urtaks', __('Top Urtaks'), array(__CLASS__, 'display_meta_box__top_urtaks'), 'urtak', 'left');
			add_meta_box('urtak-user-stats', __('User Stats'), array(__CLASS__, 'display_meta_box__user_stats'), 'urtak', 'left');
			add_meta_box('urtak-posts-without-urtaks', __('Posts without Urtaks'), array(__CLASS__, 'display_meta_box__posts_without_urtaks'), 'urtak', 'left');
			add_meta_box('urtak-top-questions', __('Top Questions'), array(__CLASS__, 'display_meta_box__top_questions'), 'urtak', 'right');
		}

		public static function add_dashboard_widget() {
			if(self::has_credentials()) {
				wp_add_dashboard_widget('urtak', __('Urtak'), array(__CLASS__, 'display_dashboard_widget'));
			}
		}

		public static function add_posts_columns($columns) {
			if(self::has_credentials()) {
				$date = $columns['date'];
				unset($columns['date']);

				$columns['urtak-questions'] = __('Questions');
				$columns['urtak-responses'] = __('Responses');
				$columns['date'] = $date;
			}

			return $columns;
		}

		public static function add_posts_columns_output($column, $post_id) {
			switch($column) {
				case 'urtak-responses':
					echo number_format_i18n(count(self::get_responses($post_id)), 0);
					break;
				case 'urtak-questions':
					echo number_format_i18n(count(self::get_questions($post_id)), 0);
					break;
			}
		}

		public static function add_meta_boxes($post) {
			add_meta_box('urtak-meta-box', __('Urtak'), array(__CLASS__, 'display_meta_box'), $post->post_type, 'normal');
		}

		public static function add_plugin_links($links) {
			$new_links = array();

			if(self::has_credentials()) {
				$new_links[] = $insights_link = sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), admin_url('admin.php')), __('Insights'));
			}

			$new_links[] = $settings_link = sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php')), __('Settings'));

			return array_merge($new_links, $links);
		}

		public static function enqueue_administrative_resources($hook) {
			wp_enqueue_style('urtak-backend', plugins_url('resources/backend/urtak.css', __FILE__), array(), self::VERSION);

			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			wp_enqueue_script('urtak-backend', plugins_url('resources/backend/urtak.js', __FILE__), array('jquery'), self::VERSION);
		}

		public static function initialize_api_object() {
			if(self::has_credentials()) {
				self::$urtak_api = new WordPressUrtak(
					array(
						'api_key' => self::get_credentials('api-key'), 
						'email' => self::get_credentials('email'), 
						'publication_key' => self::get_credentials('publication-key')
					)
				);
			}
		}

		public static function process_settings_actions() {
			$data = stripslashes_deep($_POST);

			if(!empty($data['urtak-login-noce']) && wp_verify_nonce($data['urtak-login-nonce'], 'urtak-login')) {
				self::_process_login($data['urtak-login-email'], $data['urtak-login-password']);
			} else if(!empty($data['urtak-signup-nonce']) && wp_verify_nonce($data['urtak-signup-nonce'], 'urtak-signup')) {
				self::_process_signup($data['urtak-signup-email']);
			} else if(!empty($data['save-urtak-settings-nonce']) && wp_verify_nonce($data['save-urtak-settings-nonce'], 'save-urtak-settings')) {
				self::_process_settings_save($data['urtak']);
			}
		}

		public static function save_post_meta($post_id, $post) {
			$data = stripslashes_deep($_POST);
			if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !wp_verify_nonce($data['save-urtak-meta-nonce'], 'save-urtak-meta')) {
				return;
			}

			$meta = apply_filters('urtak_pre_meta_save', $data['urtak']);
			$meta = self::set_meta($post_id, $meta);
		}

		public static function show_credentials_notice() {
			$data = stripslashes_deep($_REQUEST);

			if(!self::has_credentials() && (!isset($data['page']) || !in_array($data['page'], array(self::SUB_LEVEL_INSIGHTS_SLUG, self::SUB_LEVEL_SETTINGS_SLUG)))) {
				$base = add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php'));

				include('views/backend/misc/admin-notice.php');
			}
		}

		/// DISPLAY CALLBACKS

		public static function display_dashboard_widget() {
			$maximum_responses = 0;

			$dates = array();
			for($i = -7; $i <= 0; $i++) {
				$responses = rand(100, 2000);
				$yes = rand(0, $responses);
				$no = $responses - $yes;

				$dates[] = $item = array(
					'responses' => $responses,
					'yes' => $yes,
					'no' => $no,
					'date' => date('M j', strtotime("Today {$i} Days"))
				);

				if($item['responses'] > $maximum_responses) {
					$maximum_responses = $item['responses'];
				}
			}

			$urtaks = array();

			$item = new stdClass;
			$item->title = 'Why do you play mobile games?';
			$item->questions = rand(1, 20);
			$item->responses = rand(100, 2000);
			$item->users = rand($item->questions,$item->responses);

			$urtaks[] = $item;

			$item = new stdClass;
			$item->title = 'Who is Francois Hollande?';
			$item->questions = rand(1, 20);
			$item->responses = rand(100, 2000);
			$item->users = rand($item->questions,$item->responses);

			$urtaks[] = $item;

			$item = new stdClass;
			$item->title = 'The Science of Concussions';
			$item->questions = rand(1, 20);
			$item->responses = rand(100, 2000);
			$item->users = rand($item->questions,$item->responses);

			$urtaks[] = $item;

			include('views/backend/dashboard/widget.php');
		}

		public static function display_insights_page() {
			$is_insights = true;
			$settings = self::get_settings();

			include('views/backend/misc/header.php');

			if(self::has_credentials()) {
				include('views/backend/insights/insights.php');
			} else {
				$base = add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php'));

				$login_url = self::_get_login_url(); 
				$signup_url = self::_get_signup_url();

				include('views/backend/insights/pre-credentials.php');
			}
			
			include('views/backend/misc/footer.php');
		}

		public static function display_meta_box($post) {
			$meta = self::get_meta($post->ID);

			include('views/backend/meta-boxes/meta-box.php');
		}

		public static function display_meta_box__at_a_glance() {
			$maximum_responses = 0;

			$dates = array();
			for($i = -15; $i <= 0; $i++) {
				$responses = rand(100, 2000);
				$yes = rand(0, $responses);
				$no = $responses - $yes;

				$dates[] = $item = array(
					'responses' => $responses,
					'yes' => $yes,
					'no' => $no,
					'date' => date('M j', strtotime("Today {$i} Days"))
				);

				if($item['responses'] > $maximum_responses) {
					$maximum_responses = $item['responses'];
				}
			}

			include('views/backend/insights/meta-boxes/at-a-glance.php');
		}

		public static function display_meta_box__posts_without_urtaks() {
			include('views/backend/insights/meta-boxes/posts-without-urtaks.php');
		}

		public static function display_meta_box__top_questions() {
			include('views/backend/insights/meta-boxes/top-questions.php');
		}

		public static function display_meta_box__top_urtaks() {
			include('views/backend/insights/meta-boxes/top-urtaks.php');
		}

		public static function display_meta_box__user_stats() {
			include('views/backend/insights/meta-boxes/user-stats.php');
		}

		public static function display_settings_page() {
			$data = stripslashes_deep($_REQUEST);
			$is_settings = true;
			$settings = self::get_settings();

			include('views/backend/misc/header.php');

			include('views/backend/settings/settings.php');

			include('views/backend/misc/footer.php');
		}

		/// SHORTCODE CALLBACKS

		/// POST META

		private static function get_meta($post_id) {
			if(empty($post_id)) {
				global $post;
				$post_id = $post->ID;
			}

			$meta = wp_cache_get(self::POST_META_KEY, $post_id);

			if(false === $meta) {
				$meta = wp_parse_args((array)get_post_meta($post_id, self::POST_META_KEY, true), self::$default_meta);
				wp_cache_set(self::POST_META_KEY, $meta, $post_id, time() + self::CACHE_PERIOD);
			}

			return $meta;
		}

		private static function set_meta($post_id, $meta) {
			if(empty($post_id)) {
				global $post;
				$post_id = $post->ID;
			}

			$meta = wp_parse_args($meta, self::$default_meta);
			update_post_meta($post_id, self::POST_META_KEY, $meta);
			wp_cache_set(self::POST_META_KEY, $meta, $post_id, time() + self::CACHE_PERIOD);

			return $meta;
		}

		/// SETTINGS

		private static function get_settings() {
			$settings = wp_cache_get(self::SETTINGS_KEY);

			if(!is_array($settings)) {
				$settings = wp_parse_args(get_option(self::SETTINGS_KEY, self::$default_settings), self::$default_settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}

			return $settings;
		}

		private static function set_settings($settings) {
			if(is_array($settings)) {
				$settings = wp_parse_args($settings, self::$default_settings);
				update_option(self::SETTINGS_KEY, $settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}

			return $settings;
		}

		/// CREDENTIALS

		private static function get_credentials($credential_key = null) {
			$credentials = self::get_settings('credentials');

			return empty($credentials) ? false : (is_null($credential_key) ? $credentials : (isset($credentials[$credential_key]) ? $credentials[$credential_key] : false));
		}

		/**
		 * Returns a boolean value indicating whether credentials have been stored for the site
		 *
		 * @return bool If the site has stored credentials, then return true. Otherwise, return false.
		 */ 
		private static function has_credentials() {
			$credentials = self::get_credentials();

			return is_array($credentials) && isset($credentials['api-key']) && isset($credentials['email']) && !empty($credentials['api-key']) && !empty($credentials['email']);
		}

		/// API DELEGATES

		private static function has_pending_questions() {
			return self::get_pending_questions_count() > 0;
		}

		private static function get_pending_questions_count() {
			return 1500;
		}

		/// UTILITY

		private static function _get_login_url() {
			return add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG, 'action' => 'login'), admin_url('admin.php'));
		}

		private static function _get_signup_url() {
			return add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG, 'action' => 'signup'), admin_url('admin.php'));
		}

		private static function _print_login_form($show, $data) {
			include('views/backend/misc/form-login.php');
		}

		private static function _print_signup_form($show, $data) {
			include('views/backend/misc/form-signup.php');
		}

		private static function _process_login($email, $password) {
			add_settings_error('general', 'settings_updated', __('Account credentials saved.'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG, 'settings-updated' => 'true'), admin_url('admin.php')));
		}

		private static function _process_settings_save($settings) {
			$settings = apply_filters('urtak_pre_settings_save', $settings);
			$settings = self::set_settings($settings);

			add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG, 'settings-updated' => 'true'), admin_url('admin.php')));
			exit;
		}

		private static function _process_signup($email) {
			add_settings_error('general', 'settings_updated', __('ERROR.'), 'error');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG, 'settings-updated' => 'true'), admin_url('admin.php')));
			exit;
		}

		/// TEMPLATE TAGS

		/**
		 * Returns the embed code needed to display the Urtak widget for a particular 
		 * set of arguments. The arguments are as follows:
		 * - 
		 *
		 * @param $args array An array of arguments to apply to the returned embed code.
		 */
		public static function get_embeddable_widget($args = array()) {
			// TODO Actual return the embed code for the
			// widget based on the arguments provided

			return '';
		}

	}

	require_once('lib/template-tags.php');
	require_once('lib/utility.php');

	require_once('lib/urtak-api.php');
	require_once('lib/wordpress-urtak-api.php');
	
	UrtakPlugin::init();
}
