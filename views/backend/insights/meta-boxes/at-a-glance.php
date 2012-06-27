<ul class="urtak-tabbed-control" id="urtak-at-a-glance-tabbed-control">
	<li class="active"><a href="#urtak-at-a-glance-days-chart"><?php _e('Days'); ?></a></li>
	<li><a href="#urtak-at-a-glance-weeks-chart"><?php _e('Weeks'); ?></a></li>
	<li><a href="#urtak-at-a-glance-months-chart"><?php _e('Months'); ?></a></li>
</ul>

<div id="urtak-at-a-glance-days-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
    <div class="urtak-at-a-glance-chart-placeholder" id="urtak-at-a-glance-days-chart-placeholder"></div>
</div>

<div id="urtak-at-a-glance-weeks-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
	<div class="urtak-at-a-glance-chart-placeholder" id="urtak-at-a-glance-weeks-chart-placeholder"></div>
</div>

<div id="urtak-at-a-glance-months-chart" data-tabbed-depend-on="urtak-at-a-glance-tabbed-control">
	<div class="urtak-at-a-glance-chart-placeholder" id="urtak-at-a-glance-months-chart-placeholder"></div>
</div>	

<script type="text/javascript">
	var urtak_aag_days = jQuery.parseJSON('<?php echo json_encode($days); ?>')
	, urtak_aag_weeks = {}
	, urtak_aag_months = {}
	, urtak_data_days = []
	, urtak_data_weeks = []
	, urtak_data_months =[]
	, urtak_ticks_days = []
	, urtak_ticks_weeks = []
	, urtak_ticks_months = [];

	jQuery('#urtak-at-a-glance-tabbed-control > li:first-child a').click();

	for(var i = 0; i < urtak_aag_days.length; i++) {
		urtak_data_days.push([i*2, urtak_aag_days[i].responses]);
		urtak_ticks_days.push([i*2, urtak_aag_days[i].date]);
	}

	function urtak_plot_aag_plots() {
		jQuery('#urtak-at-a-glance-days-chart-placeholder').empty();
		jQuery('#urtak-at-a-glance-weeks-chart-placeholder').empty();
		jQuery('#urtak-at-a-glance-months-chart-placeholder').empty();

		console.log(urtak_data_days);
		console.log(urtak_ticks_days);
		jQuery.plot(
			jQuery('#urtak-at-a-glance-days-chart-placeholder'), 
			[
				{ 
					data: urtak_data_days, 
            		bars: { 
            			align: 'center', 
            			fill: '#00aef0',
            			fillColor: '#00aef0',
            			show: true 
            		},
            		color: '#00aef0'
            	}
            ], 
			{
				grid: {
					borderWidth: 0,
					color: '#666666',
					show: true
				},
				xaxis: {
					max: (urtak_ticks_days.length * 2) - 1,
					min: -1, 
					tickLength: 0,
					ticks: urtak_ticks_days 
				}
			}
		);
	}

	jQuery(window).resize(function() {
		urtak_plot_aag_plots();
	});

	urtak_plot_aag_plots();
</script>