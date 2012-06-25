<div class="urtak-bar-graph">
	<?php foreach($dates as $date) { 
		$scaled = intval($date['responses'] / $maximum_responses * 200);
		$urtak_yes = $date['yes'] >= $date['no'] ? 'urtak-yes' : '';  ?>
	<div class="urtak-bar-graph-item <?php echo $urtak_yes; ?>">
		<div class="urtak-bar-graph-item-container">
			<div class="urtak-bar-graph-item-inner <?php echo $urtak_yes; ?>" style="<?php printf('height: %dpx;', $scaled); ?>">
				<div class="urtak-bar-graph-item-value"><strong><?php esc_html_e(number_format_i18n($date['responses'], 0)); ?></strong></div>
			</div>
			<div class="urtak-bar-graph-item-identifier"><?php esc_html_e($date['date']); ?></div>
		</div>
	</div>
	<?php } ?>
</div>
<div class="clear"></div>

<table class="widefat">
	<thead>
		<tr valign="top">
			<th scope="col" class="urtak-title-column"><?php _e('Top Urtaks'); ?></th>
			<th scope="col" class="urtak-responses-column"><?php _e('Responses'); ?></th>
			<th scope="col" class="urtak-questions-column"><?php _e('Questions'); ?></th>
			<th scope="col" class="urtak-users-column"><?php _e('Users'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($urtaks as $urtak) { ?>
		<tr valign="top">
			<td class="urtak-title-column"><?php esc_html_e($urtak->title); ?></td>
			<td class="urtak-responses-column"><?php esc_html_e($urtak->responses); ?></td>
			<td class="urtak-questions-column"><?php esc_html_e($urtak->questions); ?></td>
			<td class="urtak-users-column"><?php esc_html_e($urtak->users); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>