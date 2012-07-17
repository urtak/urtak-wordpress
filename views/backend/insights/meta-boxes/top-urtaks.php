<?php if(false === $urtaks) { ?>
<div id="setting-error-settings_updated" class="settings-error error">
	<p><?php _e('Your publication\'s Urtaks could not be retrieved.', 'urtak'); ?></p>
</div>
<?php } else if(empty($urtaks)) { ?>
<div id="setting-error-settings_updated" class="settings-error error">
	<p><?php _e('You haven\'t created any Urtaks yet.', 'urtak'); ?></p>
</div>
<?php } else { ?>

<table class="widefat">
	<thead>
		<tr valign="top">
			<th scope="col"><?php _e('Top Urtaks', 'urtak'); ?></th>
			<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
			<th scope="col"><?php _e('Questions', 'urtak'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($urtaks as $urtak) { ?>
		<tr valign="top">
			<td><a target="_blank" href="<?php esc_attr_e(esc_url(get_permalink($urtak['post_id'])), 'urtak'); ?>"><?php esc_html_e($urtak['title'], 'urtak'); ?></a></td>
			<td>
				<?php esc_html_e(number_format_i18n($urtak['responses_count'], 0), 'urtak'); ?>
			</td>
			<td>
				<?php esc_html_e(number_format_i18n($urtak['approved_questions_count']), 0, 'urtak'); ?>

				<?php
				if($urtak['pending_questions_count'] > 0) {
					printf('&nbsp;<span class="urtak-pending-count">+%s</span>', number_format_i18n((float)$urtak['pending_questions_count']));
				}
				?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php } ?>