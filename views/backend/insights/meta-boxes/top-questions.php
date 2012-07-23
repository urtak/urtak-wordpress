<ul class="urtak-tabbed-control" id="urtak-questions-tabbed-control">
	<li class="active"><a href="#urtak-pending-questions"><?php _e('Pending', 'urtak'); ?></a></li>
	<li><a href="#urtak-divided-questions"><?php _e('Most Divided', 'urtak'); ?></a></li>
	<li><a href="#urtak-cared-questions"><?php _e('Most Cared About', 'urtak'); ?></a></li>
	<li><a href="#urtak-agreed-questions"><?php _e('Most Agreed On', 'urtak'); ?></a></li>
</ul>

<div id="urtak-pending-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php if(false === $pending) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('Your site\'s pending questions could not be retrieved.', 'urtak'); ?></p>
	</div>
	<?php } else if(empty($pending)) { ?>
	<div id="setting-error-settings_updated" class="settings-error updated">
		<p><?php _e('You don\'t have any pending questions.', 'urtak'); ?></p>
	</div>
	<?php } else { ?>
		<?php foreach($pending as $question) { ?>
			<?php echo self::_get_card($question, $post_id, true, true); ?>
		<?php } ?>
	<?php } ?>
</div>

<div id="urtak-divided-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php if(false === $most_divided) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('Your site\'s most divided questions could not be retrieved.', 'urtak'); ?></p>
	</div>
	<?php } else if(empty($most_divided)) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('No results for most divided questions.', 'urtak'); ?></p>
	</div>
	<?php } else { ?>
		<?php foreach($most_divided as $question) { ?>
			<?php echo self::_get_card($question, 0); ?>
		<?php } ?>
	<?php } ?>
</div>

<div id="urtak-cared-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php if(false === $most_cared) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('Your site\'s most cared about questions could not be retrieved.', 'urtak'); ?></p>
	</div>
	<?php } else if(empty($most_cared)) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('No results for most cared about questions.', 'urtak'); ?></p>
	</div>
	<?php } else { ?>
		<?php foreach($most_cared as $question) { ?>
			<?php echo self::_get_card($question, 0); ?>
		<?php } ?>
	<?php } ?>
</div>

<div id="urtak-agreed-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php if(false === $most_agreed) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('Your site\'s most agreed on questions could not be retrieved.', 'urtak'); ?></p>
	</div>
	<?php } else if(empty($most_agreed)) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('No results for most agreed on questions', 'urtak'); ?></p>
	</div>
	<?php } else { ?>
		<?php foreach($most_agreed as $question) { ?>
			<?php echo self::_get_card($question, 0); ?>
		<?php } ?>
	<?php } ?>
</div>

<script type="text/javascript">
	jQuery('#urtak-questions-tabbed-control > li:first-child a').click();
</script>
