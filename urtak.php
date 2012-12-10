<?php
/*
 Plugin Name: Urtak
 Plugin URI: http://urtak.com/wordpress/
 Description: Conversation powered by questions. Bring simplicity and structure to any online conversation by allowing your users to ask each other questions.
 Version: 1.3.0
 Author: Urtak, Inc.
 Author URI: http://urtak.com
 */

if(!class_exists('UrtakPlugin')) {
	class UrtakPlugin {
		/// CONSTANTS

		//// VERSION
		const VERSION = '1.3.0';

		//// KEYS
		const SETTINGS_KEY = '_urtak_settings';
		const FORCE_HIDE_KEY = '_urtak_never_show';
		const QUESTION_CREATED_KEY = '_urtak_created_question';

		//// SLUGS
		const TOP_LEVEL_PAGE_SLUG = 'urtak';
		const SUB_LEVEL_INSIGHTS_SLUG = 'urtak';
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
				add_action('add_meta_boxes_page', array(__CLASS__, 'add_meta_boxes'));
				add_action('add_meta_boxes_post', array(__CLASS__, 'add_meta_boxes'));
				add_action('manage_posts_custom_column', array(__CLASS__, 'add_posts_columns_output'), 10, 2);
				add_action('save_post', array(__CLASS__, 'save_post_meta'), 10, 2);
				add_action('wp_dashboard_setup', array(__CLASS__, 'add_dashboard_widget'));
			}

			add_action('admin_bar_menu', array(__CLASS__, 'add_admin_bar_items'), 35);
			add_action('init', array(__CLASS__, 'initialize_api_object'));
			add_action('wp_head', array(__CLASS__, 'enqueue_frontend_resources'), 1);

			/// AJAX
			add_action('wp_ajax_urtak_display_meta_box__dashboard', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__insights', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__questions', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__top_urtaks', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__stats', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_display_meta_box__posts_without_urtaks', array(__CLASS__, 'ajax_display_meta_box'));
			add_action('wp_ajax_urtak_get_questions', array(__CLASS__, 'ajax_get_questions'));
			add_action('wp_ajax_urtak_fetch_responses_counts', array(__CLASS__, 'ajax_fetch_responses_count'));
			add_action('wp_ajax_nopriv_urtak_fetch_responses_counts', array(__CLASS__, 'ajax_fetch_responses_count'));
			add_action('wp_ajax_urtak_modify_question_first', array(__CLASS__, 'ajax_modify_question_first'));
			add_action('wp_ajax_urtak_modify_question_status', array(__CLASS__, 'ajax_modify_question_status'));
		}

		private static function add_filters() {
			add_filter('manage_edit-post_columns', array(__CLASS__, 'add_posts_columns'));
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

		public static function ajax_get_questions() {
			$data = stripslashes_deep($_REQUEST);
			$atts = shortcode_atts(array('page' => 1, 'per_page' => 10, 'order' => 'time|DESC', 'show' => 'all', 'search' => '', 'post_id' => 0, 'default_question' => 0), $data);

			extract($atts);
			if(empty($post_id)) {
				$data = array('error' => true, 'error_message' => __('No post id was provided so the appropriate questions could not be retrieved.', 'urtak'));
			} else {
				$questions_response = self::get_questions_response($page, $per_page, $order, $show, $search, $post_id);

				if(false === $questions_response) {
					$data = array('error' => true, 'error_message' => __('The questions for the Urtak related to this post could not be retrieved. Please try again later.', 'urtak'));
				} else {
					if('st|ap' === $show && empty($search) && empty($questions_response['questions']['question'])) {
						delete_post_meta($post_id, self::QUESTION_CREATED_KEY);
					} else if('st|ap' === $show && !empty($questions_response['questions']['question'])) {
						update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
					}

					$cards = '';
					foreach($questions_response['questions']['question'] as $question) {
						$cards .= self::_get_card($question, $post_id, true);
					}

					$pager = self::_get_pager($page, $questions_response['questions']['pages']);

					$default_first_question_text = '';
					if($default_question && empty($cards)) {
						$publication = self::get_publication(self::get_credentials('publication-key'));

						$default_first_question_text = $publication['default_first_question_text'];
					}

					$data = compact('pager', 'cards', 'default_first_question_text');
				}
			}

			echo json_encode($data);
			exit;
		}


		public static function ajax_modify_question_first() {
			$data = stripslashes_deep($_REQUEST);

			$first_question = 1 == $data['first_question'];

			self::update_urtak_question($data['post_id'], $data['question_id'], array('question' => array('first_question' => $first_question)));
		}

		public static function ajax_modify_question_status() {
			$data = stripslashes_deep($_REQUEST);

			$approved_questions = array();
			$questions = (array)$data['questions'];

			$updated = array();
			foreach($questions as $question) {
				if('approve' === $question['action']) {
					$approved_questions[] = $question['post_id'];
				}

				$updated[$question['question_id']] = self::update_urtak_question($question['post_id'], $question['question_id'], array('question' => array('state_status_admin_event' => $question['action'])));
			}

			foreach(array_unique($approved_questions) as $approved_question_post_id) {
				update_post_meta($approved_question_post_id, self::QUESTION_CREATED_KEY, 'yes');
			}

			echo json_encode($updated);
			exit;
		}

		/// CALLBACKS

		public static function add_admin_bar_items($wp_admin_bar) {
			if(is_admin() && self::has_credentials() && '' != self::get_credentials('publication-key')) {
				$pending_questions_count = self::get_publication_count('pending_questions');
				if($pending_questions_count) {
					$wp_admin_bar->add_menu( array(
						'id' => 'urtak',
						'title' => sprintf(__('+%s', 'urtak'), number_format_i18n($pending_questions_count)),
						'href' => self::_get_insights_url()
					));
				}
			}
		}

		public static function add_administrative_interface_items() {
			if(current_user_can('manage_options') || self::has_credentials()) {
				self::$admin_page_hooks[] = $top_level = add_menu_page(__('Urtak Insights', 'urtak'), __('Urtak', 'urtak'), 'delete_others_pages', self::TOP_LEVEL_PAGE_SLUG, array(__CLASS__, 'display_insights_page'), plugins_url('resources/backend/img/urtak-logo-15.png', __FILE__), 56);
				self::$admin_page_hooks[] = $sub_level_insights = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Insights', 'urtak'), __('Insights', 'urtak'), 'delete_others_pages', self::SUB_LEVEL_INSIGHTS_SLUG, array(__CLASS__, 'display_insights_page'));
				self::$admin_page_hooks[] = $sub_level_settings = add_submenu_page(self::TOP_LEVEL_PAGE_SLUG, __('Urtak Settings', 'urtak'), __('Settings', 'urtak'), 'manage_options', self::SUB_LEVEL_SETTINGS_SLUG, array(__CLASS__, 'display_settings_page'));

				add_action("load-{$sub_level_settings}", array(__CLASS__, 'process_settings_actions'));

				add_meta_box('urtak-at-a-glance', __('At a Glance', 'urtak'), array(__CLASS__, 'display_meta_box__insights'), 'urtak', 'top');

				$posts_without_urtaks = self::get_nonassociated_post_ids();
				if(!empty($posts_without_urtaks)) {
					add_meta_box('urtak-posts-without-urtaks', __('Posts without Urtak', 'urtak'), array(__CLASS__, 'display_meta_box__posts_without_urtaks'), 'urtak', 'left');
				}


				add_meta_box('urtak-top-questions', __('Questions', 'urtak'), array(__CLASS__, 'display_meta_box__questions'), 'urtak', 'right');
			}
		}

		public static function add_dashboard_widget() {
			if(current_user_can('delete_others_pages') && self::has_credentials()) {
				wp_add_dashboard_widget('urtak', __('Urtak', 'urtak'), array(__CLASS__, 'display_meta_box__dashboard'));
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

		public static function add_meta_boxes($post) {
			add_meta_box('urtak-meta-box', __('Urtak', 'urtak'), array(__CLASS__, 'display_meta_box'), $post->post_type, 'normal');
		}

		public static function add_plugin_links($links) {
			$new_links = array();

			if(self::has_credentials()) {
				$new_links[] = $insights_link = sprintf('<a href="%s">%s</a>', self::_get_insights_url(), __('Insights', 'urtak'));
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

			wp_enqueue_script('urtak-backend', plugins_url('resources/backend/urtak.js', __FILE__), array('jquery', 'postbox', 'jquery-flot', 'jquery-flot-barnumbers'), self::VERSION);
			wp_localize_script('urtak-backend', 'Urtak_Vars', array(
				'see_all' => __('See all...', 'urtak'),
				'help_close' => __('Close', 'urtak'),
				'help_text' => __('Help', 'urtak')
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

			$publication_fields = $settings['publication'];
			unset($settings['publication']);
			$publication_data = json_decode($publication_fields['publication-data'], true);

			if(!empty($settings['credentials']['api-key']) && !empty($settings['credentials']['email'])) {
				$urtak_api = new WordPressUrtak(array('api_key' => $settings['credentials']['api-key'], 'email' => $settings['credentials']['email']));

				if(empty($settings['credentials']['publication-key'])) {
					$host = parse_url(home_url('/'), PHP_URL_HOST);
					$name = get_bloginfo('name');

					$publication = self::create_or_get_publication_for_host(
											$name,
											$host,
											$settings['default_first_question'],
											$settings['moderation'],
											$settings['credentials']['email'],
											$urtak_api);

					if($publication && isset($publication['key']) && !empty($publication['key'])) {
						$settings['credentials']['publication-key'] = $publication['key'];
					}
				} else if(-1 == $settings['credentials']['publication-key']) {
					// Let's create a new site
					$name = $publication_fields['name'];
					$host = empty($publication_fields['domains']) ? parse_url(home_url('/'), PHP_URL_HOST) : $publication_fields['domains'];

					$publication = self::create_publication(
											$name,
											$host,
											$settings['default_first_question'],
											$settings['moderation'],
											$settings['credentials']['email'],
											$urtak_api);

					if($publication && isset($publication['key']) && !empty($publication['key'])) {
						$settings['credentials']['publication-key'] = $publication['key'];
					} else {
						$settings['credentials']['publication-key'] = '';
					}
				} else {
					$name = '';
					foreach($publication_data as $existing_publication) {
						if($settings['credentials']['publication-key'] == $existing_publication['key']) {
							$name = $existing_publication['name'];
						}
					}

					$host = empty($publication_fields['domains']) ? parse_url(home_url('/'), PHP_URL_HOST) : $publication_fields['domains'];

					$publication = self::update_publication(
											$name,
											$host,
											$settings['default_first_question'],
											$settings['moderation'],
											$settings['credentials']['publication-key'],
											$urtak_api);
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
		      'post_id'     => $post_id,
		      'permalink'   => get_permalink($post_id),
		      'title'       => $post->post_title,
		    );

		    $question_texts = array();
		    $new_questions = array();
		    foreach((array)$data['urtak']['question']['text'] as $key => $question_text) {
		    	if(!empty($question_text) && !in_array($question_text, $question_texts)) {
			    	$new_questions[] = array(
			    		'text' => $question_text,
			    		'first_question' => 1 == $data['urtak']['question']['first_question'][$key] ? '1' : '0',
			    	);
			    	$question_texts[] = $question_text;
		    	}
		    }

			$urtak = self::get_urtak($post_id);
			if(empty($urtak)) {
				$urtak = self::create_urtak($args, $new_questions);
				if($urtak && isset($urtak['id']) && !empty($urtak['id']) && !empty($new_questions)) {
					update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
				}
			} else {
				$urtak = self::update_urtak($args, $new_questions);
				if($urtak && !empty($new_questions)) {
					update_post_meta($post_id, self::QUESTION_CREATED_KEY, 'yes');
				}
			}

			if(false === $urtak) {
				set_transient('urtak_failed_update_' . $post_id, array($new_questions), 30);
			}

			if(isset($data['urtak-force-hide-urtak']) && 'yes' === $data['urtak-force-hide-urtak']) {
				update_post_meta($post_id, self::FORCE_HIDE_KEY, 'yes');
			} else {
				delete_post_meta($post_id, self::FORCE_HIDE_KEY);
			}
		}

		public static function show_credentials_notice() {
			$data = stripslashes_deep($_REQUEST);

			if(current_user_can('manage_options') && !self::has_credentials() && (!isset($data['page']) || !in_array($data['page'], array(self::SUB_LEVEL_INSIGHTS_SLUG, self::SUB_LEVEL_SETTINGS_SLUG)))) {
				include('views/backend/misc/admin-notice.php');
			}
		}

		/// DISPLAY CALLBACKS

		public static function display_meta_box__dashboard($ajax = false) {
			if($ajax) {
				$publication = self::get_publication(self::get_credentials('publication-key'));

				$days = array();
				if(!is_wp_error($publication) && isset($publication['statistics'])) {
					$total_responses = $publication['statistics']['total_responses'];
					$total_questions = $publication['statistics']['total_urtak_questions'];
					$total_urtaks = $publication['statistics']['total_urtaks'];

					foreach(array_slice($publication['statistics']['rpd_prev_14d'], -7) as $day_response_datum) {
						$days[] = array(
							'responses' => $day_response_datum['responses'],
							'date' => date('D,<b\r />M j', $day_response_datum['start_time'])
						);
					}
				}

				include('views/backend/dashboard/widget.php');
			} else {
				self::echo_ajax_loading_action(__FUNCTION__);
			}
		}

		public static function display_insights_page() {
			$is_insights = true;
			$settings = self::get_settings();

			include('views/backend/misc/header.php');

			if(self::has_credentials()) {
				include('views/backend/insights/insights.php');
			} else {
				include('views/backend/misc/admin-notice.php');
			}

			include('views/backend/misc/footer.php');
		}

		public static function display_meta_box($post) {
			$force_hide = get_post_meta($post->ID, self::FORCE_HIDE_KEY, true);

			include('views/backend/meta-boxes/meta-box.php');
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
				}
			}

			include('views/backend/misc/header.php');

			include('views/backend/settings/settings.php');

			include('views/backend/misc/footer.php');
		}


		//// META BOX DISPLAY CALLBACKS

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

		public static function display_meta_box__posts_without_urtaks($ajax = false) {
			if($ajax) {
				$post_ids = self::get_nonassociated_post_ids();
				$posts = new WP_Query(array('nopaging' => true, 'post__in' => $post_ids, 'post_type' => self::get_settings('post-types'), 'order' => 'ASC', 'orderby' => 'title'));

				include('views/backend/insights/meta-boxes/posts-without-urtaks.php');
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

		/// POST META

		private static function get_nonassociated_post_ids() {
			global $wpdb;

			return $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('page', 'post') AND ID NOT IN(SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s)", self::QUESTION_CREATED_KEY, 'yes'));
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

		private static function create_or_get_publication_for_host($name, $host, $default_first_question, $moderation, $email, $urtak_api = null) {
			$publications = self::get_publications($urtak_api);

			foreach($publications as $publication) {
				foreach($publication['hosts'] as $phost) {
					if($host === $phost['host']) {
						return $publication;
					}
				}
			}

			// There wasn't an existing item, so we need to create one
			return self::create_publication($name, $host, $default_first_question, $moderation, $email, $urtak_api);
		}

		private static function create_publication($name, $host, $default_first_question, $moderation, $email, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$publication_args = array(
			    'domains'    => $host,
			    'name'       => $name,
			    'platform'   => 'wordpress',
			    'moderation' => $moderation,
			    'default_first_question_text' => $default_first_question,
			    'theme'      => 15
			);
			$create_response = $urtak_api->create_publication('email', $email, $publication_args);

			$publication = false;
			if($create_response->success()) {
				$publication = $create_response->body['publication'];
			}

			return $publication;
		}

		private static function get_publication($key, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$publication = false;
			$publication_response = $urtak_api->get_publication($key);
			if($publication_response->success()) {
				$publication = $publication_response->body['publication'];
			}

			return $publication;
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

		private static function update_publication($name, $host, $default_first_question, $moderation, $key, $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$publication_args = array(
				'domains' => $host,
				// 'name' => $name,
				'platform' => 'wordpress',
				'moderation' => $moderation,
				'default_first_question_text' => $default_first_question,
				'theme' => 15
			);
			$update_response = $urtak_api->update_publication($key, $publication_args);

			$publication = false;
			if($update_response->success()) {
				$publication = true;
			}

			return $publication;
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
			} else if(404 === intval($questions_response->code)) {
				// We're trapping this particular thing because we want to make sure not to provide an error
				// in case the Urtak for this post hasn't been created
				$questions = array('questions' => array('question' => array(), 'pages' => 1));
			} else {
				$questions = false;
			}

			return $questions;
		}

		private static function get_publication_questions_response($page, $per_page, $order, $show, $urtak_api = null) {
			if(empty($per_page)) {
				$per_page = 10;
			}

			$urtak_api = self::get_urtak_api($urtak_api);

			$args = array('f' => $show, 'o' => $order, 'page' => $page, 'per_page' => $per_page);

			$questions_response = $urtak_api->get_publication_questions($args);
			if($questions_response->success()) {
				$questions = (array)$questions_response->body;
			} else if(404 === intval($questions_response->code)) {
				// We're trapping this particular thing because we want to make sure not to provide an error
				// in case the Urtak for this post hasn't been created
				$questions = array('questions' => array('question' => array(), 'pages' => 1));
			} else {
				$questions = false;
			}

			return $questions;
		}

		private static function update_urtak_question($post_id, $question_id, $options = array(), $urtak = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$updated = false;
			$update_response = $urtak_api->update_urtak_question('post_id', $post_id, $question_id, $options);
			if($update_response->success()) {
				$updated = true;
			}

			return $updated;
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

		private static function get_urtaks($args = array(), $urtak_api = null) {
			$urtak_api = self::get_urtak_api($urtak_api);

			$urtaks = false;
			$urtaks_response = $urtak_api->get_urtaks($args);
			if($urtaks_response->success()) {
				$urtaks = $urtaks_response->body['urtaks']['urtak'];
				if(isset($urtaks['id'])) {
					$urtaks = array($urtaks);
				}

				foreach($urtaks as $urtak) {
					self::$urtaks_fetched[$urtak->post_id] = $urtak;
				}
			}

			return $urtaks;
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

			if(!$should_show) {
				return '';
			}


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
