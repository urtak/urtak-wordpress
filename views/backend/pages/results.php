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

					<!-- ko: foreach urtaks -->

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
					<tr valign="top">
						<td colspan="5"><?php _e('No urtaks found', 'urtak'); ?></td>
					</tr>

					<tr valign="top">
						<td colspan="5"><?php _e('Loading', 'urtak'); ?></td>
					</tr>

					<!-- ko: foreach questions -->

					<!-- /ko -->
				</tbody>
			</table>
		</div>
	</div>

	<div class="urtak-clear"></div>
</div>