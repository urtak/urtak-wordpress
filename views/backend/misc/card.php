<?php $yes_percent = rand(0,100); $post_id = rand(1000, 5000); $question_id = rand(5000, 15000); ?>


<div data-post-id="<?php esc_attr_e($post_id); ?>" data-question-id="<?php esc_attr_e($question_id); ?>" class="urtak-card <?php if($controls) { echo 'urtak-card-with-controls'; } ?>">
	<div class="urtak-card-plot">
		<?php echo self::_get_pie_image($yes_percent); ?>
	</div>
	<div class="urtak-card-info">
		<div class="urtak-card-info-question"><?php _e('Do you think an increase in cab fare would be offset by an increase in subway fare?'); ?></div>
	</div>
	<div class="urtak-card-controls-container">
		<div class="urtak-card-info-asker"><?php printf(__('Asked by %1$s'), 'smombartz'); ?></div>
		<?php if($controls) { ?>
		<div class="urtak-card-controls">
			<a data-action="reject" class="urtak-card-controls-icon urtak-card-controls-icon-reject" href="#"></a>
			<a data-action="pending" class="urtak-card-controls-icon urtak-card-controls-icon-pending" href="#"></a>
			<a data-action="archive" class="urtak-card-controls-icon urtak-card-controls-icon-archive" href="#"></a>
			<a data-action="approve" class="urtak-card-controls-icon urtak-card-controls-icon-approved" href="#"></a>

			<div class="clear"></div>
		</div>
		<?php } ?>
	</div>
</div>