<?php
/*
 Plugin Name: Urtak
 Plugin URI: http://urtak.com
 Description: Urtak is collaborative polling - everyone can ask questions. It's easy to engage a great number of people in a structured conversation that produces thousands of responses.
 Version: 2.0.0-RC1
 Author: Urtak, Inc.
 Author URI: http://urtak.com
 */

if(!class_exists('Urtak')) {
	class Urtak {
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

		public static function init() {
			self::add_actions();
			self::add_filters();
			self::register_shortcodes();
		}

		private static function add_actions() {
			if(is_admin()) {
				add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_administrative_resources'));
				add_action('admin_menu', array(__CLASS__, 'add_administrative_interface_items'));
				add_action('admin_notices', array(__CLASS__, 'show_credentials_notice'));
				add_action('add_meta_boxes_post', array(__CLASS__, 'add_meta_boxes'));
			}

			add_action('save_post', array(__CLASS__, 'save_post_meta'), 10, 2);
		}

		private static function add_filters() {
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_plugin_links'));
		}

		private static function register_shortcodes() {

		}

		/// CALLBACKS

		public static function add_administrative_interface_items() {
			self::$admin_page_hooks[] = $top_level = add_menu_page(__('Urtak Insights'), __('Urtak'), 'manage_options', self::TOP_LEVEL_PAGE_SLUG, array(__CLASS__, 'display_insights_page'), plugins_url('resources/backend/img/urtak-logo-15.png', __FILE__), 56);
			self::$admin_page_hooks[] = $sub_level_insights = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Insights'), __('Insights'), 'manage_options', self::SUB_LEVEL_INSIGHTS_SLUG, array(__CLASS__, 'display_insights_page'));
			self::$admin_page_hooks[] = $sub_level_settings = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Settings'), __('Settings'), 'manage_options', self::SUB_LEVEL_SETTINGS_SLUG, array(__CLASS__, 'display_settings_page'));

			add_action("load-{$sub_level_settings}", array(__CLASS__, 'process_settings_save'));
		}

		public static function add_meta_boxes($post) {
			add_meta_box('urtak-meta-box', __('Urtak'), array(__CLASS__, 'display_meta_box'), $post->post_type, 'normal');
		}

		public static function add_plugin_links($links) {
			$insights_link = sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), admin_url('admin.php')), __('Insights'));
			$settings_link = sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php')), __('Settings'));

			return array_merge(array('insights' => $insights_link, 'settings' => $settings_link), $links);
		}

		public static function enqueue_administrative_resources($hook) {
			wp_enqueue_style('urtak-backend', plugins_url('resources/backend/urtak.css', __FILE__), array(), self::VERSION);

			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			wp_enqueue_script('urtak-backend', plugins_url('resources/backend/urtak.js', __FILE__), array('jquery'), self::VERSION);
		}

		public static function process_settings_save() {
			$data = stripslashes_deep($_POST);

			if(!empty($data['save-urtak-settings-nonce']) && wp_verify_nonce($data['save-urtak-settings-nonce'], 'save-urtak-settings')) {

				add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
				set_transient('settings_errors', get_settings_errors(), 30);

				self::set_settings(apply_filters('urtak_pre_settings_save', $data['urtak']));
				wp_redirect(add_query_arg(array('page' => self::SETTINGS_PAGE_SLUG, 'settings-updated' => 'true'), admin_url('options-general.php')));
				exit;
			}
		}

		public static function save_post_meta($post_id, $post) {
			$data = stripslashes_deep($_POST);
			if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !wp_verify_nonce($data['save-urtak-meta-nonce'], 'save-urtak-meta')) {
				return;
			}

			self::set_meta($post_id, apply_filters('urtak_pre_meta_save', $data['urtak']));
		}

		public static function show_credentials_notice() {
			$data = stripslashes_deep($_REQUEST);

			if(!self::has_credentials() && (!isset($data['page']) || !in_array($data['page'], array(self::SUB_LEVEL_INSIGHTS_SLUG, self::SUB_LEVEL_SETTINGS_SLUG)))) {
				include('views/backend/misc/admin-notice.php');
			}
		}

		/// DISPLAY CALLBACKS

		public static function display_insights_page() {
			$is_insights = true;
			$settings = self::get_settings();

			include('views/backend/settings/header.php');
			include('views/backend/settings/insights.php');
			include('views/backend/settings/footer.php');
		}

		public static function display_meta_box($post) {
			$meta = self::get_meta($post->ID);

			include('views/backend/meta-boxes/meta-box.php');
		}

		public static function display_settings_page() {
			$is_settings = true;
			$settings = self::get_settings();

			include('views/backend/settings/header.php');
			include('views/backend/settings/settings.php');
			include('views/backend/settings/footer.php');
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
		}

		/// UTILITY

		private static function has_credentials() {
			return false;
		}

		/// TEMPLATE TAGS

		public static function get_embeddable_widget($args = array()) {
			// TODO Actual return the embed code for the
			// widget based on the arguments provided

			return '';
		}

	}

	require_once('lib/template-tags.php');
	require_once('lib/utility.php');
	Urtak::init();
}
