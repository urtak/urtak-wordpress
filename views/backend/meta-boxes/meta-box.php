<div class="urtak-meta-box-actions" id="urtak-meta-box-search-params">
	<input type="hidden" data-urtak-attribute="action" value="urtak_get_questions" />
	<input type="hidden" data-urtak-attribute="post_id" value="<?php the_ID(); ?>" />
	<input type="hidden" id="urtak-meta-box-per-page" data-urtak-attribute="per_page" value="10" />

	<div class="alignright">
		<label for="urtak-meta-box-show"><?php _e('Show'); ?></label>
		<select id="urtak-meta-box-show" data-urtak-attribute="show">
			<option value="st|all"><?php _e('All'); ?></option>
			<option value="st|pe"><?php _e('Pending'); ?></option>
			<option value="st|ap"><?php _e('Approved'); ?></option>
			<option value="st|ar"><?php _e('Archived'); ?></option>
			<option value="st|nu"><?php _e('Rejected'); ?></option>
			<option value="mine"><?php _e('Mine'); ?></option>
		</select>&nbsp;&nbsp;
		<label for="urtak-meta-box-order"><?php _e('Order By'); ?></label>
		<select id="urtak-meta-box-order" data-urtak-attribute="order">
			<option value="time|DESC"><?php _e('Newest'); ?></option>
			<option value="time|ASC"><?php _e('Oldest'); ?></option>
			<option value="care|DESC"><?php _e('Most Cared About'); ?></option>
			<option value="care|ASC"><?php _e('Least Cared About'); ?></option>
			<option value="domi|ASC"><?php _e('Most Divided'); ?></option>
			<option value="domi|DESC"><?php _e('Most Agreed On'); ?></option>
			<option value="resp|DESC"><?php _e('Responses'); ?></option>
		</select>&nbsp;&nbsp;
		<label for="urtak-meta-box-search"><?php _e('Search'); ?></label>
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
					<li class="card-question-answers-y" data-answer="y">Yes</li>
					<li class="card-question-answers-n" data-answer="n">No</li>
					<li class="card-question-answers-d" data-answer="d">Don't Care</li>
				</ul>
			</div>
			<div class="urtak-card-info">
				<div class="urtak-card-info-question">
					<textarea class="large-text" name="urtak[question][text][]" placeholder="<?php _e('Ask a Yes or No Question.'); ?>"></textarea>
					<input class="urtak-adder-answer" type="hidden" name="urtak[question][answer][]" value="" />
				</div>
			</div>
			<div class="clear"></div>
			<div class="urtak-card-controls-container">
				<div class="urtak-card-info-asker"><?php _e('Asked by the site'); ?></div>
				<div class="urtak-card-controls">
					<span class="urtak-update-message"><?php _e('Update or Publish this post to submit this question.'); ?></span>
					<a data-action="reject" class="urtak-card-controls-icon-special urtak-card-controls-icon <?php if($question['status'] === 'rejected') { echo 'active'; } ?> urtak-card-controls-icon-rejected" href="#"></a>
					<div class="clear"></div>
				</div>
			</div>
		</div>

	</div>

	<div id="urtak-meta-box-help">
		<div id="urtak-meta-box-help-content">
			<a href="#" id="urtak-meta-box-help-handle"><?php _e('Help'); ?></a>

			<div id="urtak-meta-box-help-content-inner">
				<h4><?php _e('How It Works'); ?></h4>

				<?php echo wpautop('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla congue, justo in dictum fermentum, metus turpis ullamcorper ligula, sit amet lobortis libero nulla in velit. Aliquam purus turpis, adipiscing eu gravida a, luctus nec felis. Suspendisse a dui justo. Donec iaculis sagittis sapien quis auctor. Duis ante augue, ultricies non venenatis sed, molestie placerat odio. Duis purus justo, tincidunt vel malesuada vitae, ullamcorper quis purus. Curabitur semper gravida egestas. Vivamus quis quam sit amet lorem faucibus facilisis porta sed felis. Cras iaculis, dolor non molestie consectetur, tortor augue sodales risus, ac adipiscing libero sapien in est. Aliquam eget elit lorem. Nullam luctus condimentum purus ac eleifend. Quisque dictum aliquam urna in pulvinar.

									Integer felis ante, pellentesque ut porttitor eget, aliquet et turpis. Morbi non placerat magna. Cras mauris mi, commodo sed commodo quis, ullamcorper et mauris. Curabitur id sem vitae augue tristique tempus. Suspendisse vehicula sapien quis neque tempus malesuada in nec neque. Nulla ante purus, adipiscing vitae cursus at, sollicitudin iaculis magna. Pellentesque tempor volutpat dolor at sollicitudin. Nam metus leo, interdum et tempus id, iaculis sed nisi. Ut molestie auctor elementum. Vestibulum libero erat, vestibulum in commodo et, mattis ac dui. Donec leo odio, hendrerit sed feugiat ut, egestas sed lorem. Nam et mauris ipsum, non gravida mauris.'); ?>
			</div>
		</div>
	</div>
</div>

<div class="urtak-meta-box-actions" id="urtak-meta-box-controls">
	<div id="urtak-meta-box-cards-bottom-shadow-left"></div>
	<div id="urtak-meta-box-cards-bottom-shadow" class="urtak-meta-box-cards-shadow"></div>
	<div id="urtak-meta-box-cards-bottom-shadow-right"></div>

	<div id="urtak-meta-box-controls-per-page" class="alignleft">
		<?php _e('Show'); ?>
		<a class="urtak-meta-box-controls-per-page-link active" href="#">10</a>
		| <a class="urtak-meta-box-controls-per-page-link" href="#">50</a>
		| <a class="urtak-meta-box-controls-per-page-link" href="#">100</a> <?php _e('per page'); ?>
	</div>

	<div id="urtak-meta-box-controls-pager" class="alignright">
		<?php echo self::_get_pager(1, 1); ?>
	</div>

	<div class="clear"></div>
</div>

<?php wp_nonce_field('save-urtak-meta', 'save-urtak-meta-nonce'); ?>


<style type="text/css">
#urtak-meta-box-cards.loading {
	background: #f8f8f8 url(<?php echo admin_url('images/wpspin_light.gif'); ?>) no-repeat center center;
}
</style>