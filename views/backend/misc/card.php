<?php if($use_nested_urtak) { $post_id = $question['urtak']['post_id']; } ?>

<?php $urtak_id = $use_nested_urtak ? $question['urtak']['id'] : array_pop(explode('/', $question['link'][1]['href'])); ?>

<div data-post-id="<?php esc_attr_e($post_id, 'urtak'); ?>" data-question-id="<?php esc_attr_e($question['id'], 'urtak'); ?>" class="urtak-card <?php if($controls) { echo 'urtak-card-with-controls'; } ?>">
	<div class="urtak-card-plot">
		<?php $yes_percent = empty($question['responses']['percents']['yes']) && empty($question['responses']['percents']['no']) ? 'ND' : intval($question['responses']['percents']['yes']); ?>
		<?php echo self::_get_pie_image($yes_percent); ?>

		<div class="urtak-card-stats">
			<div class="urtak-card-stats-item-yes">
				<div class="urtak-card-stats-item">Yes</div>
				<div class="urtak-card-stats-item urtak-card-stats-item-right"><?php echo intval($question['responses']['percents']['yes']); ?>%</div>
				<div class="urtak-clear"></div>
			</div>

			<div class="urtak-card-stats-item-no">
				<div class="urtak-card-stats-item">No</div>
				<div class="urtak-card-stats-item urtak-card-stats-item-right"><?php echo intval($question['responses']['percents']['no']); ?>%</div>
				<div class="urtak-clear"></div>
			</div>

			<div class="urtak-card-stats-item">Votes</div>
			<div class="urtak-card-stats-item urtak-card-stats-item-right"><?php echo intval($question['responses']['counts']['total']); ?></div>
			<div class="urtak-clear"></div>

			<div class="urtak-card-stats-item">Care</div>
			<div class="urtak-card-stats-item urtak-card-stats-item-right"><?php echo intval($question['responses']['percents']['care']); ?>%</div>
			<div class="urtak-clear"></div>
		</div>
	</div>
	<div class="urtak-card-info">
		<div class="urtak-card-info-question"><a href="https://urtak.com/u/<?php echo $urtak_id; ?>/questions" target="_blank"><?php esc_html_e($question['text'], 'urtak'); ?></a></div>
	</div>
	<div class="urtak-card-controls-container">
		<div class="urtak-card-info-asker"><?php printf(__('Asked by %1$s', 'urtak'), empty($question['ugc']) ? __('the site', 'urtak') : __('a user', 'urtak')); ?></div>
		<?php if($controls) { ?>
		<div class="urtak-card-controls">
			<a data-action="reject" class="urtak-card-controls-icon <?php if($question['status'] === 'rejected') { echo 'active'; } ?> urtak-card-controls-icon-rejected" href="#"></a>
			<a data-action="archive" class="urtak-card-controls-icon <?php if($question['status'] === 'archived') { echo 'active'; } ?> urtak-card-controls-icon-archived" href="#"></a>
			<a data-action="approve" class="urtak-card-controls-icon <?php if($question['status'] === 'approved') { echo 'active'; } ?> urtak-card-controls-icon-approved" href="#"></a>

			<?php if($question['status'] === 'pending') { ?>
			<a data-action="pending" class="urtak-card-controls-icon <?php if($question['status'] === 'pending') { echo 'active'; } ?> urtak-card-controls-icon-pending" href="#"></a>
			<?php } ?>

			<div class="clear"></div>
		</div>
		<?php } ?>
	</div>
</div>