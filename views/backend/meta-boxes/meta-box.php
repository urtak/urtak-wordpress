<div class="urtak-meta-box-actions" id="urtak-meta-box-search-params">
	<label><?php _e('Show'); ?></label>
	<select><option><?php _e('All'); ?></option></select>

	<label><?php _e('Order By'); ?></label>
	<select><option><?php _e('Most Recent'); ?></option></select>
</div>
<div id="urtak-meta-box-cards">
	<?php for($i = 0; $i < 5; $i++) { ?>
		<?php echo self::_get_card(array('question' => ''), true); ?>
	<?php } ?>
</div>
<div class="urtak-meta-box-actions" id="urtak-meta-box-controls">
	<div id="urtak-meta-box-controls-per-page">
		<?php _e('Show'); ?> <a href="#">10</a> | <a href="#">50</a> | <a href="#">100</a> <?php _e('per page'); ?>
	</div>
	<div id="urtak-meta-box-controls-alls">
		<a href="#"><?php _e('Approve'); ?></a><a href="#"><?php _e('Reject'); ?></a><span><?php _e('all on this page'); ?></span>
	</div>
	<div id="urtak-meta-box-controls-pager">
		<a href="#">Previous</a>
		<input type="text" size="2" />
		/ 134
		<a href="#">Next</a>
	</div>

</div>

<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>
