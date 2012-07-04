
<table class="widefat">
	<thead>
		<tr valign="top">
			<th scope="col" class="urtak-title-column"><?php _e('Top Urtaks'); ?></th>
			<th scope="col" class="urtak-responses-column"><?php _e('Responses'); ?></th>
			<th scope="col" class="urtak-questions-column"><?php _e('Questions'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($urtaks as $urtak) { ?>
		<tr valign="top">
			<td class="urtak-title-column"><?php esc_html_e($urtak->title); ?></td>
			<td class="urtak-responses-column"><?php esc_html_e($urtak->responses); ?></td>
			<td class="urtak-questions-column"><?php esc_html_e($urtak->questions); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>