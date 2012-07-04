<?php if(false === $urtaks) { ?>
<div id="setting-error-settings_updated" class="settings-error error">
	<p><?php _e('Your publication\'s Urtaks could not be retrieved.'); ?></p>
</div>
<?php } else if(empty($urtaks)) { ?>
<div id="setting-error-settings_updated" class="settings-error error">
	<p><?php _e('You haven\'t created any Urtaks yet.'); ?></p>
</div>
<?php } else { ?>

<?php error_log(print_r($urtaks, true)); ?>

<table class="widefat">
	<thead>
		<tr valign="top">
			<th scope="col"><?php _e('Top Urtaks'); ?></th>
			<th scope="col"><?php _e('Responses'); ?></th>
			<th scope="col"><?php _e('Questions'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($urtaks as $urtak) { ?>
		<tr valign="top">
			<td><?php esc_html_e($urtak['title']); ?></td>
			<td>
				<?php esc_html_e(number_format_i18n($urtak['responses_count'], 0)); ?>
			</td>
			<td>
				<?php esc_html_e(number_format_i18n($urtak['approved_questions_count']), 0); ?>

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