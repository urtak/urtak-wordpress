<?php
/*
 Plugin Name: Urtak
 Plugin URI: http://urtak.com/wordpress/
 Description: An Urtak poll is the simplest and fastest way to find out what an online audience is thinking.
 Version: 2.0.0
 Author: Urtak, Inc.
 Author URI: http://urtak.com
 */

if(!class_exists('UrtakPlugin')) {
	class UrtakPlugin {
		/// CONSTANTS

		//// VERSION
		const VERSION = '2.0.0';

		//// KEYS
		const SETTINGS_KEY = '_urtak_settings';
		const FORCE_HIDE_KEY = '_urtak_never_show';
		const QUESTION_CREATED_KEY = '_urtak_created_question';

		//// SLUGS
		const TOP_LEVEL_PAGE_SLUG = 'urtak';
		const SUB_LEVEL_MODERATION_SLUG = 'urtak';
		const SUB_LEVEL_RESULTS_SLUG = 'urtak-results';
		const SUB_LEVEL_SETTINGS_SLUG = 'urtak-settings';

		//// CACHE
		const CACHE_PERIOD = 86400; // 24 HOURS

		/// DATA STORAGE
		private static $admin_page_hooks = array('index.php', 'post.php', 'post-new.php');
		private static $default_meta = array();
		private static $default_settings = array();
		private static $manage_page_ids = null;
		private static $manage_page_urtaks = null;
		private static $urtak_api = null;
		private static $urtaks_fetched = array();
		private static $urtaks_shown = array();

		public static function init() {
			load_plugin_textdomain('urtak', null, path_join(dirname(plugin_basename(__FILE__)), 'lang/'));

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
				add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
				add_action('save_post', array(__CLASS__, 'save_post_meta'), 10, 2);
				add_action('wp_dashboard_setup', array(__CLASS__, 'add_dashboard_widget'));
			}

			add_action('init', array(__CLASS__, 'initialize_api_object'));
			add_action('wp_head', array(__CLASS__, 'enqueue_frontend_resources'), 1);

			/// AJAX
			add_action('wp_ajax_urtak_display_meta_box__insights', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__questions', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__top_urtaks', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__stats', array(__CLASS__, 'ajax_display_meta_box'));

			//// Post editing meta box support
			add_action('wp_ajax_urtak_get_post_data', array(__CLASS__, 'ajax_get_post_data'));
			add_action('wp_ajax_urtak_get_post_title', array(__CLASS__, 'ajax_get_post_title'));

			//// For retrieving site wide and non-sitewide questions
			add_action('wp_ajax_urtak_get_questions', array(__CLASS__, 'ajax_get_questions'));

			//// For retrieving and modifying flags
			add_action('wp_ajax_urtak_get_flags', array(__CLASS__, 'ajax_get_flags'));
			add_action('wp_ajax_urtak_modify_flag', array(__CLASS__, 'ajax_modify_flag'));

			//// For retrieving urtaks
			add_action('wp_ajax_urtak_get_urtaks', array(__CLASS__, 'ajax_get_urtaks'));

			// These are needed for the frontend when we are dynamically loading response counts
			add_action('wp_ajax_urtak_fetch_responses_counts', array(__CLASS__, 'ajax_fetch_responses_count'));
			add_action('wp_ajax_nopriv_urtak_fetch_responses_counts', array(__CLASS__, 'ajax_fetch_responses_count'));

			// For post edit and moderation page
			add_action('wp_ajax_urtak_modify_question_first', array(__CLASS__, 'ajax_modify_question_first'));
			add_action('wp_ajax_urtak_modify_question_status', array(__CLASS__, 'ajax_modify_question_status'));
		}

		private static function add_filters() {
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'add_plugin_links'));
			add_filter('the_content', array(__CLASS__, 'automatically_append_urtak'), 10000);
			add_filter('urtak_pre_settings_save', array(__CLASS__, 'sanitize_and_validate_settings'));
		}

		private static function initialize_defaults() {
			// Empty credentials to stat
			self::$default_settings['credentials'] = array();

			self::$default_settings['has_first_question'] = 'no';

			// We want the Urtaks to appear by default, so let's append them
			self::$default_settings['placement'] = 'append';

			// We want to default post types to 'post' and 'page'
			self::$default_settings['post-types'] = array('page', 'post');

			// We want users to be able to start Urtaks by default
			self::$default_settings['user-start'] = 'yes';

			// We want Urtaks to support community moderation by default so that we get more questions and responses
			self::$default_settings['moderation'] = 'community';

			// Auto height and width
			self::$default_settings['height'] = '';
			self::$default_settings['width'] = '';

			// Counter settings
			self::$default_settings['counter-icon'] = 'yes';
			self::$default_settings['counter-responses'] = 'yes';

			// Profanity
			self::$default_settings['blacklisting'] = 'no';
			self::$default_settings['blacklist_override'] = 'no';
			self::$default_settings['blacklist_words'] = '';
		}

		private static function register_shortcodes() {
			add_shortcode('urtak', array(__CLASS__, 'shortcode_urtak'));
		}

		/// AJAX CALLBACKS

		public static function ajax_display_meta_box() {
			$data = stripslashes_deep($_REQUEST);
			$action = $data['action'];
			$method = substr($action, 6);

			if(method_exists(__CLASS__, $method)) {
				call_user_func(array(__CLASS__, $method), true);
			}

			exit;
		}

		public static function ajax_fetch_responses_count() {
			$data = stripslashes_deep($_REQUEST);

			$post_ids = array_unique(array_filter((array)$data['post_ids']));

			$non_mapped_urtaks = self::get_urtaks(array('post_ids' => $post_ids, 'per_page' => count($post_ids) + 1));
			$urtaks = array();
			foreach((array)$non_mapped_urtaks as $urtak) {
				$urtaks[$urtak['post_id']] = number_format_i18n((float)$urtak['responses_count'], 0);
			}

			echo json_encode(compact('urtaks'));
			exit;
		}

		public static function ajax_get_post_data() {
			$data = stripslashes_deep($_REQUEST);

			$atts = shortcode_atts(array(
				'page' => 1,
				'per_page' => 10,
				'order' => 'time|DESC',
				'show' => 'pub',
				'search' => '',
				'post_id' => 0,
				'default_question' => 0
			), $data);

			extract($atts);

			$post_data = false;
			$response = self::get_questions_response($page, $per_page, $order, $show, $search, $post_id);

			if(false === $response) {
				$data = array('error' => true, 'error_message' => __('Questions could not be retrieved.', 'urtak'));
			} else {
				$data = $response;

				// Transform the urtak
				$urtak = self::get_urtak($post_id);
				$data['questions']['urtak'] = $urtak ? $urtak : false;
			}

			echo json_encode($data);
			exit;
		}

		public static function ajax_get_post_title() {
			$data = stripslashes_deep($_REQUEST);

			$post = get_post($data['post_id']);

			echo json_encode(array('post_id' => $data['post_id'], 'post_title' => $post->post_title));
			exit;
		}

		public static function ajax_get_questions() {
			$data = stripslashes_deep($_REQUEST);

			$atts = shortcode_atts(array(
				'page' => 1,
				'per_page' => 10,
				'order' => 'time|DESC',
				'show' => 'st|all',
				'search' => '',
				'post_id' => 0
			), $data);

			extract($atts);

			$post_data = false;

			if($post_id) {
				$response = self::get_questions_response($page, $per_page, $order, $show, $search, $post_id);
			} else {
				$response = self::get_publication_questions_response($page, $per_page, $order, $show, $search);
			}

			if(false === $response) {
				$data = array('error' => true, 'error_message' => __('Questions could not be retrieved.', 'urtak'));
			} else {
				$data = $response;

				foreach($data['questions']['question'] as $key => $question) {
					$data['questions']['question'][$key]['nicedate'] = date(get_option('date_format'), $question['created_at']);
				}
			}

			echo json_encode($data);
			exit;
		}

		public static function ajax_get_flags() {
			$data = stripslashes_deep($_REQUEST);

			$atts = shortcode_atts(array(
				'page' => 1,
				'per_page' => 20,
			), $data);

			$flagged_questions = self::get_flags($atts);

			if(false === $flagged_questions) {
				$data = array('error' => true, 'error_message' => __('Flagged questions could not be retrieved.', 'urtak'));
			} else {
				$data = $flagged_questions;
			}

			echo json_encode($data);
			exit;
		}

		public static function ajax_modify_flag() {
			$data = stripslashes_deep($_REQUEST);

			$atts = shortcode_atts(array(
				'flag_id' => 0,
				'status' => 'agree',
			), $data);

			extract($atts);

			$flag = self::modify_flag_status($flag_id, $status);

			if(false === $flag) {
				$data = array('error' => true, 'error_message' => __('Flag could not be modified.', 'urtak'));
			} else {
				$data = $flag;
			}

			echo json_encode($data);
			exit;
		}

		public static function ajax_get_urtaks() {
			$data = stripslashes_deep($_REQUEST);
			$atts = shortcode_atts(array(
				'order' => 'n_responses|DESC',
				'page' => 1,
				'per_page' => 10,
			), $data);

			extract($atts);

			$urtaks = self::get_urtaks_response($atts);

			if($urtaks) {
				$data = $urtaks;

				foreach($data['urtaks']['urtak'] as $key => $urtak) {
					$post = get_post($urtak['post_id']);

					if(!$post) {
						continue;
					}

					$data['urtaks']['urtak'][$key]['editlink'] = get_edit_post_link($urtak['post_id'], 'raw');
					$data['urtaks']['urtak'][$key]['edittitle'] = get_the_title($urtak['post_id']);
					$data['urtaks']['urtak'][$key]['moderatelink'] = self::_get_moderation_url($urtak['post_id']);
					$data['urtaks']['urtak'][$key]['viewlink'] = get_permalink($urtak['post_id']);
					$data['urtaks']['urtak'][$key]['nicedate'] = date(get_option('date_format'), strtotime($post->post_date));
				}
			} else {
				$data = array('error' => true, 'error_message' => __('Could not retrieve Urtaks.'));
			}

			echo json_encode($data);
			exit;
		}

		public static function ajax_modify_question_first() {
			$data = stripslashes_deep($_REQUEST);

			$attributes = shortcode_atts(array(
				'first_question' => false,
				'post_id' => 0,
				'question_id' => 0,
			), $data);

			extract($attributes);

			$first_question = 'false' !== $first_question;

			$updated = self::update_urtak_question($post_id, $question_id, array('question' => array('first_question' => $first_question)));

			echo json_encode($updated);
			exit;
		}

		public static function ajax_modify_question_status() {
			$data = stripslashes_deep($_REQUEST);

			$attributes = shortcode_atts(array(
				'post_id' => 0,
				'question_id' => 0,
				'status' => 'approve',
			), $data);

			extract($attributes);

			$updated = self::update_urtak_question($post_id, $question_id, array('question' => array('state_status_admin_event' => $status)));

			if('approve' === $status) {
				update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
			}

			echo json_encode($updated);
			exit;
		}

		/// CALLBACKS

		public static function add_administrative_interface_items() {
			if(current_user_can('manage_options') || self::has_credentials()) {
				// Top Level
				self::$admin_page_hooks[] = $top_level = add_menu_page(__('Urtak Insights', 'urtak'), __('Urtak', 'urtak'), 'delete_others_pages', self::TOP_LEVEL_PAGE_SLUG, array(__CLASS__, 'display_moderation_page'), plugins_url('resources/backend/img/urtak-logo-15.png', __FILE__), 56);

				// Moderation
				self::$admin_page_hooks[] = $sub_level_moderation = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Moderation', 'urtak'), __('Moderation', 'urtak'), 'manage_options', self::SUB_LEVEL_MODERATION_SLUG, array(__CLASS__, 'display_moderation_page'));

				// Results
				self::$admin_page_hooks[] = $sub_level_results = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Results', 'urtak'), __('Results', 'urtak'), 'manage_options', self::SUB_LEVEL_RESULTS_SLUG, array(__CLASS__, 'display_results_page'));

				// Settings
				self::$admin_page_hooks[] = $sub_level_settings = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Settings', 'urtak'), __('Settings', 'urtak'), 'manage_options', self::SUB_LEVEL_SETTINGS_SLUG, array(__CLASS__, 'display_settings_page'));

				add_action("load-{$sub_level_settings}", array(__CLASS__, 'process_settings_actions'));
			}
		}

		public static function add_dashboard_widget() {
			if(current_user_can('delete_others_pages') && self::has_credentials()) {
				wp_add_dashboard_widget('urtak', __('At a Glance', 'urtak'), array(__CLASS__, 'display_meta_box__insights'));
			}
		}

		public static function add_posts_columns($columns) {
			if(self::has_credentials()) {
				$date = $columns['date'];
				unset($columns['date']);

				$columns['urtak-questions'] = __('Questions', 'urtak');
				$columns['urtak-responses'] = __('Responses', 'urtak');
				$columns['date'] = $date;
			}

			return $columns;
		}

		public static function add_posts_columns_output($column, $post_id) {
			if(is_null(self::$manage_page_ids)) {
				global $wp_query;

				self::$manage_page_ids = array();
				foreach((array)$wp_query->posts as $post) {
					self::$manage_page_ids[] = $post->ID;
				}
			}

			switch($column) {
				case 'urtak-responses':
				case 'urtak-questions':
					// We're going to cache all the stuff we need right now
					if(is_null(self::$manage_page_urtaks)) {
						if(empty(self::$manage_page_ids)) {
							$urtaks = array();
						} else {
							$urtaks = self::get_urtaks(array('post_ids' => self::$manage_page_ids));
						}

						self::$manage_page_urtaks = array();
						foreach((array)$urtaks as $urtak) {
							self::$manage_page_urtaks[$urtak['post_id']] = $urtak;
						}
					}

					$urtak = self::$manage_page_urtaks[$post_id];

					if(empty($urtak)) {
						_e('N/A', 'urtak');
					} else {
						if('urtak-responses' === $column) {
							echo number_format_i18n((float)$urtak['responses_count'], 0);
						} else {
							echo number_format_i18n((float)$urtak['approved_questions_count'], 0);
							if($urtak['pending_questions_count'] > 0) {
								printf('&nbsp;<span class="urtak-pending-count">+%s</span>', number_format_i18n((float)$urtak['pending_questions_count']));
							}
						}
					}
					break;
			}
		}

		public static function add_meta_boxes($post_type) {
			$settings = self::get_settings();

			if(is_array($settings['post-types']) && in_array($post_type, $settings['post-types'])) {
				add_meta_box('urtak-meta-box', __('Urtak', 'urtak'), array(__CLASS__, 'display_meta_box'), $post->post_type, 'normal');
			}
		}

		public static function add_plugin_links($links) {
			$new_links = array();

			if(self::has_credentials()) {
				$new_links[] = sprintf('<a href="%s">%s</a>', self::_get_moderation_url(), __('Moderation', 'urtak'));
				$new_links[] = sprintf('<a href="%s">%s</a>', self::_get_results_url(), __('Results', 'urtak'));
			}

			$new_links[] = $settings_link = sprintf('<a href="%s">%s</a>', self::_get_settings_url(), __('Settings', 'urtak'));

			return array_merge($new_links, $links);
		}

		public static function automatically_append_urtak($content) {
			if(in_array(get_post_type(), self::get_settings('post-types'))
				&& 'append' === self::get_settings('placement')
				&& (is_singular() || is_page() || (is_home() && 'yes' === self::get_settings('homepage')))) {

				$content .= urtak_get_embeddable_widget();
			}

			return $content;
		}


		public static function enqueue_administrative_resources($hook) {
			wp_enqueue_style('urtak-backend', plugins_url('resources/backend/urtak.css', __FILE__), array(), self::VERSION);

			if(!in_array($hook, self::$admin_page_hooks)) { return; }
			wp_enqueue_style('urtak-font', 'http://fonts.googleapis.com/css?family=Droid+Sans:400,700');

			wp_enqueue_script('jquery-flot', plugins_url('resources/backend/flot/jquery.flot.min.js', __FILE__), array('jquery'));
			wp_enqueue_script('jquery-flot-barnumbers', plugins_url('resources/backend/jquery.flot.barnumbers.js', __FILE__), array('jquery-flot'));

			wp_enqueue_script('knockout', plugins_url('resources/backend/knockout/knockout.js', __FILE__), array(), '2.2.1');

			wp_enqueue_script('urtak-backend', plugins_url('resources/backend/urtak.js', __FILE__), array('jquery', 'postbox', 'jquery-flot', 'jquery-flot-barnumbers', 'knockout'), self::VERSION);
			wp_localize_script('urtak-backend', 'Urtak_Vars', array(
				'see_all' => __('See all...', 'urtak'),
				'help_close' => __('Close', 'urtak'),
				'help_text' => __('Help', 'urtak'),
				'remove_question' => __('Are you sure you want to remove this question?', 'urtak'),
			));

			add_action('admin_print_footer_scripts', array(__CLASS__, 'print_excanvas_script'));
		}

		public static function enqueue_frontend_resources() {
			wp_enqueue_style('urtak-frontend', plugins_url('resources/frontend/urtak.css', __FILE__), array(), self::VERSION);
			wp_enqueue_script('urtak-frontend', plugins_url('resources/frontend/urtak.js', __FILE__), array('jquery'), self::VERSION);

			wp_localize_script('urtak-frontend', 'Urtak_Vars', array(
				'ajaxurl' => admin_url('admin-ajax.php')
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

		public static function print_excanvas_script() {
			$excanvas = plugins_url('resources/backend/flot/excanvas.min.js', __FILE__);
			include('views/backend/misc/excanvas.php');
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

		public static function sanitize_and_validate_settings($settings) {
			$settings['placement'] = 'manual' === $settings['placement'] ? 'manual' : 'append';

			$settings['default_first_question'] = trim($settings['default_first_question']);
			$settings['has_first_question'] = 'yes' === pd_yes_no($settings['has_first_question']) && !empty($settings['default_first_question']) ? 'yes' : 'no';
			$settings['default_first_question'] = 'no' === $settings['has_first_question'] ? '' : $settings['default_first_question'];

			$settings['homepage'] = pd_yes_no($settings['homepage']);

			$settings['user-start'] = pd_yes_no($settings['user-start']);

			$settings['post-types'] = array_filter(is_array($settings['post-types']) ? $settings['post-types'] : array());

			$settings['moderation'] = 'publisher' === $settings['moderation'] ? 'publisher' : 'community';

			$settings['height'] = is_numeric($settings['height']) ? intval($settings['height']) : '';
			$settings['height'] = is_int($settings['height']) && $settings['height'] < 180 ? 180 : $settings['height'];

			$settings['width'] = is_numeric($settings['width']) ? intval($settings['width']) : '';
			$settings['width'] = is_int($settings['width']) && $settings['width'] < 280 ? 280 : $settings['width'];

			$settings['blacklisting'] = pd_yes_no($settings['blacklisting']);
			$settings['blacklist_override'] = pd_yes_no($settings['blacklist_override']);

			$publication_fields = $settings['publication'];
			unset($settings['publication']);
			$publication_data = json_decode($publication_fields['publication-data'], true);

			$publication_attributes = array(
				'blacklisting' => 'yes' === $settings['blacklisting'],
				'blacklist_override' => 'yes' === $settings['blacklist_override'],
				'blacklist_words' => 'yes' === $settings['blacklist_override'] ? $settings['blacklist_words'] : '',
				'default_first_question_text' => $settings['default_first_question'],
				'email' => $settings['credentials']['email'],
				'moderation' => $settings['moderation'],
			);

			if(!empty($settings['credentials']['api-key']) && !empty($settings['credentials']['email'])) {
				$urtak_api = new WordPressUrtak(array('api_key' => $settings['credentials']['api-key'], 'email' => $settings['credentials']['email']));

				if(empty($settings['credentials']['publication-key'])) {
					$publication_attributes['domains'] = parse_url(home_url('/'), PHP_URL_HOST);
					$publication_attributes['name'] = get_bloginfo('name');

					$publication = self::create_or_get_publication_for_host($publication_attributes, $urtak_api);

					$settings['credentials']['publication-key'] = ($publication && isset($publication['key']) && !empty($publication['key'])) ? $publication['key'] : '';
				} else if(-1 == $settings['credentials']['publication-key']) {
					// Let's create a new site
					$publication_attributes['domains'] = empty($publication_fields['domains']) ? parse_url(home_url('/'), PHP_URL_HOST) : $publication_fields['domains'];
					$publication_attributes['name'] = $publication_fields['name'];

					$publication = self::create_publication($publication_attributes, $urtak_api);

					$settings['credentials']['publication-key'] = ($publication && isset($publication['key']) && !empty($publication['key'])) ? $publication['key'] : '';
				} else {
					$name = '';

					foreach($publication_data as $existing_publication) {
						if($settings['credentials']['publication-key'] == $existing_publication['key']) {
							$name = $existing_publication['name'];
						}
					}

					$publication_attributes['key'] = $settings['credentials']['publication-key'];
					$publication_attributes['domains'] = empty($publication_fields['domains']) ? parse_url(home_url('/'), PHP_URL_HOST) : $publication_fields['domains'];
					$publication_attributes['name'] = $name;

					$publication = self::update_publication($publication_attributes, $urtak_api);
				}
			}

			return $settings;
		}

		public static function save_post_meta($post_id, $post) {
			$data = stripslashes_deep($_POST);

			if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !wp_verify_nonce($data['save-urtak-meta-nonce'], 'save-urtak-meta')) {
				return;
			}

			if(!self::has_credentials() || !in_array($post->post_type, self::get_settings('post-types'))) {
				return;
			}

		    $args = array(
		      'post_id' => $post_id,
		      'permalink' => get_permalink($post_id),
		      'title' => $post->post_title,
		    );

		    $urtak_data = json_decode($data['urtak-serialized'], true);
		    $questions = isset($urtak_data['questions']) && is_array($urtak_data['questions']) ? $urtak_data['questions'] : array();

		    $new = array();
		    foreach($questions as $question) {
		    	if(!$question['existing']) {
		    		if(!empty($question['text'])) {
			    		$new[] = array(
			    			'first_question' => $question['first_question'] ? 1 : 0,
			    			'text' => $question['text'],
			    		);
		    		}
		    	}
		    }

			$urtak = self::get_urtak($post_id);
			if(empty($urtak)) {
				$urtak = self::create_urtak($args, $new);
				if($urtak && isset($urtak['id']) && !empty($urtak['id']) && !empty($new)) {
					update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
				} else {
					delete_post_meta($post_id, self::QUESTION_CREATED_KEY);
				}
			} else {
				$urtak_updated = self::update_urtak($args, $new);
				if($urtak && (!empty($new) || $urtak['approved_questions_count'] > 0)) {
					update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
				} else {
					delete_post_meta($post_id, self::QUESTION_CREATED_KEY);
				}
			}

			if(false === $urtak) {
				set_transient('urtak_failed_update_' . $post_id, array($new), 30);
			}

			if(isset($data['urtak-force-hide-urtak']) && 'yes' === $data['urtak-force-hide-urtak']) {
				update_post_meta($post_id, self::FORCE_HIDE_KEY, 'yes');
			} else {
				delete_post_meta($post_id, self::FORCE_HIDE_KEY);
			}
		}

		public static function show_credentials_notice() {
			$data = stripslashes_deep($_REQUEST);

			if(current_user_can('manage_options')
				&& !self::has_credentials()
				&& (!isset($data['page']) || !in_array($data['page'], array(self::SUB_LEVEL_MODERATION_SLUG, self::SUB_LEVEL_RESULTS_SLUG, self::SUB_LEVEL_SETTINGS_SLUG)))) {

				include('views/backend/misc/admin-notice.php');
			}
		}

		/// DISPLAY CALLBACKS

		//// PAGES

		private static function _display_page_header($current_tab) {
			$has_credentials = self::has_credentials();
			$manage_options = current_user_can('manage_options');

			$logged_in_text = '';
			if($has_credentials) {
				$logged_in_text .= sprintf(__('Logged in as <a href="https://urtak.com/account/edit" target="_blank">%1$s</a>', 'urtak'), self::get_credentials('email'));
				$logged_in_text .= "&nbsp;| &nbsp;";
			}

			$active_moderation = self::SUB_LEVEL_MODERATION_SLUG === $current_tab;
			$active_results = self::SUB_LEVEL_RESULTS_SLUG === $current_tab;
			$active_settings = self::SUB_LEVEL_SETTINGS_SLUG === $current_tab;

			$url_moderation = self::_get_moderation_url();
			$url_results = self::_get_results_url();
			$url_settings = self::_get_settings_url();

			include('views/backend/pages/_inc/header.php');
		}

		private static function _display_page_footer() {
			include('views/backend/pages/_inc/footer.php');
		}

		public static function display_moderation_page() {
			self::_display_page_header(self::SUB_LEVEL_MODERATION_SLUG);

			include('views/backend/pages/moderation.php');

			self::_display_page_footer();
		}

		public static function display_results_page() {
			self::_display_page_header(self::SUB_LEVEL_RESULTS_SLUG);

			include('views/backend/pages/results.php');

			self::_display_page_footer();
		}

		public static function display_settings_page() {
			$data = stripslashes_deep($_REQUEST);
			$is_settings = true;

			$settings = self::get_settings();

			if(self::has_credentials()) {
				$publications = self::get_publications();
			}

			if(self::has_credentials() && isset($settings['credentials']['publication-key'])) {
				$publication = self::get_publication($settings['credentials']['publication-key']);

				if($publication) {
					$settings['moderation'] = $publication['moderation'];
					$settings['default_first_question'] = $publication['default_first_question_text'];
					$settings['has_first_question'] = empty($settings['default_first_question']) ? 'no' : 'yes';
					$settings['blacklisting'] = isset($publication['blacklisting']) ? ($publication['blacklisting'] ? 'yes' : 'no') : 'no';
					$settings['blacklist_override'] = isset($publication['blacklist_override']) ? ($publication['blacklist_override'] ? 'yes' : 'no') : 'no';
					$settings['blacklist_words'] = isset($publication['blacklist_words']) ? $publication['blacklist_words'] : '';
				}
			}

			self::_display_page_header(self::SUB_LEVEL_SETTINGS_SLUG);

			include('views/backend/pages/settings.php');

			self::_display_page_footer();
		}


		//// META BOX DISPLAY CALLBACKS

		public static function display_meta_box($post) {
			$force_hide = get_post_meta($post->ID, self::FORCE_HIDE_KEY, true);
			$moderation_url = self::_get_moderation_url($post->ID);
			$results_url = self::_get_results_url($post->ID);

			include('views/backend/meta-boxes/meta-box.php');
		}

		public static function display_meta_box__insights($ajax = false) {
			if($ajax) {
				$publication = self::get_publication(self::get_credentials('publication-key'));

				$total_responses = 0;
				$total_questions = 0;
				$total_urtaks = 0;
				$today_data = $publication['statistics']['rpd_prev_14d'][13];
				$responses_today = $today_data['responses'];

				$days = array();
				$weeks = array();
				$months = array();
				if(!is_wp_error($publication) && isset($publication['statistics'])) {
					$total_responses = $publication['statistics']['total_responses'];
					$total_questions = $publication['statistics']['total_urtak_questions'];
					$total_urtaks = $publication['statistics']['total_urtaks'];

					foreach($publication['statistics']['rpd_prev_14d'] as $day_response_datum) {
						$days[] = array(
							'responses' => $day_response_datum['responses'],
							'date' => date('D,<b\r />M j', $day_response_datum['start_time'])
						);
					}

					foreach($publication['statistics']['rpw_prev_12w'] as $week_response_datum) {
						$weeks[] = array(
							'responses' => $week_response_datum['responses'],
							'date' => date('n/j', $week_response_datum['start_time']) . ' - ' . date('n/j', $week_response_datum['end_time'])
						);
					}

					foreach($publication['statistics']['rpm_prev_12m'] as $month_response_datum) {
						$months[] = array(
							'responses' => $month_response_datum['responses'],
							'date' => date('M Y', $month_response_datum['start_time'])
						);
					}
				}

				include('views/backend/insights/meta-boxes/at-a-glance.php');
			} else {
				self::echo_ajax_loading_action(__FUNCTION__);
			}
		}

		public static function display_meta_box__questions($ajax = false) {
			if($ajax) {
				$page = 1;
				$per_page = 5;

				$pending_response = self::get_publication_questions_response($page, $per_page, $order, 'st|pe');
				if($pending_response) {
					$pending = $pending_response['questions']['question'];
				} else {
					$pending = false;
				}

				$most_divided_response = self::get_publication_questions_response($page, $per_page, 'most_divided', 'st|aa');
				if($most_divided_response) {
					$most_divided = $most_divided_response['questions']['question'];
				} else {
					$most_divided = false;
				}

				$most_cared_response = self::get_publication_questions_response($page, $per_page, 'most_cared', 'st|aa');
				if($most_cared_response) {
					$most_cared = $most_cared_response['questions']['question'];
				} else {
					$most_cared = false;
				}

				$most_agreed_response = self::get_publication_questions_response($page, $per_page, 'most_agreed', 'st|aa');
				if($most_agreed_response) {
					$most_agreed = $most_agreed_response['questions']['question'];
				} else {
					$most_agreed = false;
				}

				include('views/backend/insights/meta-boxes/top-questions.php');
			} else {
				self::echo_ajax_loading_action(__FUNCTION__);
			}
		}

		public static function display_meta_box__stats($ajax = false) {
			if($ajax) {
				include('views/backend/insights/meta-boxes/stats.php');
			} else {
				self::echo_ajax_loading_action(__FUNCTION__);
			}
		}

		public static function display_meta_box__top_urtaks($ajax = false) {
			if($ajax) {
				$urtaks = self::get_urtaks(array('page' => 1, 'per_page' => 10, 'o' => 'n_responses'));
				if(is_array($urtaks)) {
					$urtaks = array_slice($urtaks, 0, 10);
				}

				include('views/backend/insights/meta-boxes/top-urtaks.php');
			} else {
				self::echo_ajax_loading_action(__FUNCTION__);
			}
		}

		private static function echo_ajax_loading_action($action) {
			printf('<div class="urtak-ajax-loader" data-action="urtak_%s"><img src="%s" alt="" /> %s</div>', $action, admin_url('images/wpspin_light.gif'), __('Loading...', 'urtak'));
		}

		/// SHORTCODE CALLBACKS

		public static function shortcode_urtak($atts = null, $content) {
			$atts = shortcode_atts(array('post_id' => 0, 'force' => true), $atts);

			return urtak_get_embeddable_widget($atts);
		}

		/// SETTINGS

		private static function get_settings($settings_key = null) {
			$settings = wp_cache_get(self::SETTINGS_KEY);

			if(!is_array($settings)) {
				$settings = wp_parse_args(get_option(self::SETTINGS_KEY, self::$default_settings), self::$default_settings);
				wp_cache_set(self::SETTINGS_KEY, $settings, null, time() + self::CACHE_PERIOD);
			}

			return is_null($settings_key) ? $settings : (isset($settings[$settings_key]) ? $settings[$settings_key] : false);
		}

		private static function set_settings($settings) {
			if(is_array($settings)) {
				$settings = apply_filters('urtak_pre_settings_save', wp_parse_args($settings, self::$default_settings));
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

		/// API DELEGATES

		private static function get_urtak_api($urtak_api) {
			if(!is_a($urtak_api, 'Urtak')) {
				$urtak_api = self::$urtak_api;
			}

			return $urtak_api;
		}

		//// Publications

		private static function _normalize_publication_attributes($publication_attributes) {
			return shortcode_atts(array(
				'blacklisting' => false,
				'blacklist_override' => false,
				'blacklist_words' => '',
				'default_first_question_text' => '',
				'domains' => '',
				'moderation' => 'community',
				'name' => '',
				'platform' => 'wordpress',
				'theme' => 15,
			), $publication_attributes);
		}

		private static function create_or_get_publication_for_host($publication_attributes, $urtak_api = null) {
			$publications = self::get_publications($urtak_api);

			$publication_attributes = self::_normalize_publication_attributes($publication_attributes);

			foreach($publications as $publication) {
				foreach($publication['hosts'] as $phost) {
					if($publication_attributes['domains'] === $phost['domains']) {
						return $publication;
					}
				}
			}

			return self::create_publication($publication_attributes, $urtak_api);
		}

		private static function create_publication($publication_attributes, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$email = isset($publication_attributes['email']) ? $publication_attributes['email'] : '';

			$publication_attributes = self::_normalize_publication_attributes($publication_attributes);

			$create_response = $urtak_api->create_publication('email', $email, $publication_attributes);

			return $create_response->success() ? $create_response->body['publication'] : false;
		}

		private static function get_publication($key, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$publication_response = $urtak_api->get_publication($key);

			return $publication_response->success() ? $publication_response->body['publication'] : false;
		}

		private static function get_publications($urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$publications = false;

			$page = 1;
			do {
				$publications_response = $urtak_api->get_publications(compact('page'));
				if($publications_response->success()) {
					if(!is_array($publications)) {
						$publications = array();
					}

					$publications = array_merge($publications, $publications_response->body['publications']['publication']);
					$page++;
				}
			} while($publications_response->success() && $page < $publications_response->body['publications']['pages']);

			return $publications;
		}

		private static function update_publication($publication_attributes, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$key = isset($publication_attributes['key']) ? $publication_attributes['key'] : '';

			$publication_attributes = self::_normalize_publication_attributes($publication_attributes);

			$update_response = $urtak_api->update_publication($key, $publication_attributes);

			return $update_response->success();
		}

		//// Questions

		private static function get_publication_count($key) {
			$statistics = get_transient('urtak_publication_statistics');

			if(false === $statistics) {
				$publication = self::get_publication(self::get_credentials('publication-key'));
				if($publication && isset($publication['statistics'])) {
					$statistics = $publication['statistics'];
					set_transient('urtak_publication_statistics', $statistics, 120);
				}
			}

			return $statistics && isset($statistics[$key]) ? $statistics[$key] : 0;
		}

		private static function get_questions_response($page, $per_page, $order, $show, $search, $post_id, $urtak_api = null) {
			if(empty($per_page)) {
				$per_page = 10;
			}

			$urtak_api = self::get_urtak_api($urtak_api);

			$args = array('f' => $show, 's' => $search, 'o' => $order, 'page' => $page, 'per_page' => $per_page);

			$questions_response = $urtak_api->get_urtak_questions('post_id', $post_id, $args);
			if($questions_response->success()) {
				$questions = (array)$questions_response->body;

				foreach($questions['questions']['question'] as $key => $question) {
					$questions['questions']['question'][$key]['nicedate'] = date(get_option('date_format') . ' \a\t ' . get_option('time_format'), $question['created_at']);
				}
			} else if(404 === intval($questions_response->code)) {
				// We're trapping this particular thing because we want to make sure not to provide an error
				// in case the Urtak for this post hasn't been created
				$questions = array('questions' => array('question' => array(), 'pages' => 1));
			} else {
				$questions = false;
			}

			return $questions;
		}

		private static function get_publication_questions_response($page, $per_page, $order, $show, $search = '', $urtak_api = null) {
			if(empty($per_page)) {
				$per_page = 10;
			}

			$urtak_api = self::get_urtak_api($urtak_api);

			$args = array('s' => $search, 'f' => $show, 'o' => $order, 'page' => $page, 'per_page' => $per_page);

			$questions_response = $urtak_api->get_publication_questions($args);
			if($questions_response->success()) {
				$questions = (array)$questions_response->body;

				foreach($questions['questions']['question'] as $key => $question) {
					$questions['questions']['question'][$key]['nicedate'] = date(get_option('date_format') . ' \a\t ' . get_option('time_format'), $question['created_at']);
					$questions['questions']['question'][$key]['post_id'] = $question['urtak']['post_id'];
				}
			} else if(404 === intval($questions_response->code)) {
				// We're trapping this particular thing because we want to make sure not to provide an error
				// in case the Urtak for this post hasn't been created
				$questions = array('questions' => array('question' => array(), 'pages' => 1));
			} else {
				$questions = false;
			}

			return $questions;
		}

		private static function update_urtak_question($post_id, $question_id, $options = array(), $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$update_response = $urtak_api->update_urtak_question('post_id', $post_id, $question_id, $options);

			return $update_response->success() ? $update_response->body['question'] : false;
		}

		//// Flagged Questions
		private static function get_flags($options, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$flagged_questions_response = $urtak_api->get_flags($options);

			return $flagged_questions_response->success() ? $flagged_questions_response->body : false;
		}

		private static function modify_flag_status($flag_id, $status, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$modify_flag_status_response = $urtak_api->modify_flag_status($flag_id, $status);

			return $modify_flag_status_response->success() ? $modify_flag_status_response->body : false;
		}

		//// Urtaks

		private static function create_urtak($args, $questions, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$urtak = false;
			$create_response = $urtak_api->create_urtak($args, $questions);
			if($create_response->success()) {
				$urtak = $create_response->body['urtak'];
			}

			return $urtak;
		}

		private static function get_urtak($post_id, $urtak_api = null) {
			$urtak = false;
			if(isset(self::$urtaks_fetched[$post_id])) {
				$urtak = self::$urtaks_fetched[$post_id];
			} else {
				$urtak_api = self::get_urtak_api($urtak_api);

				$urtak_response = $urtak_api->get_urtak('post_id', $post_id, array());
				if($urtak_response->success()) {
	 				$urtak = $urtak_response->body['urtak'];
				}

				self::$urtaks_fetched[$post_id] = $urtak;
			}


			return $urtak;
		}

		private static function get_urtaks_response($args = array(), $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			if(isset($args['per_page'])) {
				$args['per'] = $args['per_page'];
				unset($args['per_page']);
			}

			$urtaks_response = $urtak_api->get_urtaks($args);

			return $urtaks_response->success() ? $urtaks_response->body : false;
		}

		private static function get_urtaks($args = array(), $urtak_api = null) {
			$urtaks_response = self::get_urtaks_response($args, $urtak_api);

			if($urtaks_response) {
				$urtaks = $urtaks_response->body['urtaks']['urtak'];
				if(isset($urtaks['id'])) {
					$urtaks = array($urtaks);
				}

				foreach($urtaks as $urtak) {
					self::$urtaks_fetched[$urtak->post_id] = $urtak;
				}

				return $urtaks;
			} else {
				return false;
			}
		}

		private static function update_urtak($args, $questions, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$urtak = false;

			$args = array_merge($args, array('questions' => $questions));
			$update_response = $urtak_api->update_urtak('post_id', $args);
			if($update_response->success()) {
				$urtak = true;
			}

			return $urtak;
		}

		/// UTILITY

		//// LINKS

		///// PAGES

		private static function _build_hash($urtak_id, $question_id) {
			$urtak_id = is_null($urtak_id) || !is_numeric($urtak_id) ? 0 : intval($urtak_id);
			$question_id = is_null($question_id) || !is_numeric($question_id) ? 0 : intval($question_id);

			return empty($urtak_id) ? '' : sprintf('#%d-%d', $urtak_id, $question_id);
		}

		private static function _get_moderation_url($urtak_id = null, $question_id = null) {
			$url = add_query_arg(array('page' => self::SUB_LEVEL_MODERATION_SLUG), admin_url('admin.php'));

			return ($url . self::_build_hash($urtak_id, $question_id));
		}

		private static function _get_results_url($urtak_id = null, $question_id = null) {
			$url = add_query_arg(array('page' => self::SUB_LEVEL_RESULTS_SLUG), admin_url('admin.php'));

			return ($url . self::_build_hash($urtak_id, $question_id));
		}

		private static function _get_settings_url() {
			return add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), admin_url('admin.php'));
		}

		///// USER CREDENTIALS

		private static function _get_login_url() {
			return add_query_arg(array('action' => 'login'), self::_get_settings_url());
		}

		private static function _get_logout_url() {
			return add_query_arg(array('action' => 'logout', 'urtak-logout-nonce' => wp_create_nonce('urtak-logout')), self::_get_settings_url());
		}
		private static function _get_signup_url() {
			return add_query_arg(array('action' => 'signup'), self::_get_settings_url());
		}


		//// URTAK DATA

		private static function _get_card($question, $post_id, $controls = false, $use_nested_urtak = false) {
			ob_start();
			include('views/backend/misc/card.php');
			return ob_get_clean();
		}

		private static function _get_pager($current_page, $number_pages) {
			ob_start();
			include('views/backend/misc/pager.php');
			return ob_get_clean();
		}

		private static function _get_pie_image($percent) {
			return sprintf('<img src="%s" alt="%1$d%% %s" />', self::_get_pie_url($percent), $percent, __('No', 'urtak'));
		}

		private static function _get_pie_url($percent) {
			if(is_numeric($percent)) {
				$percent = sprintf('%02d', $percent);
			}
			return plugins_url(sprintf('resources/backend/img/assets/pie/pie60-%s.png', $percent), __FILE__);
		}

		private static function _print_login_form($show, $data) {
			include('views/backend/misc/form-login.php');
		}

		private static function _print_signup_form($show, $data) {
			include('views/backend/misc/form-signup.php');
		}

		//// PROCESSING DELEGATES

		private static function _process_login($email, $password) {
			if(empty($email)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide an email address.', 'urtak'), 'error');
			} else if(!is_email($email)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide a valid email address.', 'urtak'), 'error');
			}

			if(empty($password)) {
				$error = true;
				add_settings_error('general', 'settings_updated', __('Please provide a password.', 'urtak'), 'error');
			}

			if(!$error) {
				self::$urtak_api->initialize(array('email' => $email));
				$account_response = self::$urtak_api->login_account(compact('password'));

				if($account_response->success()) {
					add_settings_error('general', 'settings_updated', __('Your account was successfully retrieved and your credentials saved.', 'urtak'), 'updated');

					$settings = self::get_settings();
					$settings['credentials'] = array(
						'api-key' => $account_response->body['account']['api_key'],
						'email' => $account_response->body['account']['email'],
						'id' => $account_response->body['account']['id'],
					);
					$settings = self::set_settings($settings);

					set_transient('settings_errors', get_settings_errors(), 30);
					wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
				} else {
					add_settings_error('general', 'settings_updated', __('Your account could not be retrieved. Please check your credentials and try again.', 'urtak'), 'error');
				}
			}
		}

		private static function _process_logout() {
			$settings = self::get_settings();
			$settings['credentials'] = array();
			$settings = self::set_settings($settings);

			add_settings_error('general', 'settings_updated', __('Your credentials were successfully cleared.', 'urtak'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
			exit;
		}

		private static function _process_settings_save($settings) {
			$settings = self::set_settings($settings);

			add_settings_error('general', 'settings_updated', __('Settings saved.', 'urtak'), 'updated');
			set_transient('settings_errors', get_settings_errors(), 30);

			wp_redirect(add_query_arg(array('settings-updated' => 'true'), self::_get_settings_url()));
			exit;
		}

		private static function _process_signup($email) {
			if(empty($email)) {
				add_settings_error('general', 'settings_updated', __('Please provide an email address.', 'urtak'), 'error');
			} else if(!is_email($email)) {
				add_settings_error('general', 'settings_updated', __('Please provide a valid email address.', 'urtak'), 'error');
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

					add_settings_error('general', 'settings_updated', __('Your account was successfully created and your credentials saved.', 'urtak'), 'updated');
				} else {
					$redirect_url = add_query_arg(array('action' => 'login'), $redirect_url);

					add_settings_error('general', 'settings_updated', __('An account with that email address already exists. Please login below.', 'urtak'), 'error');
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
		 * - post_id - the id of the post that you want to show the Urtak for - defaults to current global post if available
		 *
		 * @param $args array An array of arguments to apply to the returned embed code.
		 */
		public static function get_embeddable_widget($args = array()) {
			$args = shortcode_atts(array('post_id' => 0, 'force' => false), $args);
			extract($args);

			if(empty($post_id)) {
				$post_id = get_the_ID();
			}

			if(empty($post_id)) {
				return '';
			}

			$should_show = $force || ('yes' !== get_post_meta($post_id, self::FORCE_HIDE_KEY, true)
										&& ('yes' === self::get_settings('user-start')
											|| 'yes' === get_post_meta($post_id, self::QUESTION_CREATED_KEY, true)));

			$should_show = $should_show && (!isset(self::$urtaks_shown[$post_id]));

			if(!$should_show) {
				return '';
			}

			self::$urtaks_shown[$post_id] = true;

			$height = self::get_settings('height');
			$permalink = get_permalink($post_id);
			$title = get_the_title($post_id);
			$publication_key = self::get_credentials('publication-key');
			$width = self::get_settings('width');

			ob_start();
			include('views/frontend/embed/script.php');
			return ob_get_clean();
		}

		public static function get_responses_number_markup($post_id) {
			if(empty($post_id)) {
				$post_id = get_the_ID();
			}

			$settings = self::get_settings();

			$icon_class = 'yes' === $settings['counter-icon'] ? 'urtak-responses-number-with-icon' : '';
			$responses_text = 'yes' === $settings['counter-responses'] ? __('responses', 'urtak') : '';

			return sprintf('<a href="%s#embedded-urtak-%d" data-post-id="%d" class="urtak-responses-number %s"><span class="urtak-responses-number-interior">...</span> %s</a>', get_permalink($post_id), $post_id, $post_id, $icon_class, $responses_text);
		}

	}

	require_once('lib/template-tags.php');
	require_once('lib/utility.php');

	require_once('lib/urtak-api.php');
	require_once('lib/wordpress-urtak-api.php');

	UrtakPlugin::init();
}
