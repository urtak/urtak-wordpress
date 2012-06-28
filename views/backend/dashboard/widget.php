<div id="urtak-dashboard-chart">
	<div class="urtak-at-a-glance-chart-placeholder" id="urtak-dashboard-chart-placeholder"></div>
</div>	

<table class="widefat">
	<thead>
		<tr valign="top">
			<th scope="col" class="urtak-title-column"><?php _e('Top Urtaks'); ?></th>
			<th scope="col" class="urtak-responses-column"><?php _e('Responses'); ?></th>
			<th scope="col" class="urtak-questions-column"><?php _e('Questions'); ?></th>
			<th scope="col" class="urtak-users-column"><?php _e('Users'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($urtaks as $urtak) { ?>
		<tr valign="top">
			<td class="urtak-title-column"><?php esc_html_e($urtak->title); ?></td>
			<td class="urtak-responses-column"><?php esc_html_e($urtak->responses); ?></td>
			<td class="urtak-questions-column"><?php esc_html_e($urtak->questions); ?></td>
			<td class="urtak-users-column"><?php esc_html_e($urtak->users); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">
	var urtak_aag_days = jQuery.parseJSON('<?php echo json_encode($days); ?>')
	, urtak_data_days = []
	, urtak_ticks_days = [];

	for(var i = 0; i < urtak_aag_days.length; i++) {
		urtak_data_days.push([i*2, urtak_aag_days[i].responses]);
		urtak_ticks_days.push([i*2, urtak_aag_days[i].date]);
	}

	function urtak_plot_dashboard_plots() {
		jQuery('#urtak-dashboard-chart-placeholder').empty();

		if(!jQuery('#urtak.postbox').hasClass('closed')) {
			UrtakDelegates.plot_bar_graph('#urtak-dashboard-chart-placeholder', urtak_data_days, urtak_ticks_days);
		}
	}

	jQuery(window).resize(function() {
		setTimeout(urtak_plot_dashboard_plots, 25);
	});

	urtak_plot_dashboard_plots();

	jQuery('#urtak.postbox .hndle, #urta.postbox .handlediv').live('click', function(event) {
		urtak_plot_dashboard_plots();
	});

</script>