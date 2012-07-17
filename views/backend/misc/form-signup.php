<form class="urtak-settings urtak-login-signup-settings" method="post"
		action="<?php esc_attr_e(self::_get_signup_url(), 'urtak'); ?>"
		<?php if(!$show) { ?>style="display: none;"<?php } ?> >

	<h2><?php _e('Account Sign Up', 'urtak'); ?>
		<small><a href="<?php esc_attr_e(self::_get_login_url(), 'urtak'); ?>"><?php _e('or Login', 'urtak'); ?></a></small></h2>

	<?php $email = isset($data['urtak-signup-email']) ? $data['urtak-signup-email'] : get_the_author_meta('email');	?>

	<div class="urtak-field">
		<input autocomplete="off" type="text" class="large-text" name="urtak-signup-email" value="<?php esc_attr_e($email, 'urtak'); ?>" placeholder="<?php _e('Email', 'urtak'); ?>" />
	</div>

	<p class="submit">
		<?php wp_nonce_field('urtak-signup', 'urtak-signup-nonce'); ?>
		<input class="button button-primary" type="submit" name="urtak-signup-submit" id="urtak-signup-submit" value="<?php _e('Sign Up', 'urtak'); ?>" />
	</p>
</form>