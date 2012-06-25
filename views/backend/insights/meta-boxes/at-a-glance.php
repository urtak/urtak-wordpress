<div class="urtak-bar-graph urtak-bar-graph-many">
	<?php foreach($dates as $date) { 
		$scaled = intval($date['responses'] / $maximum_responses * 200);
		$urtak_yes = $date['yes'] >= $date['no'] ? 'urtak-yes' : '';  ?>
	<div class="urtak-bar-graph-item <?php echo $urtak_yes; ?>">
		<div class="urtak-bar-graph-item-container">
			<div class="urtak-bar-graph-item-inner <?php echo $urtak_yes; ?>" style="<?php printf('height: %dpx;', $scaled); ?>">
				<div class="urtak-bar-graph-item-value"><strong><?php esc_html_e(number_format_i18n($date['responses'], 0)); ?></strong></div>
			</div>
			<div class="urtak-bar-graph-item-identifier"><?php esc_html_e($date['date']); ?></div>
		</div>
	</div>
	<?php } ?>
</div>
<div class="clear"></div>