<div class="urtak-questions-editor">
	<table class="fixed widefat">
		<thead>
			<tr valign="top">
				<th scope="col" class="urtak-question-title">
					<?php _e('Questions'); ?>
					-
					<a href="<?php esc_attr_e(esc_url($moderation_url)); ?>" target="_blank"><?php _e('Moderate'); ?></a>
					<small>(<a href="<?php esc_attr_e(esc_url($moderation_url)); ?>" target="_blank"><span data-bind="text: pending_questions_count"></span> <?php _e('Pending'); ?></a>)</small>
					-
					<small><a href="#" data-bind="click: add_new_question"><?php _e('Add New', 'urtak'); ?></a></small>

					<span class="urtak-save-warning" data-bind="visible: is_focused"><?php _e(' - Press enter to add multiple questions'); ?></span>

					<span class="urtak-save-warning" data-bind="visible: has_new_questions"><?php _e(' - Save post to submit new questions'); ?></span>
				</th>
				<th scope="col" class="urtak-question-responses">
					<?php _e('Responses'); ?>
					-
					<a href="<?php esc_attr_e(esc_url($results_url)); ?>" target="_blank"><?php _e('Explore Results'); ?></a>
					<small>(<a href="<?php esc_attr_e(esc_url($results_url)); ?>" target="_blank"><span data-bind="text: responses_count"></span> <?php _e('Total'); ?></a>)</small>
				</th>
			</tr>
		</thead>

		<tbody>
			<!-- ko foreach: questions -->
			<tr valign="top">
				<td class="urtak-question-title">
					<input type="text" class="large-text" data-bind="event: { keydown: $parent.check_for_add }, value: text, visible: !existing(), hasfocus: has_focus, valueUpdate: 'afterkeydown'" placeholder="<?php _e('Ask a YES or NO question', 'urtak'); ?>" />

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

	<div class="tablenav bottom">
		<div class="alignleft actions">
			<input type="hidden" name="urtak-force-hide-urtak" value="no" />
			<label class="">
				<input <?php checked($force_hide, 'yes'); ?> type="checkbox" name="urtak-force-hide-urtak" id="urtak-force-hide-urtak" value="yes" />
				<?php _e('Hide the Urtak poll on this post', 'urtak'); ?>
			</label>
		</div>

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

	<input type="hidden" id="urtak-serialized" name="urtak-serialized" value="<?php echo json_encode(array()); ?>" />
	<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>
</div>
