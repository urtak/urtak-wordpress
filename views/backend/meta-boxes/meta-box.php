<div class="urtak-meta-box-actions" id="urtak-meta-box-search-params">
	<input type="hidden" data-urtak-attribute="action" value="urtak_get_questions" />
	<input type="hidden" data-urtak-attribute="post_id" value="<?php the_ID(); ?>" />
	<input type="hidden" id="urtak-meta-box-per-page" data-urtak-attribute="per_page" value="10" />

	<div class="alignright">
		<label for="urtak-meta-box-show"><?php _e('Show'); ?></label>
		<select id="urtak-meta-box-show" data-urtak-attribute="show">
			<option value="all"><?php _e('All'); ?></option>
			<option value="approved"><?php _e('Approved'); ?></option>
			<option value="pending"><?php _e('Pending'); ?></option>
			<option value="archived"><?php _e('Archived'); ?></option>
			<option value="rejected"><?php _e('Rejected'); ?></option>
		</select>
		<label for="urtak-meta-box-order"><?php _e('Order By'); ?></label>
		<select id="urtak-meta-box-order" data-urtak-attribute="order">
			<option value="recent"><?php _e('Most Recent'); ?></option>
			<option value="responses"><?php _e('Responses'); ?></option>
		</select>
		<label for="urtak-meta-box-search"><?php _e('Search'); ?></label>
		<input type="text" class="text small-text" data-urtak-attribute="search" value="" />
	</div>

	<div class="clear"></div>
	<div id="urtak-meta-box-cards-top-shadow" class="urtak-meta-box-cards-shadow"></div>
</div>

<div id="urtak-meta-box-cards" class="loading">

	<div class="urtak-card urtak-card-adder urtak-card-with-controls">
		<div class="urtak-card-plot">
			<?php echo self::_get_pie_image('PENDING'); ?>
		</div>
		<div class="urtak-card-info">
			<div class="urtak-card-info-question">
				<input type="text" class="large-text" name="urtak[question][]" placeholder="<?php _e('Ask a Yes or No Question.'); ?>" />
			</div>
		</div>
		<div class="urtak-card-controls-container">
			<div class="urtak-card-info-asker"></div>
			<div class="urtak-card-controls">
				<div class="alignright">
					<input type="button" class="button button-secondary urtak-card-remove" value="<?php _e('Remove Question'); ?>" />
					<input type="button" class="button button-primary urtak-card-add" value="<?php _e('Add Question'); ?>" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>

</div>

<div class="urtak-meta-box-actions" id="urtak-meta-box-controls">
	<div id="urtak-meta-box-cards-bottom-shadow" class="urtak-meta-box-cards-shadow"></div>
	<div id="urtak-meta-box-controls-per-page" class="alignleft">
		<?php _e('Show'); ?> 
		<a class="urtak-meta-box-controls-per-page-link active" href="#">10</a> 
		| <a class="urtak-meta-box-controls-per-page-link" href="#">50</a> 
		| <a class="urtak-meta-box-controls-per-page-link" href="#">100</a> <?php _e('per page'); ?>
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