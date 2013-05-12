<h4><?php _e('Content Controls'); ?></h4>

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
	<div data-bind="visible: has_urtak">
		<h4><?php _e('Quick Stats'); ?></h4>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e('Pending Questions', 'urtak'); ?></th>
					<td>
						<a href="<?php esc_attr_e(esc_url($moderation_url)); ?>" target="_blank" data-bind="text: pending_questions_count"></a>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e('Responses', 'urtak'); ?></th>
					<td>
						<a href="<?php esc_attr_e(esc_url($results_url)); ?>" target="_blank" data-bind="text: responses_count"></a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>


	<h4><?php _e('Questions'); ?></h4>

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
					<input type="text" class="large-text" data-bind="value: text, visible: !existing()" placeholder="<?php _e('Ask a question', 'urtak'); ?>" />

					<strong data-bind="text: text, visible: existing"></strong>

					<div class="row-actions">
						<span data-bind="visible: first_question">
							<a href="#" data-bind="click: $parent.unset_first"><?php _e('Unset as First Question'); ?></a>
							|
						</span>

						<span data-bind="visible: not_first_question">
							<a href="#" data-bind="click: $parent.set_first"><?php _e('Set as First Question'); ?></a>
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
