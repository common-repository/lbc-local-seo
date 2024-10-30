<div class="wrap lbc-local-seo-settings-wrapper">
	<?php
	if (file_exists($view_data['plg_root_path'] . 'views/top-adverts.php')) {
		include_once $view_data['plg_root_path'] . 'views/top-adverts.php';
	}
	?>

	<div class="icon32" id="lbc_page_icon"></div>
	<h2>LBC Local SEO - <?php echo esc_html(__('User Guide', 'lbc-local-seo')); ?></h2>
	
	<h3 class="title"><?php echo esc_html(__('Set up guide', 'lbc-local-seo')); ?></h3>
	<div class="lbc-form-panel">
		<ol>
			<li><?php echo esc_html(__("Go to the 'LBC Local SEO/Settings' page.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Enter/select all information.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Click on the 'Save Settings' button. (A WP page will be automatically created if it doesn't exist yet. [name: 'Location'])", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Go to the 'LBC Local SEO/Location' page.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Enter/select all information.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Click on the 'Save Location' button. (Any 'Save Location' button on the page saves all location settings.)", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Go to the 'Appearance/Menus' page.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Find and add the 'Location' WP page to a menu.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Click on the 'Save Menu' button.", 'lbc-local-seo')); ?></li>
			<li><?php echo esc_html(__("Congratulations! ;) You've set up the", 'lbc-local-seo') . ' LBC Local SEO plugin.'); ?></li>
		</ol>
	</div>
	<div class="lbc-created-by">
		<?php echo esc_html(__('Created by', 'lbc-local-seo')); ?> <a href="http://www.localbizcommando.com" target="_blank">Local Biz Commando</a>
	</div>
</div>