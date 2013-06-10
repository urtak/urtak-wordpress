<div class="urtak-results-urtaks">
	<h3 class="table-heading"><?php _e('Posts with Urtak Polls'); ?></h3>

	<div class="tablenav top">
		<div class="alignleft actions">
			<label><?php _e('Order By'); ?></label>
			<select data-bind="value: urtaks_order">
				<option value="n_responses|DESC" selected="selected"><?php _e('Most Responses'); ?></option>
				<option value="n_responses|ASC"><?php _e('Least Responses'); ?></option>
			</select>

			<label class="screen-reader-text"><?php _e('Search Urtaks'); ?></label>
			<input type="button" class="button action" data-bind="click: filter_and_fetch_urtaks" value="<?php _e('Filter'); ?>" />
			<input type="button" class="button action" data-bind="click: reset_and_fetch_urtaks" value="<?php _e('Reset'); ?>" />
		</div>

		<div class="tablenav-pages">
			<span class="displaying-num"><span data-bind="text: urtaks_total"></span> <?php _e('Urtaks'); ?></span>
			<span class="pagination-links" data-bind="visible: urtaks_pages() > 0">
				<a class="prev-page" data-bind="click: previous_urtaks_page, css: { disabled: !has_previous_urtaks_page() }" title="<?php _e('Go to the previous page'); ?>" href="#"><?php _e('‹'); ?></a>
				<span class="paging-input">
					<span class="current-page" data-bind="text: urtaks_page"></span>
					<?php _e('of'); ?>
					<span class="total-pages" data-bind="text: urtaks_pages"></span>
				</span>
				<a class="next-page" data-bind="click: next_urtaks_page, css: { disabled: !has_next_urtaks_page() }" title="<?php _e('Go to the next page'); ?>" href="#"><?php _e('›'); ?></a>
			</span>
		</div>
	</div>

	<table class="widefat fixed">
		<thead>
			<tr valign="top">
				<th scope="col"><?php _e('Post Title', 'urtak'); ?></th>
				<th class="urtak-responses-column" scope="col"><?php _e('Questions', 'urtak'); ?></th>
				<th class="urtak-pending-questions-column" scope="col"><?php _e('Pending Questions', 'urtak'); ?></th>
				<th class="urtak-responses-column" scope="col"><?php _e('Responses', 'urtak'); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr data-bind="visible: no_urtaks" valign="top">
				<td colspan="4"><?php _e('No Urtaks found', 'urtak'); ?></td>
			</tr>

			<tr data-bind="visible: urtaks_loading" valign="top">
				<td colspan="4"><?php _e('Loading...', 'urtak'); ?></td>
			</tr>

			<!-- ko foreach: urtaks -->
			<tr valign="top">
				<td>
					<strong><a data-bind="attr: { href: editlink }, text: edittitle" target="_blank"></a></strong>
					<small class="urtak-date"> - <span data-bind="text: nicedate"></span></small>
					<div class="row-actions">
						<span class="edit">
							<a data-bind="attr: { href: editlink }" target="_blank"><?php _e('Edit'); ?></a>
							|
						</span>

						<span class="view">
							<a data-bind="attr: { href: viewlink }" target="_blank"><?php _e('View', 'urtak'); ?></a>
							|
						</span>

						<span class="load">
							<a data-bind="click: function() { $parent.load_questions_for_urtak($data); }" href="#"><?php _e('Show Questions'); ?></a>
							|
						</span>

						<span class="load">
							<a data-bind="attr: { href: moderatelink }" href="#"><?php _e('Moderate'); ?></a>
						</span>
					</div>
				</td>
				<td class="urtak-responses-column" data-bind="text: approved_questions_count"></td>
				<td class="urtak-pending-questions-column" data-bind="text: pending_questions_count"></td>
				<td class="urtak-responses-column" data-bind="text: responses_count"></td>
			</tr>
			<!-- /ko -->
		</tbody>
	</table>
</div>