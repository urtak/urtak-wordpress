<ul class="urtak-tabbed-control" id="urtak-at-a-glance-tabbed-control">
	<li class="active"><a href="#urtak-at-a-glance-days-chart"><?php _e('Days'); ?></a></li>
	<li><a href="#urtak-at-a-glance-weeks-chart"><?php _e('Weeks'); ?></a></li>
	<li><a href="#urtak-at-a-glance-months-chart"><?php _e('Months'); ?></a></li>
</ul>

<div id="urtak-at-a-glance-days-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
	<div class="urtak-bar-graph urtak-bar-graph-many">
		<?php foreach($dates as $date) { 
			$scaled = intval($date['responses'] / $maximum_responses * 200); ?>
		<div class="urtak-bar-graph-item">
			<div class="urtak-bar-graph-item-container">
				<div class="urtak-bar-graph-item-inner" style="<?php printf('height: %dpx;', $scaled); ?>">
					<div class="urtak-bar-graph-item-value"><strong><?php esc_html_e(number_format_i18n($date['responses'], 0)); ?></strong></div>
				</div>
				<div class="urtak-bar-graph-item-identifier"><?php echo ($date['date']); ?></div>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="clear"></div>
</div>

<div id="urtak-at-a-glance-weeks-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
	Weeks Chart
</div>

<div id="urtak-at-a-glance-months-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
	Months Chart
</div>	