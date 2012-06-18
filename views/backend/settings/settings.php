<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Plugin Skeleton Settings'); ?></h2>
	
	<form method="post" action="<?php esc_url(add_query_arg(array())); ?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="plugin-skeleton-some-setting"><?php _e('Some Setting'); ?></label></th>
					<td>
						<input class="regular-text" type="text" name="plugin-skeleton[some-setting]" id="plugin-skeleton-some-setting" value="<?php esc_attr_e($settings['some-setting']); ?>" /><br />
						<small><?php _e('This is some example instruction text for the above field.'); ?></small>
					</td>
				</tr>
			</tbody>
		</table>
	
		<p class="submit">
			<?php wp_nonce_field('save-plugin-skeleton-settings', 'save-plugin-skeleton-settings-nonce'); ?>
			<input type="submit" class="button button-primary" name="save-plugin-skeleton-settings" value="<?php _e('Save Changes'); ?>" />
		</p>
	</form>
</div>