<?php if(empty($settings['credentials']['publication-key'])) { ?>

<div id="urtak-credential-notice" class="settings-error updated urtak-error urtak-moved-notice">
	<div class="globe"></div>
	<p>
		<strong><?php printf(__('Urtak is almost ready. <a href="%1$s#urtak-site">Select or create a site to get started</a>.', 'urtak'),
								self::_get_settings_url()); ?></strong>
	</p>
	<div class="clear"></div>
</div>

<?php } else { ?>

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
		</div>

		<?php do_meta_boxes('urtak', 'left', ''); ?>
	</div>
</div>
<div class"clear"></div>

<?php } ?>