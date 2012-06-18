<?php
/*
 Plugin Name: Plugin Skeleton
 Plugin URI: http://example.com
 Description: Make sure you put a description here.
 Version: 1.0.0-BETA1
 Author: Nick Ohrn of Plugin-Developer.com
 Author URI: http://plugin-developer.com/
 */

if(!class_exists('Plugin_Skeleton')) {
	class Plugin_Skeleton {
		/// CONSTANTS
		
		//// VERSION
		const VERSION = '1.0.0-BETA1';
		
		//// KEYS
		const POST_META_KEY = '_plugin_skeleton_post_meta';
		const SETTINGS_KEY = '_plugin_skeleton_settings';
		
		//// SLUGS
		const SETTINGS_PAGE_SLUG = 'plugin-skeleton-settings';
		
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
			
			register_activation_hook(__FILE__, array(__CLASS__, 'do_activation_actions'));
			register_deactivation_hook(__FILE__, array(__CLASS__, 'do_deactivation_actions'));
		}

		private static function add_actions() {
			if(is_admin()) {
				add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_administrative_resources'));
				add_action('admin_menu', array(__CLASS__, 'add_administrative_interface_items'));
				add_action('add_meta_boxes_post', array(__CLASS__, 'add_meta_boxes'));
			}
			
			add_action('init', array(__CLASS__, 'register_content_taxonomies'));
			add_action('init', array(__CLASS__, 'register_content_types'));
			add_action('save_post', array(__CLASS__, 'save_post_meta'), 10, 2);
		}

		private static function add_filters() {
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(__CLASS__, 'add_settings_link'));
		}

		private static function register_shortcodes() {

		}
		
		/// CALLBACKS
		
		public static function add_administrative_interface_items() {
			self::$admin_page_hooks[] = $settings = add_options_page(__('Plugin Skeleton Settings'), __('Plugin Skeleton'), 'manage_options', self::SETTINGS_PAGE_SLUG, array(__CLASS__, 'display_settings_page'));
			
			add_action("load-{$settings}", array(__CLASS__, 'process_settings_save'));
		}
		
		public static function add_meta_boxes($post) {
			add_meta_box('plugin-skeleton-meta-box', __('Plugin Skeleton'), array(__CLASS__, 'display_meta_box'), $post->post_type, 'normal');
		}
		
		public static function add_settings_link($links) {
			$settings_link = sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => self::SETTINGS_PAGE_SLUG), admin_url('options-general.php')), __('Settings'));
			
			return array('settings' => $settings_link) + $links;
		}
		
		public static function do_activation_actions() {
		
		}
		
		public static function do_deactivation_actions() {
		
		}
		
		public static function enqueue_administrative_resources($hook) {
			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			
			wp_enqueue_script('plugin-skeleton-backend', plugins_url('resources/backend/plugin-skeleton.js', __FILE__), array('jquery'), self::VERSION);
			wp_enqueue_style('plugin-skeleton-backend', plugins_url('resources/backend/plugin-skeleton.css', __FILE__), array(), self::VERSION);
		}
		
		public static function process_settings_save() {
			$data = stripslashes_deep($_POST);
			
			if(!empty($data['save-plugin-skeleton-settings-nonce']) && wp_verify_nonce($data['save-plugin-skeleton-settings-nonce'], 'save-plugin-skeleton-settings')) {
				
				add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
				set_transient('settings_errors', get_settings_errors(), 30);
				
				self::set_settings(apply_filters('plugin_skeleton_pre_settings_save', $data['plugin-skeleton']));
				wp_redirect(add_query_arg(array('page' => self::SETTINGS_PAGE_SLUG, 'settings-updated' => 'true'), admin_url('options-general.php')));
				exit;
			}
		}
		
		public static function register_content_taxonomies() {
		
		}
		
		public static function register_content_types() {
		
		}
		
		public static function save_post_meta($post_id, $post) {
			$data = stripslashes_deep($_POST);
			if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !wp_verify_nonce($data['save-plugin-skeleton-meta-nonce'], 'save-plugin-skeleton-meta')) {
				return;
			}
			
			self::set_meta($post_id, apply_filters('plugin_skeleton_pre_meta_save', $data['plugin-skeleton']));
		}
		
		/// DISPLAY CALLBACKS
		
		public static function display_meta_box($post) {
			$meta = self::get_meta($post->ID);
			
			include('views/backend/meta-boxes/meta-box.php');
		}
		
		public static function display_settings_page() {
			$settings = self::get_settings();
			
			include('views/backend/settings/settings.php');
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
		
		/// TEMPLATE TAGS
		
		public static function get_string($string) {
			return (string)$string;
		}

	}
	
	require_once('lib/template-tags.php');
	require_once('lib/utility.php');
	Plugin_Skeleton::init();
}
