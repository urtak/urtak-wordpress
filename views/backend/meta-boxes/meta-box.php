<div class="urtak-meta-box-actions" id="urtak-meta-box-search-params">
	<input type="hidden" data-urtak-attribute="action" value="urtak_get_questions" />
	<input type="hidden" data-urtak-attribute="post_id" value="<?php the_ID(); ?>" />
	<input type="hidden" id="urtak-meta-box-per-page" data-urtak-attribute="per_page" value="10" />

	<div class="alignleft">
		<label><?php _e('Show'); ?></label>
		<select data-urtak-attribute="show">
			<option value="all"><?php _e('All'); ?></option>
			<option value="pending"><?php _e('Pending'); ?></option>
			<option value="archived"><?php _e('Archived'); ?></option>
			<option value="rejected"><?php _e('Rejected'); ?></option>
		</select>
	</div>

	<div class="alignright">
		<label><?php _e('Order By'); ?></label>
		<select data-urtak-attribute="order">
			<option value="recent"><?php _e('Most Recent'); ?></option>
			<option value="responses"><?php _e('Responses'); ?></option>
		</select>
	</div>

	<div class="clear"></div>
	<div id="urtak-meta-box-cards-top-shadow" class="urtak-meta-box-cards-shadow"></div>
</div>
<div id="urtak-meta-box-cards" class="loading">
</div>
<div class="urtak-meta-box-actions" id="urtak-meta-box-controls">
	<div id="urtak-meta-box-cards-bottom-shadow" class="urtak-meta-box-cards-shadow"></div>
	<div id="urtak-meta-box-controls-per-page" class="alignleft">
		<?php _e('Show'); ?> <a class="urtak-meta-box-controls-per-page-link active" href="#">10</a> | <a class="urtak-meta-box-controls-per-page-link" href="#">50</a> | <a class="urtak-meta-box-controls-per-page-link" href="#">100</a> <?php _e('per page'); ?>
	</div>

	<div id="urtak-meta-box-controls-pager" class="alignright">
		<?php echo self::_get_pager(1, 1); ?>
	</div>
	
	<div id="urtak-meta-box-controls-alls">
		<a data-action="approve" href="#"><?php _e('Approve'); ?></a><a data-action="reject" href="#"><?php _e('Reject'); ?></a><span><?php _e('all on this page'); ?></span>
	</div>

	<div class="clear"></div>
</div>

<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>


<style type="text/css">
#urtak-meta-box-cards.loading {
	background: #f8f8f8 url(<?php echo admin_url('images/wpspin_light.gif'); ?>) no-repeat center center;	
}
</style>