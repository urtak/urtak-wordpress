<?php if(self::has_credentials()) { ?>
<form class="urtak-settings" method="post" action="<?php esc_url(add_query_arg(array())); ?>">

	<h2><?php _e('Account'); ?></h2>

	<input type="hidden" name="urtak[credentials][email]" 
			id="urtak-credentials-email" 
			value="<?php esc_attr_e($settings['credentials']['email']); ?>" />
	<input type="hidden" name="urtak[credentials][api-key]" 
			id="urtak-credentials-email" 
			value="<?php esc_attr_e($settings['credentials']['email']); ?>" />

	<div class="urtak-field">
		<p>
			<?php printf('Logged in as %1$s', self::get_credentials('email')); ?><br />
			<span class="urtak-api-key"><?php printf('API Key: %1$s', self::get_credentials('api-key')); ?></span>
		</p>
	</div>

	<h3><?php _e('This Site'); ?></h3>

	<?php } else { $action = (isset($_GET['action']) && 'login' === $_GET['action']) ? 'login' : 'signup'; ?>

<form class="urtak-settings" method="post" 
		action="<?php esc_url(add_query_arg(array())); ?>"
		<?php if('login' === $action) { echo 'style="display: none;"'; } ?> 
		id="urtak-signup-section">

	<h2><?php _e('Account Sign Up'); ?> <small><a href="<?php esc_attr_e(self::_get_login_url()); ?>"><?php _e('or Login'); ?></a></small></h2>

	<div class="urtak-field">
		<input type="text" class="large-text" name="urtak-signup-email" value="<?php esc_attr_e(get_option(get_the_author_meta('email'))); ?>" placeholder="<?php _e('Email'); ?>" />
	
		<p class="submit">
			<?php wp_nonce_field('urtak-signup', 'urtak-signup-nonce'); ?>
			<input class="button button-primary" type="submit" name="urtak-signup-submit" id="urtak-signup-submit" value="<?php _e('Sign Up'); ?>" />
		</p>
	</div>

</form>

<form class="urtak-settings" method="post" 
		action="<?php esc_url(add_query_arg(array())); ?>" 
		<?php if('signup' === $action) { echo 'style="display: none;"'; } ?>
		id="urtak-login-section">

	<h2><?php _e('Account Login'); ?> <small><a href="<?php esc_attr_e(self::_get_signup_url()); ?>"><?php _e('or Sign Up'); ?></a></small></h2>

	<p class="submit">
		<?php wp_nonce_field('urtak-signup', 'urtak-signup-nonce'); ?>
		<input class="button button-primary" type="submit" name="urtak-signup-submit" id="urtak-signup-submit" value="<?php _e('Sign Up'); ?>" />
	</p>

</form>

<form class="urtak-settings" method="post" action="<?php esc_url(add_query_arg(array())); ?>">

	<?php } ?>

	<h2><?php _e('Settings'); ?></h2>

	<h3><?php _e('Placement'); ?></h3>

	<div class="urtak-field">
		<label class="urtak-checkbox">
			<input <?php checked($settings['placement'], 'append'); ?> type="radio" name="urtak[placement]" id="urtak-placement-append" value="append" />
			<?php _e('I want Urtaks automatically appended to my posts'); ?>
		</label>
		<p class="urtak-help">
			<label for="urtak-placement-append">
				<?php _e('Select this option if you want Urtaks to automatically be added to the end of all your posts.'); ?>
			</label>
		</p>

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
			<input readonly="readonly" type="text" class="code large-text" id="urtak-placement-manual-tag" value="<?php esc_attr_e("<?php do_action('make_urtak_widget'); ?>"); ?>" />
		</p>
	</div>

	<div class="urtak-field">
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

	<h3><?php _e('Moderation'); ?></h3>
	
	<div class="urtak-field">
		<label class="urtak-checkbox">
			<input <?php checked($settings['moderation'], 'community'); ?> type="radio" name="urtak[moderation]" id="urtak-moderation-community" value="community" />
			<?php _e('Community Moderation'); ?>
		</label>
		<p class="urtak-help">
			<label for="urtak-moderation-community">
				<?php _e('Some text goes here about what community moderation means, exactly.'); ?>
			</label>
		</p>

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