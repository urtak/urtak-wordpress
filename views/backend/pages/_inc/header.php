<div class="urtak-wrap wrap">
	<?php screen_icon(); ?>

	<h2 class="nav-tab-wrapper">
		<span class="urtak-wrap-title"><?php _e('Urtak', 'urtak'); ?></span>

		<a href="<?php esc_attr_e(esc_url($url_moderation)); ?>" class="nav-tab <?php echo $active_moderation ? 'nav-tab-active' : ''; ?>"><?php _e('Moderation', 'urtak'); ?></a>
		<a href="<?php esc_attr_e(esc_url($url_results)); ?>" class="nav-tab <?php echo $active_results ? 'nav-tab-active' : ''; ?>"><?php _e('Results', 'urtak'); ?></a>

		<?php if($manage_options) { ?>
		<a href="<?php esc_attr_e(esc_url($url_settings)); ?>" class="nav-tab <?php echo $active_settings ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'urtak'); ?></a>
		<?php } ?>

		<small class="urtak-logged-in">
			<?php echo $logged_in_text; ?>
			<a target="_blank" href="http://blog.urtak.com/"><?php _e('Blog', 'urtak'); ?></a>
			&nbsp;| &nbsp;
			<a target="_blank" href="http://about.urtak.com/"><?php _e('Help', 'urtak'); ?></a>
			&nbsp;| &nbsp;
			<a target="_blank" href="http://about.urtak.com/terms"><?php _e('Privacy &amp; Terms', 'urtak'); ?></a>
		</small>
	</h2>

	<div class="urtak-inner-wrap <?php echo $active_settings ? 'urtak-inner-wrap-settings' : ''; ?>">
		<?php settings_errors(); ?>
		<br />
