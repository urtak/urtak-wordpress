<div class="urtak-moderation">
	<div class="urtak-moderation-left">
		<div class="urtak-moderation-left-inner">
			<h4><?php _e('Questions'); ?></h4>

			<div class="tablenav top" data-bind="visible: has_questions_pages">
				<div class="tablenav-pages">
					<span class="pagination-links">
						<a class="prev-page" data-bind="click: previous_questions_page, visible: has_previous_questions_page" title="Go to the previous page" href="#">‹</a>
						<span class="paging-input">
							<span class="current-page" data-bind="text: questions_page"></span>
							<?php _e('of'); ?>
							<span class="total-pages" data-bind="text: questions_pages"></span>
						</span>
						<a class="next-page" data-bind="click: next_questions_page, visible: has_next_questions_page" title="Go to the next page" href="#">›</a>
					</span>
				</div>
			</div>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Actions', 'urtak'); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Actions', 'urtak'); ?></th>
					</tr>
				</tfoot>

				<tbody>
					<tr data-bind="visible: no_questions" valign="top">
						<td colspan="3"><?php _e('No pending questions found', 'urtak'); ?></td>
					</tr>

					<tr data-bind="visible: questions_loading" valign="top">
						<td colspan="3"><?php _e('Loading...', 'urtak'); ?></td>
					</tr>

					<!-- ko foreach: questions -->
					<tr valign="top">
						<td data-bind="text: text"></td>
						<td data-bind="text: number_responses"></td>
						<td>
							<a data-bind="click: archive" href="#"><?php _e('Archive'); ?></a>
							|
							<a data-bind="click: reject" href="#"><?php _e('Reject'); ?></a>
						</td>
					</tr>
					<!-- /ko -->
				</tbody>
			</table>

			<h4><?php _e('Flagged Questions'); ?></h4>

			<div class="tablenav top" data-bind="visible: has_flags_pages">
				<div class="tablenav-pages">
					<span class="pagination-links">
						<a class="prev-page" data-bind="click: previous_flags_page, visible: has_previous_flags_page" title="Go to the previous page" href="#">‹</a>
						<span class="paging-input">
							<span class="current-page" data-bind="text: flags_page"></span>
							<?php _e('of'); ?>
							<span class="total-pages" data-bind="text: flags_pages"></span>
						</span>
						<a class="next-page" data-bind="click: next_flags_page, visible: has_next_flags_page" title="Go to the next page" href="#">›</a>
					</span>
				</div>
			</div>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Times Flagged', 'urtak'); ?></th>
						<th scope="col"><?php _e('Actions', 'urtak'); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Times Flagged', 'urtak'); ?></th>
						<th scope="col"><?php _e('Actions', 'urtak'); ?></th>
					</tr>
				</tfoot>

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
	</div>

	<div class="urtak-moderation-right">
		<div class="urtak-moderation-right-inner">
			<h4><?php _e('Urtaks'); ?></h4>

			<div class="tablenav top" data-bind="visible: has_urtaks_pages">
				<div class="tablenav-pages">
					<span class="pagination-links">
						<a class="prev-page" data-bind="click: previous_urtaks_page, visible: has_previous_urtaks_page" title="Go to the previous page" href="#">‹</a>
						<span class="paging-input">
							<span class="current-page" data-bind="text: urtaks_page"></span>
							<?php _e('of'); ?>
							<span class="total-pages" data-bind="text: urtaks_pages"></span>
						</span>
						<a class="next-page" data-bind="click: next_urtaks_page, visible: has_next_urtaks_page" title="Go to the next page" href="#">›</a>
					</span>
				</div>
			</div>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Post Title', 'urtak'); ?></th>
						<th scope="col"><?php _e('Questions', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr valign="top">
						<th scope="col"><?php _e('Post Title', 'urtak'); ?></th>
						<th scope="col"><?php _e('Questions', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
					</tr>
				</tfoot>

				<tbody>
					<tr data-bind="visible: no_urtaks" valign="top">
						<td colspan="3"><?php _e('No urtaks found', 'urtak'); ?></td>
					</tr>

					<tr data-bind="visible: urtaks_loading" valign="top">
						<td colspan="3"><?php _e('Loading...', 'urtak'); ?></td>
					</tr>

					<!-- ko foreach: urtaks -->
					<tr valign="top">
						<td>
							<strong><a data-bind="attr: { href: editlink }, text: edittitle" target="_blank"></a></strong>
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
									<a data-bind="click: function() { $parent.load_questions_for_urtak($data); }" href="#"><?php _e('Load Questions'); ?></a>
								</span>
							</div>
						</td>
						<td data-bind="text: approved_questions_count"></td>
						<td data-bind="text: responses_count"></td>
					</tr>
					<!-- /ko -->
				</tbody>
			</table>
		</div>
	</div>

	<div class="urtak-clear"></div>
</div>