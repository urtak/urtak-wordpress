<ul class="urtak-tabbed-control" id="urtak-questions-tabbed-control">
	<li class="active"><a href="#urtak-at-a-glance-pending-questions"><?php _e('Pending'); ?></a></li>
	<li><a href="#urtak-divided-questions"><?php _e('Most Divided'); ?></a></li>
	<li><a href="#urtak-cared-questions"><?php _e('Most Cared'); ?></a></li>
	<li><a href="#urtak-agreed-questions"><?php _e('Most Agreed'); ?></a></li>
</ul>

<div id="urtak-pending-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	Pending Data
</div>

<div id="urtak-divided-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	Most Divided Data
</div>

<div id="urtak-cared-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	Most Cared Data
</div>

<div id="urtak-agreed-questions" data-tabbed-depend-on="urtak-questions-tabbed-control">
	Most Agreed Data
</div>	


<script type="text/javascript">
	jQuery('#urtak-questions-tabbed-control > li:first-child a').click();
</script>