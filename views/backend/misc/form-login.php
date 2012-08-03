<form class="urtak-settings urtak-login-signup-settings" method="post"
		action="<?php esc_attr_e(self::_get_login_url(), 'urtak'); ?>"
		<?php if(!$show) { ?>style="display: none;"<?php } ?> >

	<h2><?php _e('Account Login', 'urtak'); ?>
		<small><a href="<?php esc_attr_e(self::_get_signup_url(), 'urtak'); ?>"><?php _e('or Sign Up', 'urtak'); ?></a></small></h2>

	<?php $email = isset($data['urtak-login-email']) ? $data['urtak-login-email'] : get_the_author_meta('email', get_current_user_id()); ?>

	<div class="urtak-field">
		<input autocomplete="off" type="text" class="text large-text" name="urtak-login-email" value="<?php esc_attr_e($email, 'urtak'); ?>" placeholder="<?php _e('Email', 'urtak'); ?>" />

		<input autocomplete="off" type="password" class="text large-text" name="urtak-login-password" value="" placeholder="<?php _e('Password', 'urtak'); ?>" />

		<div class="alignright" style="margin-right: 10px;">
			<a href="https://urtak.com/users/password/new" target="_blank"><?php _e('Forgot your password?', 'urtak'); ?></a>
		</div>
		<div class="clear"></div>
	</div>

	<p class="submit">
		<?php wp_nonce_field('urtak-login', 'urtak-login-nonce'); ?>
		<input class="button button-primary" type="submit" name="urtak-login-submit" id="urtak-login-submit" value="<?php _e('Login', 'urtak'); ?>" />
	</p>

</form>