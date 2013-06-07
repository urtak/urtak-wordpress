<div class="urtak-moderation-flags">
	<div class="tablenav top">
		<div class="tablenav-pages">
			<span class="displaying-num"><span data-bind="text: flags_total"></span> <?php _e('Flags'); ?></span>
			<span class="pagination-links" data-bind="visible: flags_pages() > 0">
				<a class="prev-page" data-bind="click: previous_flags_page, css: { disabled: !has_previous_flags_page() }" title="<?php _e('Go to the previous page'); ?>" href="#">‹</a>
				<span class="paging-input">
					<span class="current-page" data-bind="text: flags_page"></span>
					<?php _e('of'); ?>
					<span class="total-pages" data-bind="text: flags_pages"></span>
				</span>
				<a class="next-page" data-bind="click: next_flags_page, css: { disabled: !has_next_flags_page() }" title="<?php _e('Go to the next page'); ?>" href="#">›</a>
			</span>
		</div>
	</div>

	<table class="widefat fixed">
		<thead>
			<tr valign="top">
				<th scope="col"><?php _e('Flagged Question', 'urtak'); ?></th>
				<th scope="col"><?php _e('Times Flagged', 'urtak'); ?></th>
				<th scope="col"><?php _e('Actions', 'urtak'); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr data-bind="visible: no_flags" valign="top">
				<td colspan="3"><?php _e('No flagged questions found', 'urtak'); ?></td>
			</tr>

			<tr data-bind="visible: flags_loading" valign="top">
				<td colspan="3"><?php _e('Loading...', 'urtak'); ?></td>
			</tr>

			<!-- ko foreach: flags -->
			<tr valign="top">
				<td data-bind="text: question"></td>
				<td data-bind="text: count"></td>
				<td>
					<a data-bind="click: function() { $parent.change_flag_status($data, 'ignore'); }" href="#"><?php _e('Keep'); ?></a>
					|
					<a data-bind="click: function() { $parent.change_flag_status($data, 'agree'); }" href="#"><?php _e('Reject'); ?></a>
				</td>
			</tr>
			<!-- /ko -->
		</tbody>
	</table>
</div>