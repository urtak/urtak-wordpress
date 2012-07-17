<?php if(!empty($pending)) { ?>
<br />
<?php } ?>
<div id="urtak-pending-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php if(false === $pending) { ?>
	<div id="setting-error-settings_updated" class="settings-error error">
		<p><?php _e('Your publication\'s pending questions could not be retrieved.', 'urtak'); ?></p>
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