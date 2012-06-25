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
		const SETTINGS_KEY = '_urtak_settings';
		const URTAK_ID_KEY = '_urtak_id';

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

			/// DISABLE COMMENTS CALLBACKS
			add_action('widgets_init', array(__CLASS__, 'disable_comments__remove_widget'));
			add_action('wp_loaded', array(__CLASS__, 'disable_comments'));	
		}

		private static function add_filters() {
			add_filter('manage_edit-post_columns', array(__CLASS__, 'add_posts_columns'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_plugin_links'));
		}

		private static function initialize_defaults() {
			// Empty credentials to stat
			self::$default_settings['credentials'] = array();

			// No pubilcation to start
			self::$default_settings['publication-key'] = '';

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
					'href' => self::_get_insights_url()
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

			$posts_without_urtaks = self::get_nonassociated_post_ids();
			if(!empty($posts_without_urtaks)) {
				add_meta_box('urtak-posts-without-urtaks', __('Posts without Urtaks'), array(__CLASS__, 'display_meta_box__posts_without_urtaks'), 'urtak', 'left');
			}
			

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
				$new_links[] = $insights_link = sprintf('<a href="%s">%s</a>', self::_get_insights_url(), __('Insights'));
			}

			$new_links[] = $settings_link = sprintf('<a href="%s">%s</a>', self::_get_settings_url(), __('Settings'));

			return array_merge($new_links, $links);
		}

		/**
		 * Big thank you to the Disable Comments plugin for these.
		 */
		public static function disable_comments() {
			if(self::comments_are_disabled()) {
				foreach(array('page', 'post') as $post_type_key) {
					if( post_type_supports($post_type_key, 'comments') ) {
						remove_post_type_support($post_type_key, 'comments');
						remove_post_type_support($post_type_key, 'trackbacks');
					}
				}

				add_filter('comments_open', array(__CLASS__, 'disable_comments__filter_comment_status'), 20, 2 );
				add_filter('pings_open', array(__CLASS__, 'disable_comments__filter_comment_status'), 20, 2 );
			
				add_action('admin_head', array(__CLASS__, 'disable_comments__hide_discussion_rightnow'));
				add_action('admin_menu', array(__CLASS__, 'disable_comments__filter_admin_menu'), 9999);
				add_action('edit_form_advanced', array(__CLASS__, 'disable_comments__edit_form_inputs'));
				add_action('edit_page_form', array(__CLASS__, 'disable_comments__edit_form_inputs'));				
				add_action('wp_dashboard_setup', array(__CLASS__, 'disable_comments__filter_dashboard'));

				remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
			}
		}

		public static function disable_comments__discussion_js() {
			echo '<script> jQuery(document).ready(function($){ $("#dashboard_right_now .table_discussion").has(\'a[href="edit-comments.php"]\').first().hide(); }); </script>';
		}

		public static function disable_comments__edit_form_inputs() {
			global $post;
			if(in_array($post->post_type, array('page', 'post'))) {
				echo '<input type="hidden" name="comment_status" value="' . $post->comment_status . '" /><input type="hidden" name="ping_status" value="' . $post->ping_status . '" />';
			}
		}

		public static function disable_comments__hide_discussion_rightnow(){
			if('dashboard' == get_current_screen()->id) {
				add_action('admin_print_footer_scripts', array(__CLASS__, 'disable_comments__discussion_js'));
			}
		}

		public static function disable_comments__filter_admin_menu(){
			global $menu;
			if(isset($menu[25]) && $menu[25][2] == 'edit-comments.php') {
				unset($menu[25]);
			}
		}

		public static function disable_comments__filter_comment_status($open, $post_id) {
			return false;
		}

		public static function disable_comments__filter_dashboard() {
			remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		}

		public static function disable_comments__remove_widget() {
			if(self::comments_are_disabled()) {
				unregister_widget('WP_Widget_Recent_Comments');
			}
		}

		public static function enqueue_administrative_resources($hook) {
			wp_enqueue_style('urtak-backend', plugins_url('resources/backend/urtak.css', __FILE__), array(), self::VERSION);

			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			wp_enqueue_script('urtak-backend', plugins_url('resources/backend/urtak.js', __FILE__), array('jquery'), self::VERSION);
			wp_localize_script('urtak-backend', 'Urtak_Vars', array(
				'see_all' => __('See all...')
			));
		}

		public static function initialize_api_object() {
			self::$urtak_api = new WordPressUrtak(
				array(
					'api_key' => self::get_credentials('api-key'), 
					'email' => self::get_credentials('email'), 
					'publication_key' => self::get_credentials('publication-key')
				)
			);
		}

		public static function process_settings_actions() {
			$data = stripslashes_deep($_REQUEST);

			if(!empty($data['urtak-login-nonce']) && wp_verify_nonce($data['urtak-login-nonce'], 'urtak-login')) {
				self::_process_login($data['urtak-login-email'], $data['urtak-login-password']);
			} else if(!empty($data['urtak-logout-nonce']) && wp_verify_nonce($data['urtak-logout-nonce'], 'urtak-logout')) {
				self::_process_logout();
			} else if(!empty($data['save-urtak-settings-nonce']) && wp_verify_nonce($data['save-urtak-settings-nonce'], 'save-urtak-settings')) {
				self::_process_settings_save($data['urtak']);
			} else if(!empty($data['urtak-signup-nonce']) && wp_verify_nonce($data['urtak-signup-nonce'], 'urtak-signup')) {
				self::_process_signup($data['urtak-signup-email']);
			} 
		}

		public static function save_post_meta($post_id, $post) {
			$data = stripslashes_deep($_POST);
			if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !wp_verify_nonce($data['save-urtak-meta-nonce'], 'save-urtak-meta')) {
				return;
			}

			// Save associated Urtak
		}

		public static function show_credentials_notice() {
			$data = stripslashes_deep($_REQUEST);

			if(!self::has_credentials() && (!isset($data['page']) || !in_array($data['page'], array(self::SUB_LEVEL_INSIGHTS_SLUG, self::SUB_LEVEL_SETTINGS_SLUG)))) {
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
			include('views/backend/meta-boxes/meta-box.php');
		}

		public static function display_settings_page() {
			$data = stripslashes_deep($_REQUEST);
			$is_settings = true;
			$settings = self::get_settings();

			include('views/backend/misc/header.php');

			include('views/backend/settings/settings.php');

			include('views/backend/misc/footer.php');
		}


		//// META BOX DISPLAY CALLBACKS

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
			$post_ids = self::get_nonassociated_post_ids();
			$posts = new WP_Query(array('nopaging' => true, 'post__in' => $post_ids, 'post_type' => 'any', 'order' => 'ASC', 'orderby' => 'title'));

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

		/// SHORTCODE CALLBACKS

		/// POST META

		private static function get_nonassociated_post_ids() {
			global $wpdb;

			return $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID NOT IN(SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> %s)", self::URTAK_ID_KEY, ''));
		}

		private static function get_urtak_id($post_id) {
			if(empty($post_id)) {
				$post_id = get_the_ID();
			}

			$urtak_id = wp_cache_get(self::URTAK_ID_KEY, $post_id);

			if(false === $meta) {
				$urtak_id = get_post_meta($post_id, self::URTAK_ID_KEY, true);
				wp_cache_set(self::URTAK_ID_KEY, $urtak_id, $post_id, time() + self::CACHE_PERIOD);
			}

			return $urtak_id;
		}

		private static function set_urtak_id($post_id, $urtak_id) {
			if(empty($post_id)) {
				$post_id = get_the_ID();
			}

			update_post_meta($post_id, self::URTAK_ID_KEY, $urtak_id);
			wp_cache_set(self::POST_META_KEY, $urtak_id, $post_id, time() + self::CACHE_PERIOD);

			return $urtak_id;
		}

		/// SETTINGS

		private static function get_settings($settings_key = null) {
			$settings = wp_cache_get(self::SETTINGS_KEY);

			if(!is_array($settings)) {
				$settings = wp_parse_args(get_option(self::SETTINGS_KEY, self::$default_settings), self::$default_settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}

			return is_null($settings_key) ? $settings : 
					(isset($settings[$settings_key]) ? $settings[$settings_key] : false);
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

		/**
		 * Returns a hash of stored credentials or a single credential by name (depending
		 * on if the parameter is passed or not). Returns false if the credential doesn't 
		 * exist.
		 *
		 * @param string $credential_key The name of the credential from the has to return 
		 * @return false|mixed
		 */
		private static function get_credentials($credential_key = null) {
			$credentials = self::get_settings('credentials');

			return empty($credentials) ? false : 
						(is_null($credential_key) ? $credentials : 
							(isset($credentials[$credential_key]) ? $credentials[$credential_key] : false));
		}

		/**
		 * Returns a boolean value indicating whether credentials have been stored for the site
		 *
		 * @return bool If the site has stored credentials, then return true. Otherwise, return false.
		 */ 
		private static function has_credentials() {
			$credentials = self::get_credentials();

			return is_array($credentials) 
					&& isset($credentials['api-key']) 
					&& isset($credentials['email']) 
					&& isset($credentials['id']) 
					&& !empty($credentials['api-key']) 
					&& !empty($credentials['email'])
					&& !empty($credentials['id']);
		}

		/// COMMENTS

		private static function comments_are_disabled() {
			return 'yes' === self::get_settings('disable-comments');
		}

		/// API DELEGATES

		private static function has_pending_questions() {
			return self::get_pending_questions_count() > 0;
		}

		private static function get_pending_questions_count() {
			return 1500;
		}

		/// UTILITY

		private static function _get_insights_url() {
			return add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), admin_url('admin.php'));
		}

		private static function _get_login_url() {
			return add_query_arg(array('action' => 'login'), self::_get_settings_url());
		}

		private static function _get_logout_url() {
			return add_query_arg(array('action' => 'logout', 'urtak-logout-nonce' => wp_create_nonce('urtak-logout')), self::_get_settings_url());
		}

		private static function _get_settings_url() {
			return add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php'));
		}

		private static function _get_signup_url() {
			return add_query_arg(array('action' => 'signup'), self::_get_settings_url());
		}

		private static function _print_login_form($show, $data) {
			include('views/backend/misc/form-login.php');
		}

		private static function _print_signup_form($show, $data) {
			include('views/backend/misc/form-signup.php');
		}

		private static function _process_login($email, $password) {
			if(empty($email)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide an email address.'), 'error');
			} else if(!is_email($email)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide a valid email address.'), 'error');
			} 

			if(empty($password)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide a password.'), 'error');
			}

			if(!$error) {
				$account_response = self::$urtak_api->login_account($email, $password);

				if($account_response->success()) {
					add_settings_error('general', 'settings_updated', __('Your account was successfully retrieved and your credentials saved.'), 'updated');

					set_transient('settings_errors', get_settings_errors(), 30);
					wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
				} else {
					add_settings_error('general', 'settings_updated', __('Your account could not be created. Please try again.'), 'error');
				}
			}
		}

		private static function _process_logout() {
			$settings = self::get_settings();
			$settings['credentials'] = array();
			$settings = self::set_settings($settings);

			add_settings_error('general', 'settings_updated', __('Your credentials were successfully cleared.'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
			exit;
		}

		private static function _process_settings_save($settings) {
			$settings = apply_filters('urtak_pre_settings_save', $settings);
			$settings = self::set_settings($settings);

			add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
			exit;
		}

		private static function _process_signup($email) {
			if(empty($email)) {
				add_settings_error('general', 'settings_updated', __('Please provide an email address.'), 'error');
			} else if(!is_email($email)) {
				add_settings_error('general', 'settings_updated', __('Please provide a valid email address.'), 'error');
			} else {
				$account_response = self::$urtak_api->create_account(array('email' => $email));

				$redirect_url = add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url());
				if($account_response->success()) {
					$settings = self::get_settings();
					$settings['credentials'] = array(
						'api-key' => $account_response->body['account']['api_key'],
						'email' => $account_response->body['account']['email'],
						'id' => $account_response->body['account']['id'],
					);
					$settings = self::set_settings($settings);

					add_settings_error('general', 'settings_updated', __('Your account was successfully created and your credentials saved.'), 'updated');
				} else {
					$redirect_url = add_query_arg(array('action' => 'login'), $redirect_url);

					add_settings_error('general', 'settings_updated', __('An account with that email address already exists. Please login below.'), 'error');
				}

				set_transient('settings_errors', get_settings_errors(), 30);

				wp_redirect($redirect_url);
				exit;
			}
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
