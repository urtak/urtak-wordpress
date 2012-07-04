<ul class="urtak-tabbed-control" id="urtak-questions-tabbed-control">
	<li class="active"><a href="#urtak-pending-questions"><?php _e('Pending'); ?></a></li>
	<li><a href="#urtak-divided-questions"><?php _e('Most Divided'); ?></a></li>
	<li><a href="#urtak-cared-questions"><?php _e('Most Cared'); ?></a></li>
	<li><a href="#urtak-agreed-questions"><?php _e('Most Agreed'); ?></a></li>
</ul>

<div id="urtak-pending-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php for($i = 0; $i < 5; $i++) { ?>
		<?php echo self::_get_card($question, $post_id, true); ?>
	<?php } ?>
</div>

<div id="urtak-divided-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php for($i = 0; $i < 5; $i++) { ?>
		<?php echo self::_get_card($question, $post_id); ?>
	<?php } ?>
</div>

<div id="urtak-cared-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php for($i = 0; $i < 5; $i++) { ?>
		<?php echo self::_get_card($question, $post_id); ?>
	<?php } ?>
</div>

<div id="urtak-agreed-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	<?php for($i = 0; $i < 5; $i++) { ?>
		<?php echo self::_get_card($question, $post_id); ?>
	<?php } ?>
</div>	


<script type="text/javascript">
	jQuery('#urtak-questions-tabbed-control > li:first-child a').click();
</script>