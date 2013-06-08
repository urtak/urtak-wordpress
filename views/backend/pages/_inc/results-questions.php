<div class="urtak-results-questions">
	<div class="tablenav top">
		<div class="alignleft actions">
			<select data-bind="value: questions_filter">
				<option value="st|all" selected="selected"><?php _e('Status - All'); ?></option>
				<option value="st|aa"><?php _e('Approved'); ?></option>
				<option value="st|pe"><?php _e('Pending'); ?></option>
				<option value="st|ap"><?php _e('Approved or Pending'); ?></option>
				<option value="st|ar"><?php _e('Archived'); ?></option>
				<option value="st|nu"><?php _e('Rejected'); ?></option>
			</select>

			<select data-bind="value: questions_order">
				<option value="time" selected="selected"><?php _e('Order By - Time'); ?></option>
				<option value="n_responses"><?php _e('Number of Responses'); ?></option>
				<option value="most_cared"><?php _e('Most Cared'); ?></option>
				<option value="least_cared"><?php _e('Least Cared'); ?></option>
				<option value="most_agreed"><?php _e('Most Agreed'); ?></option>
				<option value="least_agreed"><?php _e('Least Agreed'); ?></option>
			</select>

			<label class="screen-reader-text"><?php _e('Search Questions'); ?></label>
			<input type="text" data-bind="value: questions_search_query" placeholder="<?php _e('Search Questions'); ?>" />

			<a data-bind="visible: post_id() > 0, attr: { href: '<?php echo admin_url('post.php?action=edit&post='); ?>' + post_id() }" target="_blank" href="#"><?php _e('Related Post'); ?></a>

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
				<th scope="col"><?php _e('Asked', 'urtak'); ?></th>
				<th scope="col"><?php _e('Status', 'urtak'); ?></th>
				<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
				<th scope="col"><?php _e('Results', 'urtak'); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr data-bind="visible: no_questions" valign="top">
				<td colspan="5"><?php _e('No questions found', 'urtak'); ?></td>
			</tr>

			<tr data-bind="visible: questions_loading" valign="top">
				<td colspan="5"><?php _e('Loading...', 'urtak'); ?></td>
			</tr>

			<!-- ko foreach: questions -->
			<tr valign="top">
				<td data-bind="text: text"></td>
				<td data-bind="text: nicedate"></td>
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
					</div>
				</td>
			</tr>
			<!-- /ko -->
		</tbody>
	</table>
</div>