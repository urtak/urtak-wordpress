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
	<h4><?php _e('Questions'); ?></h4>

	<div class="tablenav top">
		<div class="tablenav-pages">
			<span class="displaying-num"><span data-bind="text: total"></span> <?php _e('questions'); ?></span>
			<span class="pagination-links">
				<a class="prev-page" data-bind="click: previous_page, css: { disabled: !has_previous_page() }" title="<?php _e('Go to the previous page'); ?>" href="#"><?php _e('‹'); ?></a>
				<span class="paging-input">
					<span class="current-page" data-bind="text: page"></span>
					<?php _e('of'); ?>
					<span class="total-pages" data-bind="text: pages"></span>
				</span>
				<a class="next-page" data-bind="click: next_page, css: { disabled: !has_next_page() }" title="<?php _e('Go to the next page'); ?>" href="#"><?php _e('›'); ?></a>
			</span>
		</div>
	</div>

	<table class="fixed widefat">
		<thead>
			<tr valign="top">
				<th scope="col" class="urtak-question-title">
					<?php _e('Question'); ?>
					<small>(<a href="<?php esc_attr_e(esc_url($moderation_url)); ?>" target="_blank" data-bind="text: pending_questions_count"></a> <?php _e('Pending'); ?>)</small>
					-
					<small><a href="#" data-bind="click: add_new_question"><?php _e('Add New', 'urtak'); ?></a></small>
				</th>
				<th scope="col" class="urtak-question-responses">
					<?php _e('Responses'); ?>
					<small>(<a href="<?php esc_attr_e(esc_url($results_url)); ?>" target="_blank" data-bind="text: responses_count"></a> <?php _e('Total'); ?>)</small>
				</th>
			</tr>
		</thead>

		<tfoot>
			<tr valign="top">
				<th scope="col" class="urtak-question-title">
					<?php _e('Question'); ?>
					<small>(<a href="<?php esc_attr_e(esc_url($moderation_url)); ?>" target="_blank" data-bind="text: pending_questions_count"></a> <?php _e('Pending'); ?>)</small>
					-
					<small><a href="#" data-bind="click: add_new_question"><?php _e('Add New', 'urtak'); ?></a></small>
				</th>
				<th scope="col" class="urtak-question-responses">
					<?php _e('Responses'); ?>
					<small>(<a href="<?php esc_attr_e(esc_url($results_url)); ?>" target="_blank" data-bind="text: responses_count"></a> <?php _e('Total'); ?>)</small>
				</th>
			</tr>
		</tfoot>

		<tbody>
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

			<tr data-bind="visible: loading" valign="top">
				<td colspan="2"><?php _e('Loading...'); ?></td>
			</tr>

			<tr data-bind="visible: no_questions" valign="top">
				<td colspan="2"><?php _e('No questions found. <a href="#" data-bind="click: add_question">Add one now!</a>'); ?></td>
			</tr>
		</tbody>
	</table>

	<input type="hidden" id="urtak-serialized" name="urtak-serialized" value="<?php echo json_encode(array()); ?>" />
	<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>
</div>
