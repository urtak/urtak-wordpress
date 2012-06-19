<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Urtak Settings'); ?></h2>

	<form method="post" action="<?php esc_url(add_query_arg(array())); ?>">
		<p>Put settings fields here.</p>

		<p class="submit">
			<?php wp_nonce_field('save-urtak-settings', 'save-urtak-settings-nonce'); ?>
			<input type="submit" class="button button-primary" name="save-urtak-settings" value="<?php _e('Save Changes'); ?>" />
		</p>
	</form>
</div>