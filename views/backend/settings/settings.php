<?php if(!self::has_credentials()) { ?>

	<?php $action = (isset($data['action']) && 'login' === $data['action']) ? 'login' : 'signup'; ?>

	<?php self::_print_login_form('login' === $action, $data); ?>

	<?php self::_print_signup_form('signup' === $action, $data); ?>

<?php } else { ?>

<form class="urtak-settings" method="post" action="<?php esc_url(add_query_arg(array())); ?>">

	<div class="urtak-settings-section urtak-settings-section-left">

	<?php if(self::has_credentials()) { ?>

		<?php foreach($settings['credentials'] as $credential_key => $credential_value) { ?>
		<input type="hidden" name="urtak[credentials][<?php esc_attr_e($credential_key, 'urtak'); ?>]"
				id="urtak-credentials-<?php esc_attr_e($credential_key, 'urtak'); ?>-hidden"
				value="<?php esc_attr_e($credential_value, 'urtak'); ?>" />

		<?php } ?>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Credentials', 'urtak'); ?></h3>

			<div class="urtak-field">
				<p class="urtak-credentals-field">
					<?php printf(__('Logged in as <a href="https://urtak.com/account/edit" target="_blank">%1$s</a>', 'urtak'), self::get_credentials('email')); ?> <a id="urtak-field-logout" href="<?php esc_attr_e(self::_get_logout_url(), 'urtak'); ?>"><?php _e('Log out', 'urtak'); ?></a>
				</p>
				<p>
					<span class="urtak-api-key"><?php printf('API Key: %1$s', self::get_credentials('api-key')); ?></span>
				</p>
			</div>

		</div>

		<div class="urtak-individual-settings-section">

			<h3 id="urtak-site"><?php _e('This Site', 'urtak'); ?></h3>

			<?php
			function get_host_var($host) {
				return $host['host'];
			}
			?>

			<div class="urtak-field">
				<?php if(false === $publications) { ?>

				<p class="urtak-help urtal-help-nomargin"><?php _e('Selecting a site has been disabled because communication with the Urtak service has been interrupted.', 'urtak'); ?></p>

				<?php } else { ?>

				<p class="urtak-help urtak-help-nomargin"><label for="urtak-credentials-publication-key"><?php _e('Select this site from the list or create a new one.', 'urtak'); ?></label></p>
				<select name="urtak[credentials][publication-key]" id="urtak-credentials-publication-key">
					<?php foreach($publications as $publication) {
						$hosts_string = isset($publication['hosts']) && is_array($publication['hosts']) ? implode(', ', array_map('get_host_var', $publication['hosts'])) : ''; ?>
					<option data-domains="<?php esc_attr_e($hosts_string, 'urtak'); ?>" <?php selected($settings['credentials']['publication-key'], $publication['key']); ?> value="<?php esc_attr_e($publication['key'], 'urtak'); ?>"><?php esc_html_e($publication['name'], 'urtak'); ?></option>
					<?php } ?>
					<option <?php selected(true, empty($settings['credentials']['publication-key'])); ?> data-domains="<?php esc_attr_e(parse_url(home_url('/'), PHP_URL_HOST), 'urtak'); ?>" value="-1"><?php _e('Create a new site...', 'urtak'); ?></option>
				</select>

				<div id="urtak-new-site-dependencies">
					<p class="urtak-help urtak-help-nomargin"><label for="urtak-publication-name"><?php _e('This will help you organize your Urtak activity in your Account page.', 'urtak'); ?></label></p>
					<input type="text" class="text large-text" name="urtak[publication][name]" value="" placeholder="<?php _e('Site Name', 'urtak'); ?>" />
				</div>

				<p class="urtak-help urtak-help-nomargin"><label for="urtak-publication-domains"><?php _e('Please enter the domains on which you will be using Urtak. Urtak will only load on these domains.', 'urtak'); ?></label></p>
				<input type="text" class="text large-text code" name="urtak[publication][domains]" id="urtak-publication-domains" value="<?php ?>" />

				<p class="urtak-help urtak-help-nomargin" id="urtak-publication-key-display-container"><?php _e('Site Key: <span id="urtak-publication-key-display"></span>', 'urtak'); ?></p>

				<div id="urtak-create-site-button-container">
					<div class="alignright">
						<input class="button button-primary" type="submit" value="<?php _e('Create New Site', 'urtak'); ?>" />
					</div>
					<div class="clear"></div>
				</div>

				<?php } ?>
			</div>
			<input type="hidden" name="urtak[publication][publication-data]" value="<?php esc_attr_e(json_encode($publications), 'urtak'); ?>" />

		</div>

	<?php } ?>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('First Question', 'urtak'); ?></h3>

			<div class="urtak-field">
				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['has_first_question'], 'no'); ?> type="radio" name="urtak[has_first_question]" id="urtak-has_first_question-no" value="no" />
						</div><?php _e('No default first question', 'urtak'); ?>
					</label>
				</div>

				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['has_first_question'], 'yes'); ?> type="radio" name="urtak[has_first_question]" id="urtak-has_first_question-yes" value="yes" />
						</div><?php _e('Use a default first question', 'urtak'); ?>
					</label>
				</div>

				<div id="urtak-help-has_first_question-yes">
					<p class="urtak-help">
						<label for="urtak-default_first_question">
							<?php _e('Please enter the default question you would like to use:'); ?>
						</label>
					</p>
					<p class="urtak-help">
						<input type="text" class="text large-text" id="urtak-default_first_question" name="urtak[default_first_question]" value="<?php esc_attr_e($settings['default_first_question']); ?>" />
					</p>
				</div>

			</div>

		</div>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Moderation', 'urtak'); ?></h3>

			<div class="urtak-field">
				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['moderation'], 'community'); ?> type="radio" name="urtak[moderation]" id="urtak-moderation-community" value="community" />
						</div><?php _e('Community Moderation', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-moderation-community">
							<?php printf(__('No effort required. All questions will be approved by default. But don’t worry, our algorithm will take care of any bad questions automatically. <a href="%1$s" target="_blank">Read more here</a>.', 'urtak'), 'http://about.urtak.com/algorithm/'); ?>
						</label>
					</p>
				</div>

				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['moderation'], 'publisher'); ?> type="radio" name="urtak[moderation]" id="urtak-moderation-publisher" value="publisher" />
						</div><?php _e('Publisher Moderation', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-moderation-publisher">
							<?php _e('Full control. You’ll receive notifications when new questions are asked, and you’ll have to explicitly approve before anyone else can see or answer them.', 'urtak'); ?>
						</label>
					</p>
				</div>

			</div>

		</div>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Size', 'urtak'); ?></h3>

			<div class="urtak-field">
				<div class="urtak-field-half urtak-field-half-left">
					<label for="urtak-height"><?php _e('Height'); ?>&nbsp;&nbsp;</label>
					<input type="text" class="text large-text urtak-hw-text" name="urtak[height]" id="urtak-height" value="<?php esc_attr_e($settings['height']); ?>" /> px
				</div>
				<div class="urtak-field-half urtak-field-half-right">
					<label for="urtak-width"><?php _e('Width'); ?>&nbsp;&nbsp;</label>
					<input type="text" class="text large-text urtak-hw-text" name="urtak[width]" id="urtak-width" value="<?php esc_attr_e($settings['width']); ?>" /> px
				</div>

				<p class="urtak-help urtak-help-nomargin"><label for="urtak-height"><?php _e('Minimum height 180px. Minimum width 280px. Leave fields blank for auto height and width.'); ?></label></p>
			</div>

		</div>

	</div>

	<div class="urtak-settings-section urtak-settings-section-right">

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Placement', 'urtak'); ?></h3>

			<div class="urtak-field" style="padding-bottom: 5px;">
				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['user-start'], 'yes'); ?> type="radio" name="urtak[user-start]" id="urtak-user-start-yes" value="yes" />
						</div><?php _e('Create an Urtak with each new post', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-user-start-yes">
							<?php _e('Urtak will be added to all your posts.', 'urtak'); ?>
						</label>
					</p>
				</div>

				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['user-start'], 'no'); ?> type="radio" name="urtak[user-start]" id="urtak-user-start-no" value="no" />
						</div><?php _e('Create an Urtak only when I ask questions', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-user-start-no">
							<?php _e('Urtak will only appear when you have started the conversation off with questions of your own.', 'urtak'); ?>
						</label>
					</p>
				</div>
			</div>

			<div class="urtak-field" style="padding-bottom: 5px;">
				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['placement'], 'append'); ?> type="radio" name="urtak[placement]" id="urtak-placement-append" value="append" />
						</div><?php _e('End of post', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-placement-append">
							<?php _e('Urtak will load right at the bottom of your posts.', 'urtak'); ?>
						</label>
					</p>
				</div>

				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['placement'], 'manual'); ?> type="radio" name="urtak[placement]" id="urtak-placement-manual" value="manual" />
						</div><?php _e('Custom', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-placement-manual">
							<?php _e('Place the following code in the precise location in your template in which you wish Urtak to appear.', 'urtak'); ?>
						</label>
					</p>
					<p class="urtak-help">
						<input readonly="readonly" type="text" class="code text large-text" id="urtak-placement-manual-tag" value="<?php esc_attr_e("<?php do_action('make_urtak_widget'); ?>", 'urtak'); ?>" />
					</p>
				</div>
				<div class="clear"></div>
			</div>

			<div class="urtak-field">
				<div class="urtak-checkbox-container">
					<input type="hidden" name="urtak[homepage]" id="urtak-homepage-hidden" value="no" />
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['homepage'], 'yes'); ?> type="checkbox" name="urtak[homepage]" id="urtak-homepage" value="yes" />
						</div><?php _e('Include on Homepage', 'urtak'); ?>
					</label>
					<p class="urtak-help">
						<label for="urtak-homepage">
							<?php _e('Select this option if you wish Urtak to load on your front page. Otherwise, Urtak will only appear on your posts.', 'urtak'); ?>
						</label>
					</p>
				</div>
			</div>

		</div>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Post Types', 'urtak'); ?></h3>

			<?php $post_types = apply_filters('daylife-supported-post-types', get_post_types(array('show_ui' => true), 'objects')); ?>

			<div class="urtak-field">
				<input type="hidden" name="urtak[post-types][]" value="" />
				<?php foreach($post_types as $post_type) { ?>
				<div class="urtak-checkbox-container">
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked(true, in_array($post_type->name, $settings['post-types'])); ?> type="checkbox" name="urtak[post-types][]" id="urtak-post-types-<?php esc_attr_e($post_type->name); ?>" value="<?php esc_attr_e($post_type->name); ?>" />
						</div><?php esc_html_e($post_type->labels->name); ?>
					</label>
				</div>
				<?php } ?>
			</div>

		</div>

		<div class="urtak-individual-settings-section">

			<h3><?php _e('Response Counter', 'urtak'); ?></h3>

			<div class="urtak-field">
				<div class="urtak-checkbox-container">
					<input type="hidden" name="urtak[counter-icon]" id="urtak-counter-icon-hidden" value="no" />
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['counter-icon'], 'yes'); ?> type="checkbox" name="urtak[counter-icon]" id="urtak-counter-icon" value="yes" />
						</div><?php _e('Show Urtak Icon', 'urtak'); ?>
					</label>
				</div>

				<div class="urtak-checkbox-container">
					<input type="hidden" name="urtak[counter-responses]" id="urtak-counter-responses-hidden" value="no" />
					<label class="urtak-checkbox">
						<div class="urtak-checkbox-input-container">
							<input <?php checked($settings['counter-responses'], 'yes'); ?> type="checkbox" name="urtak[counter-responses]" id="urtak-counter-responses" value="yes" />
						</div><?php _e('Display the word "responses" after the counter', 'urtak'); ?>
					</label>
				</div>

				<p class="urtak-help">
					<input readonly="readonly" type="text" class="code text large-text" id="urtak-counter-tag" value="<?php esc_attr_e("<?php do_action('make_urtak_counter'); ?>", 'urtak'); ?>" />
				</p>
			</div>

		</div>

	</div>

	<div class="urtak-clear"></div>

	<p class="submit">
		<?php wp_nonce_field('save-urtak-settings', 'save-urtak-settings-nonce'); ?>
		<input type="submit" class="button button-primary" name="save-urtak-settings" value="<?php _e('Save Changes', 'urtak'); ?>" />
	</p>
</form>

<div class="urtak-clear urtak-settings" style="margin-top: 0;">
	<h3><?php _e('Newsletter', 'urtak'); ?></h3>

<form action="http://urtak.us1.list-manage.com/subscribe/post?u=be8b1f8143784f8105867393d&amp;id=9c0f338966" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank">
  <div class="urtak-field">
    <p class="urtak-help urtak-help-nomargin"><label for="mce-EMAIL">Subscribe to the Urtak Newsletter</label></p>
    <input type="email" value="" name="EMAIL" class="required email text large-text code" id="mce-EMAIL" placeholder="Your Email">
    <div>
      <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button button-primary">
    </div>
  </div>
  <!-- Hidden for WP Plugin: Add to Group Platform-WordPress -->
  <div class="mc-field-group input-group" style="display:none;">
    <input checked="checked" type="checkbox" value="1" name="group[2505][1]" id="mce-group[2505]-2505-0"><label for="mce-group[2505]-2505-0">WordPress</label>
  </div>
</form>
</div>

<?php } ?>
