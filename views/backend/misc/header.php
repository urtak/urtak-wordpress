<?php $has_credentials = self::has_credentials(); ?>

<div class="urtak-wrap wrap">
	<?php screen_icon(); ?>

	<?php $base = admin_url('admin.php'); ?>
	<h2 class="nav-tab-wrapper">
		<span class="urtak-wrap-title"><?php _e('Urtak', 'urtak'); ?></span>

		<a href="<?php esc_attr_e(add_query_arg(array('page' => self::SUB_LEVEL_INSIGHTS_SLUG), $base), 'urtak'); ?>" class="nav-tab <?php if($is_insights) { ?>nav-tab-active<?php } ?>"><?php _e('Insights', 'urtak'); ?></a>

		<?php if(current_user_can('manage_options')) { ?>
		<a href="<?php esc_attr_e(add_query_arg(array('page' => self::SUB_LEVEL_SETTINGS_SLUG), $base), 'urtak'); ?>" class="nav-tab <?php if($is_settings) { ?>nav-tab-active<?php } ?>"><?php _e('Settings', 'urtak'); ?></a>
		<?php } ?>

		<small class="urtak-logged-in">
			<?php if($has_credentials) { ?>
			<?php printf(__('Logged in as <a href="https://urtak.com/account/edit" target="_blank">%1$s</a>', 'urtak'), self::get_credentials('email')); ?>
			&nbsp;| &nbsp;
			<?php } ?>
			<a target="_blank" href="http://blog.urtak.com/"><?php _e('Blog', 'urtak'); ?></a>
			&nbsp;| &nbsp;
			<a target="_blank" href="http://about.urtak.com/"><?php _e('Help', 'urtak'); ?></a>
			&nbsp;| &nbsp;
			<a target="_blank" href="http://about.urtak.com/terms"><?php _e('Privacy &amp; Terms', 'urtak'); ?></a>
		</small>
	</h2>

	<div class="urtak-inner-wrap <?php if($is_settings) { ?>urtak-inner-wrap-settings<?php } ?>">
		<?php settings_errors(); ?>
		<br />
