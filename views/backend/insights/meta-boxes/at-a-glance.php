<ul class="urtak-tabbed-control" id="urtak-at-a-glance-tabbed-control">
	<li class="active"><a data-key="days" href="#urtak-at-a-glance-days-chart"><?php _e('Days', 'urtak'); ?></a></li>
	<li><a data-key="weeks" href="#urtak-at-a-glance-weeks-chart"><?php _e('Weeks', 'urtak'); ?></a></li>
	<li><a data-key="months" href="#urtak-at-a-glance-months-chart"><?php _e('Months', 'urtak'); ?></a></li>
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

<div class="urtak-clear"></div>

<div class="urtak-statistics-section-container">

	<div class="urtak-statistics-section">
		<div>
			<h2><?php esc_html_e(number_format_i18n($total_responses)); ?></h2>
			<strong><?php _e('Total Responses', 'urtak'); ?></strong>
		</div>
	</div>

	<div class="urtak-statistics-section">
		<div>
			<h2><?php esc_html_e(number_format_i18n($total_urtaks)); ?></h2>
			<strong><?php _e('Total Urtaks', 'urtak'); ?></strong>
		</div>
	</div>

	<div class="urtak-statistics-section">
		<div>
			<h2><?php esc_html_e(number_format_i18n($total_questions)); ?></h2>
			<strong><?php _e('Total Questions', 'urtak'); ?></strong>
		</div>
	</div>

	<div class="urtak-statistics-section">
		<div>
			<h2><?php esc_html_e(number_format_i18n($responses_today)); ?></h2>
			<strong><?php _e('Responses Today', 'urtak'); ?></strong>
		</div>
	</div>

</div>

<div class="urtak-clear"></div>

<script type="text/javascript">
	var urtak_aag_days = jQuery.parseJSON('<?php echo json_encode($days); ?>')
	, urtak_aag_weeks = jQuery.parseJSON('<?php echo json_encode($weeks); ?>')
	, urtak_aag_months = jQuery.parseJSON('<?php echo json_encode($months); ?>')
	, urtak_data_days = []
	, urtak_data_weeks = []
	, urtak_data_months =[]
	, urtak_ticks_days = []
	, urtak_ticks_weeks = []
	, urtak_ticks_months = [];

	for(var i = 0; i < urtak_aag_days.length; i++) {
		urtak_data_days.push([i*2, urtak_aag_days[i].responses]);
		urtak_ticks_days.push([i*2, urtak_aag_days[i].date]);
	}

	for(var i = 0; i < urtak_aag_weeks.length; i++) {
		urtak_data_weeks.push([i*2, urtak_aag_weeks[i].responses]);
		urtak_ticks_weeks.push([i*2, urtak_aag_weeks[i].date]);
	}

	for(var i = 0; i < urtak_aag_months.length; i++) {
		urtak_data_months.push([i*2, urtak_aag_months[i].responses]);
		urtak_ticks_months.push([i*2, urtak_aag_months[i].date]);
	}

	function urtak_plot_aag_plots(keys) {
		jQuery('#urtak-at-a-glance-days-chart-placeholder').empty();
		jQuery('#urtak-at-a-glance-weeks-chart-placeholder').empty();
		jQuery('#urtak-at-a-glance-months-chart-placeholder').empty();

		jQuery.each(keys, function(index, element) {
			var data = window['urtak_data_' + this], ticks = window['urtak_ticks_' + this];

			UrtakPlot('#urtak-at-a-glance-' + this + '-chart-placeholder', data, ticks);
		});
	}

	jQuery(window).resize(function() {
		urtak_plot_aag_plots([jQuery('#urtak-at-a-glance-tabbed-control .active a').attr('data-key')]);
	});

	jQuery('#urtak-at-a-glance-tabbed-control > li a').click(function(event) {
		event.preventDefault();

		var data_key = jQuery(this).attr('data-key');

		setTimeout(function() {
			urtak_plot_aag_plots([data_key]);
		}, 25);
	}).parent().filter(':first-child').find('a').click();

</script>