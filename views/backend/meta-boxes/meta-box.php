<div class="urtak-meta-box-actions" id="urtak-meta-box-search-params">
	<input type="hidden" data-urtak-attribute="action" value="urtak_get_questions" />
	<input type="hidden" data-urtak-attribute="post_id" value="<?php the_ID(); ?>" />
	<input type="hidden" id="urtak-meta-box-per-page" data-urtak-attribute="per_page" value="10" />

	<div class="alignright">
		<label for="urtak-meta-box-show"><?php _e('Show', 'urtak'); ?></label>
		<select id="urtak-meta-box-show" data-urtak-attribute="show">
			<option value="st|all"><?php _e('All', 'urtak'); ?></option>
			<option value="st|pe"><?php _e('Pending', 'urtak'); ?></option>
			<option selected="selected" value="st|ap"><?php _e('Approved', 'urtak'); ?></option>
			<option value="st|ar"><?php _e('Archived', 'urtak'); ?></option>
			<option value="st|nu"><?php _e('Rejected', 'urtak'); ?></option>
			<option value="mine"><?php _e('Mine', 'urtak'); ?></option>
		</select>&nbsp;&nbsp;
		<label for="urtak-meta-box-order"><?php _e('Order By', 'urtak'); ?></label>
		<select id="urtak-meta-box-order" data-urtak-attribute="order">
			<option value="time|DESC"><?php _e('Newest', 'urtak'); ?></option>
			<option value="time|ASC"><?php _e('Oldest', 'urtak'); ?></option>
			<option value="care|DESC"><?php _e('Most Cared About', 'urtak'); ?></option>
			<option value="care|ASC"><?php _e('Least Cared About', 'urtak'); ?></option>
			<option value="domi|ASC"><?php _e('Most Divided', 'urtak'); ?></option>
			<option value="domi|DESC"><?php _e('Most Agreed On', 'urtak'); ?></option>
			<option value="resp|DESC"><?php _e('Responses', 'urtak'); ?></option>
		</select>&nbsp;&nbsp;
		<label for="urtak-meta-box-search"><?php _e('Search', 'urtak'); ?></label>
		<input type="text" class="text" data-urtak-attribute="search" value="" />
	</div>

	<div class="clear"></div>

	<div id="urtak-meta-box-cards-top-shadow-left"></div>
	<div id="urtak-meta-box-cards-top-shadow" class="urtak-meta-box-cards-shadow"></div>
	<div id="urtak-meta-box-cards-top-shadow-right"></div>
</div>

<div id="urtak-meta-box-cards" class="loading">

	<div id="urtak-meta-box-cards-holder">

		<div class="urtak-card urtak-card-adder urtak-card-with-controls">
			<div class="urtak-card-plot urtak-card-plot-y">
				<?php echo self::_get_pie_image(100); ?>
			</div>
			<div class="urtak-card-plot urtak-card-plot-n">
				<?php echo self::_get_pie_image(0); ?>
			</div>
			<div class="urtak-card-plot urtak-card-plot-controls">
				<ul class="card-question-answers">
					<li class="card-question-answers-s" data-answer="s"><a tabindex="102" href="#"><?php _e('Submit', 'urtak'); ?></a></li>
					<li class="card-question-answers-d" data-answer="d"><a tabindex="103" href="#"><?php _e('Cancel', 'urtak'); ?></a></li>
				</ul>
			</div>
			<div class="urtak-card-info">
				<div class="urtak-card-info-question">
					<textarea tabindex="100" class="large-text" name="urtak[question][text][]" placeholder="<?php _e('Ask a Yes or No Question.', 'urtak'); ?>"></textarea>
					<input class="urtak-adder-answer" type="hidden" name="urtak[question][answer][]" value="" />
				</div>
			</div>
			<div class="clear"></div>
			<div class="urtak-card-controls-container">
				<div class="urtak-card-info-asker"><?php _e('Asked by the site', 'urtak'); ?></div>
				<div class="urtak-card-controls">
					<span class="urtak-update-message"><?php _e('Update or Publish this post to submit this question.', 'urtak'); ?></span>
					<a data-action="reject" class="urtak-card-controls-icon-special urtak-card-controls-icon <?php if($question['status'] === 'rejected') { echo 'active'; } ?> urtak-card-controls-icon-rejected" href="#"></a>
					<div class="clear"></div>
				</div>
			</div>
		</div>

	</div>

	<div id="urtak-meta-box-help">
		<div id="urtak-meta-box-help-content">
			<a href="#" id="urtak-meta-box-help-handle"><?php _e('Help', 'urtak'); ?></a>

			<div id="urtak-meta-box-help-content-inner">
				<h4><?php _e('How It Works', 'urtak'); ?></h4>

				<p><?php _e('Getting started with Urtak couldn’t be easier. We recommend kicking off the conversation with 3-5 questions, but the more you ask, the more people will answer! At any time, you can archive questions that are out of date, reject questions that you think are inappropriate or simply explore results from your urtak.com Dashboard. And when participation is winding down, Urtak will send you a detailed report of your community’s activity.', 'urtak'); ?></p>

				<h4><?php _e('Icon Guide', 'urtak'); ?></h4>

				<div id="urtak-meta-box-help-content-inner-icon-indicators">
					<div class="urtak-help-content-inner-icon">
						<a class="urtak-card-controls-icon active urtak-card-controls-icon-pending" href="#"></a>
						<span><?php _e('Pending', 'urtak'); ?></span>
					</div>

					<div class="urtak-help-content-inner-icon">
						<a class="urtak-card-controls-icon active urtak-card-controls-icon-approved" href="#"></a>
						<span><?php _e('Approve', 'urtak'); ?></span>
					</div>

					<div class="urtak-help-content-inner-icon">
						<a class="urtak-card-controls-icon active urtak-card-controls-icon-archived" href="#"></a>
						<span><?php _e('Archive', 'urtak'); ?></span>
					</div>

					<div class="urtak-help-content-inner-icon">
						<a class="urtak-card-controls-icon active urtak-card-controls-icon-rejected" href="#"></a>
						<span><?php _e('Reject', 'urtak'); ?></span>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="urtak-meta-box-actions" id="urtak-meta-box-controls">
	<div id="urtak-meta-box-cards-bottom-shadow-left"></div>
	<div id="urtak-meta-box-cards-bottom-shadow" class="urtak-meta-box-cards-shadow"></div>
	<div id="urtak-meta-box-cards-bottom-shadow-right"></div>

	<div id="urtak-meta-box-controls-per-page" class="alignleft">
		<?php _e('Show', 'urtak'); ?>
		<a class="urtak-meta-box-controls-per-page-link active" href="#">10</a>
		| <a class="urtak-meta-box-controls-per-page-link" href="#">50</a>
		| <a class="urtak-meta-box-controls-per-page-link" href="#">100</a> <?php _e('per page', 'urtak'); ?>
	</div>

	<div id="urtak-meta-box-controls-pager" class="alignright">
		<?php echo self::_get_pager(1, 1); ?>
	</div>

	<div id="urtak-meta-box-controls-alls">
		<input type="hidden" name="urtak-force-hide-urtak" value="no" />
		<label>
			<input <?php checked('yes', $force_hide); ?> type="checkbox" name="urtak-force-hide-urtak" value="yes" />
			<?php _e('Hide the Urtak for this post', 'urtak'); ?>
		</label>
	</div>

	<div class="clear"></div>
</div>

<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>


<style type="text/css">
#urtak-meta-box-cards.loading {
	background: #f8f8f8 url(<?php echo admin_url('images/wpspin_light.gif'); ?>) no-repeat center center;
}
</style>