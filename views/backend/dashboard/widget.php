<div id="urtak-at-a-glance-days-chart">
    <div style="height: 250px; width: 100%;" class="urtak-at-a-glance-chart-placeholder" id="urtak-at-a-glance-days-chart-placeholder"></div>
</div>

<div class="urtak-clear"></div>

<script type="text/javascript">
	var urtak_aag_days = jQuery.parseJSON('<?php echo json_encode($days); ?>')
	, urtak_data_days = []
	, urtak_ticks_days = [];

	for(var i = 0; i < urtak_aag_days.length; i++) {
		urtak_data_days.push([i*2, urtak_aag_days[i].responses]);
		urtak_ticks_days.push([i*2, urtak_aag_days[i].date]);
	}

	function widget_urtak_plot_aag_plots() {
		jQuery('#urtak-at-a-glance-days-chart-placeholder').empty();

		UrtakDelegates.plot_bar_graph('#urtak-at-a-glance-days-chart-placeholder', urtak_data_days, urtak_ticks_days);
	}

	jQuery(window).resize(function() {
		widget_urtak_plot_aag_plots();
	});

	widget_urtak_plot_aag_plots();
</script>