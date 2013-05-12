<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row"><label for="urtak-force-hide-urtak"><?php _e('Hide Urtak'); ?></label></th>
			<td>
				<input type="hidden" name="urtak-force-hide-urtak" value="no" />

				<label>
					<input <?php checked($force_hide, 'yes'); ?> type="checkbox" name="urtak-force-hide-urtak" id="urtak-force-hide-urtak" value="yes" />
					<?php _e('The Urtak for this content should not show', 'urtak'); ?>
				</label>
			</td>
		</tr>
	</tbody>
</table>

<div class="urtak-questions-editor">
	<table class="fixed widefat">
		<thead>
			<tr valign="top">
				<th scope="col" class="urtak-question-title">
					<?php _e('Question'); ?> -
					<small><a href="#" data-bind="click: add_new_question"><?php _e('Add New', 'urtak'); ?></a></small>
				</th>
				<th scope="col" class="urtak-question-responses"><?php _e('Responses'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr data-bind="visible: loading" valign="top">
				<td colspan="2">Loading</td>
			</tr>

			<tr data-bind="visible: no_questions" valign="top">
				<td colspan="2"><?php _e('No questions found. <a href="#" data-bind="click: add_question">Add one now!</a>'); ?></td>
			</tr>

			<!-- ko foreach: questions -->
			<tr valign="top">
				<td class="urtak-question-title">
					<input type="text" class="large-text" data-bind="value: text, visible: !existing()" />

					<strong data-bind="text: text, visible: existing"></strong>

					<div class="row-actions">
						<span data-bind="visible: first_question">
							<a href="#" data-bind="click: $parent.unset_first"><?php _e('Unset as First'); ?></a>
							|
						</span>

						<span data-bind="visible: not_first_question">
							<a href="#" data-bind="click: $parent.set_first"><?php _e('Set as First'); ?></a>
							|
						</span>

						<span class="trash">
							<a href="#" data-bind="click: $parent.remove_question"><?php _e('Remove'); ?></a>
						</span>
					</div>
				</td>
				<td class="urtak-question-responses">
					<span data-bind="text: number_responses"></span>
				</td>
			</tr>
			<!-- /ko -->

		</tbody>
	</table>

	<input type="hidden" id="urtak-serialized" name="urtak-serialized" value="<?php echo json_encode(array()); ?>" />
	<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>
</div>
