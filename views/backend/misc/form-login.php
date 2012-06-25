<form class="urtak-settings urtak-login-signup-settings" method="post" 
		action="<?php esc_attr_e(self::_get_signup_url()); ?>" 
		<?php if(!$show) { ?>style="display: none;"<?php } ?> >

	<h2><?php _e('Account Login'); ?> 
		<small><a href="<?php esc_attr_e(self::_get_signup_url()); ?>"><?php _e('or Sign Up'); ?></a></small></h2>

	<?php $email = isset($data['urtak-login-email']) ? $data['urtak-login-email'] : get_the_author_meta('email');	?>

	<div class="urtak-field">
		<input type="text" class="large-text" name="urtak-login-email" value="<?php esc_attr_e(get_the_author_meta('email')); ?>" placeholder="<?php _e('Email'); ?>" />

		<input type="password" class="large-text" name="urtak-login-password" value="" placeholder="<?php _e('Password'); ?>" />
	</div>

	<p class="submit">
		<?php wp_nonce_field('urtak-signup', 'urtak-signup-nonce'); ?>
		<input class="button button-primary" type="submit" name="urtak-signup-submit" id="urtak-signup-submit" value="<?php _e('Login'); ?>" />
	</p>

</form>