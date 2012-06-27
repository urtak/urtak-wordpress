<?php $no_percent = rand(0,100); ?>

<div class="urtak-card">
	<div class="urtak-card-plot">
		<?php echo self::_get_pie_image($no_percent); ?>
	</div>
	<div class="urtak-card-info">
		<div class="urtak-card-info-question"><?php _e('Do you think an increase in cab fare would be offset by an increase in subway fare?'); ?></div>
		<div class="urtak-card-info-asker"><?php printf(__('Asked by %1$s'), 'smombartz'); ?></div>
	</div>
</div>