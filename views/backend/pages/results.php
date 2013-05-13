<div class="urtak-results">
	<div class="urtak-results-left">
		<div class="urtak-results-left-inner">
			<h3><?php _e('Urtaks'); ?></h3>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Post', 'urtak'); ?></th>
						<th scope="col"><?php _e('Approved Questions', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Participants', 'urtak'); ?></th>
						<th scope="col"><?php _e('Created', 'urtak'); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr valign="top">
						<th scope="col"><?php _e('Post', 'urtak'); ?></th>
						<th scope="col"><?php _e('Approved Questions', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Participants', 'urtak'); ?></th>
						<th scope="col"><?php _e('Created', 'urtak'); ?></th>
					</tr>
				</tfoot>

				<tbody>
					<tr valign="top" data-bind="visible: no_urtaks_found">
						<td colspan="5"><?php _e('No urtaks found', 'urtak'); ?></td>
					</tr>

					<tr valign="top" data-bind="visible: urtaks_loading">
						<td colspan="5"><?php _e('Loading', 'urtak'); ?></td>
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

								<span class="moderate">
									<a data-bind="attr: { href: moderatelink }" target="_blank"><?php _e('Moderate', 'urtak'); ?></a>
								</span>
							</div>
						</td>
						<td data-bind="text: approved_questions_count"></td>
						<td data-bind="text: responses_count"></td>
						<td data-bind="">###</td>
						<td data-bind="text: nicedate"></td>
					</tr>
					<!-- /ko -->
				</tbody>
			</table>
		</div>
	</div>

	<div class="urtak-results-right">
		<div class="urtak-results-right-inner">
			<h3><?php _e('Questions'); ?></h3>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Yes / No', 'urtak'); ?></th>
						<th scope="col"><?php _e('Date Asked', 'urtak'); ?></th>
						<th scope="col"><?php _e('Status', 'urtak'); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
						<th scope="col"><?php _e('Responses', 'urtak'); ?></th>
						<th scope="col"><?php _e('Yes / No', 'urtak'); ?></th>
						<th scope="col"><?php _e('Date Asked', 'urtak'); ?></th>
						<th scope="col"><?php _e('Status', 'urtak'); ?></th>
					</tr>
				</tfoot>

				<tbody>
					<tr valign="top" data-bind="visible: no_questions_found">
						<td colspan="5"><?php _e('No questions found', 'urtak'); ?></td>
					</tr>

					<tr valign="top" data-bind="visible: questions_loading">
						<td colspan="5"><?php _e('Loading', 'urtak'); ?></td>
					</tr>

					<!-- ko foreach: questions -->
					<tr valign="top">
						<td data-bind="text: text"></td>
						<td data-bind="text: response_count"></td>
						<td>BAR CHART</td>
						<td data-bind="text: nicedate"></td>
						<td data-bind="text: status"></td>
					</tr>
					<!-- /ko -->
				</tbody>
			</table>
		</div>
	</div>

	<div class="urtak-clear"></div>
</div>