<div id="urtak-main-widget" class="metabox-holder columns-1">
	<div class="postbox-container">
		<?php do_meta_boxes('urtak', 'top', ''); ?>
	</div>
</div>
<div class="clear"></div>

<div id="urtak-other-widgets" class="metabox-holder columns-2">
	<div class="postbox-container">
		<?php do_meta_boxes('urtak', 'right', ''); ?>
	</div>
	<div class="postbox-container">
		<div class="meta-box-sortables">
			<?php self::display_meta_box__top_urtaks() ?>

			<?php self::display_meta_box__stats(); ?>
		</div>

		<?php do_meta_boxes('urtak', 'left', ''); ?>
	</div>
</div>
<div class"clear"></div>