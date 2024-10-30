<div class="wrap lbc-local-seo-settings-wrapper">
	<?php
	if (file_exists($view_data['plg_root_path'] . 'views/top-adverts.php')) {
		include_once $view_data['plg_root_path'] . 'views/top-adverts.php';
	}
	?>

	<div class="icon32" id="lbc_page_icon"></div>
	<h2>LBC Local SEO - <?php echo esc_html(__('Settings', 'lbc-local-seo')); ?></h2>
	
	<form id="lbc_local_seo_settings_form" action="" name="" method="post">
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Settings', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-settings-changes-button">
			<span class="lbc-save-settings-changes-msg"></span>
		</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_location_page_slug"><?php echo esc_html(__('Location page slug', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Location page slug', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_location_page_slug" name="lbc_var_local_seo_location_page_slug" value="<?php echo esc_attr($view_data['settings']['location_page_slug']); ?>" />
							<input type="hidden" id="lbc_original_location_page_slug" value="<?php echo esc_attr($view_data['settings']['location_page_slug']); ?>">
						</fieldset>
						<div class="lbc-setting-desc">
							<?php echo esc_html(__('Unique string for the location. It will appear in the URL of the location page. Allowed characters', 'lbc-local-seo') . ': a-z, 0-9, -. ' . __('E.g.: store-location', 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__('If you leave this field empty the slug will be automatically generated.', 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__("A WP page will be automatically created if it doesn't exist yet. (name: ", 'lbc-local-seo') . __("'Location'", 'lbc-local-seo') . __(', slug: the slug above)', 'lbc-local-seo')); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_google_maps_api_key"><?php echo esc_html(__('Google Maps API key', 'lbc-local-seo')) . ':<br/>' . esc_html(__('(optional)', 'lbc-local-seo')); ?></label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Google Maps API key (optional)', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_google_maps_api_key" name="lbc_var_local_seo_google_maps_api_key" class="lbc-input-medium" value="<?php echo esc_attr($view_data['settings']['google_maps_api_key']); ?>" />
						</fieldset>
						<div class="lbc-setting-desc">
							<?php echo esc_html(__('Google Maps API key is optional. You can leave this field empty.', 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__('If you want to monitor your usage of Google Maps then you need to enter a Google Maps API key.', 'lbc-local-seo')); ?>
							<a href="https://developers.google.com/maps/documentation/javascript/tutorial?csw=1#api_key" target="_blank"><?php echo esc_html(__('To find out how to obtain an API key please click here.', 'lbc-local-seo')); ?></a>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_address_format"><?php echo esc_html(__('Address format', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Address format', 'lbc-local-seo')); ?></span></legend>
							<select id="lbc_var_local_seo_address_format" name="lbc_var_local_seo_address_format">
								<?php
								foreach ($view_data['addr_formats'] as $addr_format_id => $addr_format_title) {
									$selected = ($view_data['settings']['addr_format'] == $addr_format_id) ? ' selected="selected"' : '';
									echo '<option value="' . esc_attr($addr_format_id) . '"' . $selected . '>' . esc_html($addr_format_title) . '</option>';
								}
								?>
							</select>
						</fieldset>
						<div class="lbc-setting-desc">
							<?php echo "' / ' " . esc_html(__('will be replaced with a line break. If you need a different address format please feel free to contact us and we will add it. Email', 'lbc-local-seo')); ?>:
							<a href="mailto:support@localbizcommando.com">support@localbizcommando.com</a>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_opening_hours_format"><?php echo esc_html(__('Opening hours format', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Opening hours format', 'lbc-local-seo')); ?></span></legend>
							<select id="lbc_var_local_seo_opening_hours_format" name="lbc_var_local_seo_opening_hours_format">
								<?php
								foreach ($view_data['opening_hours_formats'] as $o_h_id => $o_h_title) {
									$selected = ($view_data['settings']['opening_hours_format'] == $o_h_id) ? ' selected="selected"' : '';
									echo '<option value="' . esc_attr($o_h_id) . '"' . $selected . '>' . esc_html($o_h_title) . '</option>';
								}
								?>
							</select>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Settings', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-settings-changes-button">
			<span class="lbc-save-settings-changes-msg"></span>
		</p>
	</form>
	<div class="lbc-created-by">
		<?php echo esc_html(__('Created by', 'lbc-local-seo')); ?> <a href="http://www.localbizcommando.com" target="_blank">Local Biz Commando</a>
	</div>
</div>