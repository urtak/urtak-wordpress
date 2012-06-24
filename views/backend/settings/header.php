<div class="urtak-wrap wrap">
	<?php screen_icon(); ?>

	<?php $base = admin_url('admin.php'); ?>
	<h2 class="nav-tab-wrapper">
		<span class="urtak-wrap-title"><?php _e('Urtak'); ?></span>
		<a href="<?php esc_attr_e(add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), $base)); ?>" class="nav-tab <?php if($is_insights) { ?>nav-tab-active<?php } ?>"><?php _e('Insights'); ?></a>
		<a href="<?php esc_attr_e(add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), $base)); ?>" class="nav-tab <?php if($is_settings) { ?>nav-tab-active<?php } ?>"><?php _e('Settings'); ?></a>
		<small class="urtak-logged-in"><?php printf(__('Logged in as %1$s'), 'nohrn@ohrnventures.com'); ?></small>
	</h2>