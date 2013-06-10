<div class="urtak-results-questions">
	<h3 class="table-heading" data-bind="visible: post_id() > 0"><?php _e('Questions in '); ?><a href="#" target="_blank" data-bind="attr: { href: '<?php echo admin_url('post.php?action=edit&post='); ?>' + post_id() }, text: post_title"></a></h3>

	<h3 class="table-heading" data-bind="visible: post_id() == 0"><?php _e('All Questions'); ?></h3>

	<div class="tablenav top">
		<div class="alignleft actions">
			<label><?php _e('Status'); ?></label>
			<select data-bind="value: questions_filter">
				<option value="st|all"><?php _e('All'); ?></option>
				<option value="st|aa"><?php _e('Approved'); ?></option>
				<option value="st|pe"><?php _e('Pending'); ?></option>
				<option value="st|ap"><?php _e('Approved or Pending'); ?></option>
				<option value="st|ar"><?php _e('Archived'); ?></option>
				<option value="st|nu"><?php _e('Rejected'); ?></option>
			</select>

			<label><?php _e('Order By'); ?></label>
			<select data-bind="value: questions_order">
				<option value="time"><?php _e('Time'); ?></option>
				<option value="n_responses"><?php _e('Number of Responses'); ?></option>
				<option value="most_cared"><?php _e('Most Cared'); ?></option>
				<option value="least_cared"><?php _e('Least Cared'); ?></option>
				<option value="most_agreed"><?php _e('Most Agreed'); ?></option>
				<option value="least_agreed"><?php _e('Least Agreed'); ?></option>
			</select>

			<label class="screen-reader-text"><?php _e('Search Questions'); ?></label>
			<input type="text" data-bind="value: questions_search_query" placeholder="<?php _e('Search Questions'); ?>" />

			<input type="button" class="button action" data-bind="click: filter_and_fetch_questions" value="<?php _e('Filter'); ?>" />
			<input type="button" class="button action" data-bind="click: reset_and_fetch_questions" value="<?php _e('Reset'); ?>" />
		</div>

		<div class="tablenav-pages">
			<span class="displaying-num"><span data-bind="text: questions_total"></span> <?php _e('Questions'); ?></span>
			<span class="pagination-links" data-bind="visible: questions_pages() > 0">
				<a class="prev-page" data-bind="click: previous_questions_page, css: { disabled: !has_previous_questions_page() }" title="<?php _e('Go to the previous page'); ?>" href="#"><?php _e('‹'); ?></a>
				<span class="paging-input">
					<span class="current-page" data-bind="text: questions_page"></span>
					<?php _e('of'); ?>
					<span class="total-pages" data-bind="text: questions_pages"></span>
				</span>
				<a class="next-page" data-bind="click: next_questions_page, css: { disabled: !has_next_questions_page() }" title="<?php _e('Go to the next page'); ?>" href="#"><?php _e('›'); ?></a>
			</span>
		</div>
	</div>

	<table class="widefat fixed">
		<thead>
			<tr valign="top">
				<th scope="col"><?php _e('Question', 'urtak'); ?></th>
				<th class="urtak-responses-column" scope="col"><?php _e('Responses', 'urtak'); ?></th>
				<th class="urtak-responses-column" scope="col"><?php _e('Status', 'urtak'); ?></th>
				<th class="urtak-mini-graph-container" scope="col"><?php _e('Results', 'urtak'); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr data-bind="visible: no_questions" valign="top">
				<td colspan="4"><?php _e('No questions found', 'urtak'); ?></td>
			</tr>

			<tr data-bind="visible: questions_loading" valign="top">
				<td colspan="4"><?php _e('Loading...', 'urtak'); ?></td>
			</tr>

			<!-- ko foreach: questions -->
			<tr valign="top">
				<td>
					<strong data-bind="text: text"></strong>
					<small class="urtak-date"> - <span data-bind="text: nicedate"></span></small>
				</td>
				<td data-bind="text: nicestatus"></td>
				<td data-bind="text: number_responses"></td>
				<td class="urtak-mini-graph-container">
					<div class="urtak-mini-graph">
						<div class="urtak-mini-graph-bar">
							<div class="urtak-mini-graph-bar-inner yes" data-bind="style: { width: yes_percent() + 'px' }"></div>
							<span class="urtak-mini-graph-bar-label" data-bind="text: yes_percent"></span>% <?php _e('Yes'); ?>
						</div>
						<div class="urtak-mini-graph-bar">
							<div class="urtak-mini-graph-bar-inner no" data-bind="style: { width: no_percent() + 'px' }"></div>
							<span class="urtak-mini-graph-bar-label" data-bind="text: no_percent"></span>% <?php _e('No'); ?>
						</div>
						<div class="urtak-mini-graph-bar">
							<div class="urtak-mini-graph-bar-inner care" data-bind="style: { width: care_percent() + 'px' }"></div>
							<span class="urtak-mini-graph-bar-label" data-bind="text: care_percent"></span>% <?php _e('Care'); ?>
						</div>
					</div>
				</td>
			</tr>
			<!-- /ko -->
		</tbody>
	</table>
</div>
