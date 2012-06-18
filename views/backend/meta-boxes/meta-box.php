<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="plugin-skeleton-some-meta"><?php _e('Some Meta'); ?></label></th>
			<td>
				<input type="text" class="regular-text" name="plugin-skeleton[some-meta]" id="plugin-skeleton-some-meta" value="<?php esc_attr_e($meta['some-meta']); ?>" />
			</td>
		</tr>
	</tbody>
</table>

<?php wp_nonce_field('save-plugin-skeleton-meta', 'save-plugin-skeleton-meta-nonce');
