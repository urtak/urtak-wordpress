<?php if(!self::has_credentials()) { ?>

<?php $action = (isset($data['action']) && 'login' === $data['action']) ? 'login' : 'signup'; ?>

<?php self::_print_login_form('login' === $action, $data); ?>

<?php self::_print_signup_form('signup' === $action, $data); ?>

<?php } ?>

<form class="urtak-settings" method="post" action="<?php esc_url(add_query_arg(array())); ?>">

<?php if(self::has_credentials()) { ?>

	<h2><?php _e('Account'); ?></h2>

	<?php foreach($settings['credentials'] as $credential_key => $credential_value) { ?>
	<input type="hidden" name="urtak[credentials][<?php esc_attr_e($credential_key); ?>]"
			id="urtak-credentials-<?php esc_attr_e($credential_key); ?>-hidden"
			value="<?php esc_attr_e($credential_value); ?>" />

	<?php } ?>

	<div class="urtak-field">
		<p class="urtak-credentals-field">
			<?php printf(__('Logged in as <a href="https://urtak.com/account/edit" target="_blank">%1$s</a>'), self::get_credentials('email')); ?> <a id="urtak-field-logout" href="<?php esc_attr_e(self::_get_logout_url()); ?>"><?php _e('Log out'); ?></a>
		</p>
		<p>
			<span class="urtak-api-key"><?php printf('API Key: %1$s', self::get_credentials('api-key')); ?></span>
		</p>
	</div>

	<h3 id="urtak-site"><?php _e('This Site'); ?></h3>

	<?php
	function get_host_var($host) {
		return $host['host'];
	}
	?>

	<div class="urtak-field">
		<?php if(false === $publications) { ?>

		<p class="urtak-help urtal-help-nomargin"><?php _e('Selecting a site has been disabled because communication with the Urtak service has been interrupted.'); ?></p>

		<?php } else { ?>

		<p class="urtak-help urtak-help-nomargin"><label for="urtak-credentials-publication-key"><?php _e('Select this site from the list or create a new one.'); ?></label></p>
		<select name="urtak[credentials][publication-key]" id="urtak-credentials-publication-key">
			<?php foreach($publications as $publication) {
				$hosts_string = isset($publication['hosts']) && is_array($publication['hosts']) ? implode(', ', array_map('get_host_var', $publication['hosts'])) : ''; ?>
			<option data-domains="<?php esc_attr_e($hosts_string); ?>" <?php selected($settings['credentials']['publication-key'], $publication['key']); ?> value="<?php esc_attr_e($publication['key']); ?>"><?php esc_html_e($publication['name']); ?></option>
			<?php } ?>
			<option <?php selected(true, empty($settings['credentials']['publication-key'])); ?> data-domains="<?php esc_attr_e(parse_url(home_url('/'), PHP_URL_HOST)); ?>" value="-1"><?php _e('Create a new site...'); ?></option>
		</select>

		<div id="urtak-new-site-dependencies">
			<p class="urtak-help urtak-help-nomargin"><label for="urtak-publication-name"><?php _e('What do you want to name this site?'); ?></label></p>
			<input type="text" class="text large-text" name="urtak[publication][name]" value="<?php _e(''); ?>" placeholder="<?php _e('Site Name'); ?>" />
		</div>

		<p class="urtak-help urtak-help-nomargin"><label for="urtak-publication-domains"><?php _e('Domains this Urtak Site will run on.'); ?></label></p>
		<input type="text" class="text large-text code" name="urtak[publication][domains]" id="urtak-publication-domains" value="<?php ?>" />

		<p class="urtak-help urtak-help-nomargin" id="urtak-publication-key-display-container"><?php _e('Site Key: <span id="urtak-publication-key-display"></span>'); ?></p>

		<div id="urtak-create-site-button-container">
			<div class="alignright">
				<input class="button button-primary" type="submit" value="<?php _e('Create New Site'); ?>" />
			</div>
			<div class="clear"></div>
		</div>

		<?php } ?>
	</div>
	<input type="hidden" name="urtak[publication][publication-data]" value="<?php esc_attr_e(json_encode($publications)); ?>" />

<?php } ?>

	<h2 id="urtak-settings-header"><?php _e('Settings'); ?></h2>

	<h3 id="urtak-settings-subheader"><?php _e('Placement'); ?></h3>

	<div class="urtak-field" style="padding-bottom: 5px;">
		<div class="urtak-checkbox-container">
			<label class="urtak-checkbox">
				<input <?php checked($settings['placement'], 'append'); ?> type="radio" name="urtak[placement]" id="urtak-placement-append" value="append" />
				<?php _e('I want Urtaks automatically appended to my posts'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-placement-append">
					<?php _e('Select this option if you want Urtaks to automatically be added to the end of all your posts.'); ?>
				</label>
			</p>
		</div>

		<div class="urtak-checkbox-container">
			<label class="urtak-checkbox">
				<input <?php checked($settings['placement'], 'manual'); ?> type="radio" name="urtak[placement]" id="urtak-placement-manual" value="manual" />
				<?php _e('I will insert Urtaks manually'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-placement-manual">
					<?php _e('Select this option if you want to place your Urtaks manually by using the template tag. Simply copy the following into your theme.'); ?>
				</label>
			</p>
			<p class="urtak-help">
				<input readonly="readonly" type="text" class="code text large-text" id="urtak-placement-manual-tag" value="<?php esc_attr_e("<?php do_action('make_urtak_widget'); ?>"); ?>" />
			</p>
		</div>
		<div class="clear"></div>
	</div>

	<div class="urtak-field">
		<div class="urtak-checkbox-container">
			<input type="hidden" name="urtak[homepage]" id="urtak-homepage-hidden" value="no" />
			<label class="urtak-checkbox">
				<input <?php checked($settings['homepage'], 'yes'); ?> type="checkbox" name="urtak[homepage]" id="urtak-homepage" value="yes" />
				<?php _e('Include on Homepage'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-homepage">
					<?php _e('If this is unchecked, Urtaks are only displayed on posts and pages.'); ?>
				</label>
			</p>
		</div>

		<div class="urtak-checkbox-container">
			<input type="hidden" name="urtak[user-start]" id="urtak-user-start-hidden" value="no" />
			<label class="urtak-checkbox">
				<input <?php checked($settings['user-start'], 'yes'); ?> type="checkbox" name="urtak[user-start]" id="urtak-user-start" value="yes" />
				<?php _e('Let Users Start Urtaks'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-user-start">
					<?php _e('Display Urtaks even if they don\'t have any questions and let users start the conversation.'); ?>
				</label>
			</p>
		</div>
	</div>

	<h3><?php _e('Moderation'); ?></h3>

	<div class="urtak-field">
		<div class="urtak-checkbox-container">
			<label class="urtak-checkbox">
				<input <?php checked($settings['moderation'], 'community'); ?> type="radio" name="urtak[moderation]" id="urtak-moderation-community" value="community" />
				<?php _e('Community Moderation'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-moderation-community">
					<?php _e('Some text goes here about what community moderation means, exactly.'); ?>
				</label>
			</p>
		</div>

		<div class="urtak-checkbox-container">
			<label class="urtak-checkbox">
				<input <?php checked($settings['moderation'], 'publisher'); ?> type="radio" name="urtak[moderation]" id="urtak-moderation-publisher" value="publisher" />
				<?php _e('Publisher Moderation'); ?>
			</label>
			<p class="urtak-help">
				<label for="urtak-moderation-publisher">
					<?php _e('Some text goes here about what publisher moderation means, exactly.'); ?>
				</label>
			</p>
		</div>
	</div>

	<h3><?php _e('Language'); ?></h3>

	<div class="urtak-field">
		<select name="urtak[language]" id="urtak-language">
			<option <?php selected($settings['language'], 'en'); ?> value="en"><?php _e('English'); ?></option>
			<option <?php selected($settings['language'], 'es'); ?> value="es"><?php _e('Spanish'); ?></option>
		</select>
	</div>

	<h3><?php _e('Disable Comments'); ?></h3>

	<div class="urtak-field">
		<input type="hidden" name="urtak[disable-comments]" id="urtak-disable-comments-hidden" value="no" />
		<label class="urtak-checkbox">
			<input <?php checked($settings['disable-comments'], 'yes'); ?> type="checkbox" name="urtak[disable-comments]" id="urtak-disable-comments" value="yes" />
			<?php _e('Disable all commenting features'); ?>
		</label>
		<p class="urtak-help">
			<label for="urtak-disable-comments">
				<?php _e('Prevent visitors to your site from commenting on your content by checking the box above.'); ?>
			</label>
		</p>
	</div>

	<p class="submit">
		<?php wp_nonce_field('save-urtak-settings', 'save-urtak-settings-nonce'); ?>
		<input type="submit" class="button button-primary" name="save-urtak-settings" value="<?php _e('Save Changes'); ?>" />
	</p>
</form>