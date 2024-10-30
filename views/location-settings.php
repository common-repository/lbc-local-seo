<div class="wrap lbc-local-seo-settings-wrapper">
	<?php
	if (file_exists($view_data['plg_root_path'] . 'views/top-adverts.php')) {
		include_once $view_data['plg_root_path'] . 'views/top-adverts.php';
	}
	?>
	
	<div class="icon32" id="lbc_page_icon"></div>
	<h2>LBC Local SEO - <?php echo esc_html(__('Edit Location', 'lbc-local-seo')); ?></h2>

	<form id="lbc_local_seo_location_settings_form" action="" name="" method="post">
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Business', 'lbc-local-seo'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_name"><?php echo esc_html(__('Name', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Name', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_name" name="lbc_var_local_seo_business_name" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['name']); ?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_type"><?php echo esc_html(__('Type', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Type', 'lbc-local-seo')); ?></span></legend>
							<select id="lbc_var_local_seo_business_type" name="lbc_var_local_seo_business_type">
								<?php
								foreach ($view_data['business_types'] as $b_type_id => $b_type_data) {
									$selected = ($view_data['location_settings']['type'] == $b_type_id) ? ' selected' : '';
									echo '<option value="' . esc_attr($b_type_id) . '"' . $selected . '>' . esc_html($b_type_data['title']) . '</option>';
									if (isset($b_type_data['subtypes'])) {
										foreach ($b_type_data['subtypes'] as $b_subtype_id => $b_subtype_title) {
											$selected = ($view_data['location_settings']['type'] == $b_subtype_id) ? ' selected' : '';
											echo '<option value="' . esc_attr($b_subtype_id) . '"' . $selected . '>&gt;&gt; ' . esc_html($b_subtype_title) . '</option>';
										}
									}
								}
								?>
							</select>
						</fieldset>
						<div class="lbc-setting-desc"><?php echo esc_html(__('Select the type that is the most relevant to your business. If none of them is relevant then select: ', 'lbc-local-seo') . $view_data['business_types']['LocalBusiness']['title']); ?></div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_short_desc"><?php echo esc_html(__('Short description', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Short description', 'lbc-local-seo')); ?></span></legend>
							<textarea id="lbc_var_local_seo_business_short_desc" name="lbc_var_local_seo_business_short_desc" class="lbc-textarea-medium"><?php echo esc_textarea($view_data['location_settings']['short_desc']); ?></textarea>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_meta_desc"><?php echo esc_html(__('Meta description', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Meta description', 'lbc-local-seo')); ?></span></legend>
							<textarea id="lbc_var_local_seo_business_meta_desc" name="lbc_var_local_seo_business_meta_desc" maxlength="<?php echo esc_attr(LBC_LS_META_DESC_MAX_LEN); ?>" class="lbc-textarea-medium"><?php echo esc_html($view_data['location_settings']['meta_desc']); ?></textarea>
							<span id="local_seo_business_meta_desc_char_countdown" class="lbc-char-countdown"><?php echo (LBC_LS_META_DESC_MAX_LEN - strlen($view_data['location_settings']['meta_desc'])); ?></span>
						</fieldset>
						<div class="lbc-setting-desc">
							<?php echo esc_html(__("Write a compelling description that a searcher will want to click. Keep it relevant to the location and make sure it's unique for each location.", 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__("This description will appear on search engine (Google, Bing, etc.) result pages below the title.", 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__("It will be used by social media sites (Facebook, Twitter, Google+, etc.) as well when this location will be shared.", 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__("If you leave it empty then the first", 'lbc-local-seo') . ' ' . LBC_LS_META_DESC_MAX_LEN . ' ' . __("characters of the short description will be used as meta description. If the short description is empty as well then the address and the phone number will be used as meta description.", 'lbc-local-seo')); ?>
							<br/>
							<?php echo esc_html(__("Max length", 'lbc-local-seo') . ': ' . LBC_LS_META_DESC_MAX_LEN . ' ' . __("character.", 'lbc-local-seo')); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_website_url"><?php echo esc_html(__('Website URL', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Website URL', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_website_url" name="lbc_var_local_seo_business_website_url" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['url']); ?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_logo"><?php echo esc_html(__('Logo', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Logo', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_logo" name="lbc_var_local_seo_business_logo" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['logo']); ?>" />
							<div class="lbc-upload-buttons-wrapper">
								<input type="button" class="button lbc-media-upload-button" value="<?php echo esc_attr(__('Upload', 'lbc-local-seo'));?>" />
								<input type="button" id="lbc_button_remove_business_logo" class="button" value="<?php echo esc_attr(__('Remove', 'lbc-local-seo'));?>" />
							</div>
							<?php $lbc_attr_escaped_business_logo = esc_attr($view_data['location_settings']['logo']); ?>
							<div id="lbc_img_local_seo_business_logo_wrapper" class="lbc-business-logo-wrapper"<?php echo ((!$view_data['is_logo_accessible']) ? ' style="display:none;"' : ''); ?>>
								<img id="lbc_img_local_seo_business_logo" src="<?php echo $lbc_attr_escaped_business_logo;?>" class="lbc-img-preview" alt="<?php echo esc_attr(__('Logo', 'lbc-local-seo')); ?>" />
							</div>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Address', 'lbc-local-seo'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_street_address"><?php echo esc_html(__('Street address', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Street address', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_street_address" name="lbc_var_local_seo_business_street_address" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['street_address']); ?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_city"><?php echo esc_html(__('City/locality', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('City/locality', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_city" name="lbc_var_local_seo_business_city" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['city']); ?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_state"><?php echo esc_html(__('State/region', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('State/region', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_state" name="lbc_var_local_seo_business_state" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['state']); ?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_country"><?php echo esc_html(__('Country', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Country', 'lbc-local-seo')); ?></span></legend>
							<select id="lbc_var_local_seo_business_country" name="lbc_var_local_seo_business_country">
								<?php
								foreach ($view_data['countries'] as $country_code => $country_title) {
									$selected = ($view_data['location_settings']['country'] == $country_code) ? ' selected' : '';
									echo '<option value="' . esc_attr($country_code) . '"' . $selected . '>' . esc_html($country_title) . '</option>';
								}
								?>
							</select>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_postcode"><?php echo esc_html(__('Postcode', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Postcode', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_postcode" name="lbc_var_local_seo_business_postcode" value="<?php echo esc_attr($view_data['location_settings']['postcode']); ?>" />
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Map & Geo coordinates', 'lbc-local-seo'); ?></h3>
		<div class="lbc-form-panel">
			<div class="lbc-form-panel-row">
				<label for="lbc_show_on_location_page_map"><?php echo esc_html(__('Show map on location page: ')); ?></label>
				<input
					type="checkbox"
					id="lbc_show_on_location_page_map"
					name="lbc_show_on_location_page_map"
					value="1"
					<?php echo ($view_data['location_settings']['on_location_page_map'] == 1) ? 'checked' : ''; ?>
				>
			</div>
			<input type="button" id="lbc_map_get_coordinates" class="button lbc-map-get-coordinates" value="<?php echo esc_attr(__('Calculate coordinates based on the address above', 'lbc-local-seo'));?>" />
			<div class="lbc-setting-desc">
				<?php
				echo esc_html(__('Street address and postcode are required for calculating the coordinates!', 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__('If the pin has not been put to the right place you can drag and drop it to the right place.', 'lbc-local-seo'));
				?>
			</div>
			<div id="lbc_map_canvas" class="lbc-map-canvas" style="width: 480px; height: 320px;"></div>
			<label for="lbc_var_local_seo_business_latitude"><?php echo esc_html(__('Latitude: ')); ?></label>
			<input type="text" id="lbc_var_local_seo_business_latitude" name="lbc_var_local_seo_business_latitude" value="<?php echo esc_attr($view_data['location_settings']['latitude']); ?>" />
			<label for="lbc_var_local_seo_business_longitude"><?php echo esc_html(__('Longitude: ')); ?></label>
			<input type="text" id="lbc_var_local_seo_business_longitude" name="lbc_var_local_seo_business_longitude" value="<?php echo esc_attr($view_data['location_settings']['longitude']); ?>" />
		</div>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Contacts', 'lbc-local-seo'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<td colspan="2">
						<label for="lbc_show_on_location_page_contacts"><?php echo esc_html(__('Show contact details on location page: ')); ?></label>
						<input
							type="checkbox"
							id="lbc_show_on_location_page_contacts"
							name="lbc_show_on_location_page_contacts"
							value="1"
							<?php echo ($view_data['location_settings']['on_location_page_contacts'] == 1) ? 'checked' : ''; ?>
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo esc_html(__('Phone numbers', 'lbc-local-seo')); ?>:
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Phone numbers', 'lbc-local-seo')); ?></span></legend>
							<div id="lbc_business_phone_numbers_wrapper">
								<?php
								foreach ($view_data['location_settings']['phone_numbers'] as $phone_id => $phone_num) :
								?>
									<input
										type="text"
										id="lbc_var_local_seo_business_phone_<?php echo esc_attr($phone_id); ?>"
										name="lbc_var_local_seo_business_phone_numbers[]"
										value="<?php echo esc_attr($phone_num); ?>"
									/><br/>
								<?php
								endforeach;
								?>
							</div>
							<input
								type="button"
								id="lbc_phone_numbers_add_new"
								class="button<?php echo (count($view_data['location_settings']['phone_numbers']) > 0 ? ' lbc-action-button' : ''); ?>"
								value="<?php echo esc_attr(__('Add new phone number', 'lbc-local-seo'));?>"
							/>
						</fieldset>
						<div class="lbc-setting-desc"><?php echo esc_html(__('If you would like to delete a phone number then delete the text from the input field and save the form.', 'lbc-local-seo')); ?></div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo esc_html(__('Fax numbers', 'lbc-local-seo')); ?>:
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Fax numbers', 'lbc-local-seo')); ?></span></legend>
							<div id="lbc_business_fax_numbers_wrapper">
								<?php
								foreach ($view_data['location_settings']['fax_numbers'] as $fax_id => $fax_num) :
								?>
									<input
										type="text"
										id="lbc_var_local_seo_business_fax_<?php echo esc_attr($fax_id); ?>"
										name="lbc_var_local_seo_business_fax_numbers[]"
										value="<?php echo esc_attr($fax_num); ?>"
									/><br/>
								<?php
								endforeach;
								?>
							</div>
							<input
								type="button"
								id="lbc_fax_numbers_add_new"
								class="button<?php echo (count($view_data['location_settings']['fax_numbers']) > 0 ? ' lbc-action-button' : ''); ?>"
								value="<?php echo esc_attr(__('Add new fax number', 'lbc-local-seo'));?>"
							/>
						</fieldset>
						<div class="lbc-setting-desc"><?php echo esc_html(__('If you would like to delete a fax number then delete the text from the input field and save the form.', 'lbc-local-seo')); ?></div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_business_email"><?php echo esc_html(__('Email', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Email', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_business_email" name="lbc_var_local_seo_business_email" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['email']); ?>" />
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Payments', 'lbc-local-seo'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<td colspan="2">
						<label for="lbc_show_on_location_page_payments"><?php echo esc_html(__('Show payment details on location page: ')); ?></label>
						<input
							type="checkbox"
							id="lbc_show_on_location_page_payments"
							name="lbc_show_on_location_page_payments"
							value="0"
							<?php echo ($view_data['location_settings']['on_location_page_payments'] == 1) ? 'checked' : ''; ?>
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_accepted_payment_type"><?php echo esc_html(__('Accepted payment types', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Accepted payment types', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_accepted_payment_type" name="lbc_var_local_seo_accepted_payment_type" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['accepted_payment_type']); ?>" placeholder="<?php echo esc_attr(__('Cash, credit card, PayPal, etc.', 'lbc-local-seo')); ?>" />
						</fieldset>
						<div class="lbc-setting-desc"><?php echo esc_html(__('Cash, credit card, PayPal, etc.', 'lbc-local-seo')); ?></div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_accepted_currencies_str"><?php echo esc_html(__('Accepted currencies', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Accepted currencies', 'lbc-local-seo')); ?></span></legend>
							<input type="text" id="lbc_var_local_seo_accepted_currencies_str" name="lbc_var_local_seo_accepted_currencies_str" class="lbc-input-medium" value="<?php echo esc_attr($view_data['location_settings']['accepted_currencies']); ?>" readonly="readonly" /><br/>
							<input type="button" id="lbc_currencies_dialog" class="button lbc-action-button" value="<?php echo esc_attr(__('Currencies', 'lbc-local-seo'));?>" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="lbc_var_local_seo_price_range"><?php echo esc_html(__('Price range (for Google listings)', 'lbc-local-seo')); ?>:</label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo esc_html(__('Price range (for Google listings)', 'lbc-local-seo')); ?></span></legend>
							<select id="lbc_var_local_seo_price_range" name="lbc_var_local_seo_price_range">
								<?php
								foreach ($view_data['price_ranges'] as $pr) {
									$selected = ($view_data['location_settings']['price_range'] == $pr) ? ' selected' : '';
									echo '<option value="' . esc_attr($pr) . '"' . $selected . '>' . esc_html($pr) . '</option>';
								}
								?>
							</select>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Opening hours', 'lbc-local-seo'); ?></h3>
		<div class="lbc-form-panel">
			<label for="lbc_show_on_location_page_opening_hours"><?php echo esc_html(__('Show opening hours on location page: ')); ?></label>
			<input
				type="checkbox"
				id="lbc_show_on_location_page_opening_hours"
				name="lbc_show_on_location_page_opening_hours"
				value="0"
				<?php echo ($view_data['location_settings']['on_location_page_opening_hours'] == 1) ? 'checked' : ''; ?>
			>
			<table class="lbc-opening-hours">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th colspan="3"><?php echo esc_html(__('1st set of opening hours', 'lbc-local-seo')); ?></th>
						<th>&nbsp;</th>
						<th colspan="3"><?php echo esc_html(__('2nd set of opening hours', 'lbc-local-seo')); ?></th>
					</tr>
					<tr>
						<th><?php echo esc_html(__('Day', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Open?', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Opens at', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Closes at', 'lbc-local-seo')); ?></th>
						<th>&nbsp;</th>
						<th><?php echo esc_html(__('Open?', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Opens at', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Closes at', 'lbc-local-seo')); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>&nbsp;</td>
						<td class="lbc-checkbox-cell"><input type="checkbox" id="lbc_1st_opening_hours_all_none_open" value="" /></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td class="lbc-checkbox-cell"><input type="checkbox" id="lbc_2nd_opening_hours_all_none_open" value="" /></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<?php
					foreach ($view_data['opening_hours']['days'] as $day_id => $day_title) :
					?>
						<tr>
							<?php
							foreach (array('1st', '2nd') as $first_second) :
							?>
								<td>
									<?php
									echo ($first_second == '1st') ? esc_html($day_title) : '&nbsp;';
									?>
								</td>
								<td class="lbc-checkbox-cell">
									<input
										type="checkbox"
										id="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_open_<?php echo esc_attr($day_id); ?>"
										name="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_open_<?php echo esc_attr($day_id); ?>"
										value="<?php echo esc_attr($day_id); ?>"
										<?php
										if (isset($view_data['location_settings']['opening_hours'][$day_id][$first_second . '_open']) &&
											$view_data['location_settings']['opening_hours'][$day_id][$first_second . '_open'] == 1)
										{
											echo 'checked';
										}
										?>
									/>
								</td>
								<?php
								foreach (array('opens', 'closes') as $open_close) :
									$splitted_selected_time = array('', '', '');
									if (isset($view_data['location_settings']['opening_hours'][$day_id][$first_second . '_' . $open_close . '_at'])) {
										if ($view_data['settings']['opening_hours_format'] == 12 &&
											($ts = strtotime($view_data['location_settings']['opening_hours'][$day_id][$first_second . '_' . $open_close . '_at'])) != false)
										{
											$splitted_selected_time = explode('-', date('g-i-a', $ts));
										}
										elseif ($view_data['settings']['opening_hours_format'] == 24) {
											$splitted_selected_time = explode(':', $view_data['location_settings']['opening_hours'][$day_id][$first_second . '_' . $open_close . '_at']);
										}
									}
								?>
									<td>
										<select
											id="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_<?php echo esc_attr($day_id); ?>"
											name="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_<?php echo esc_attr($day_id); ?>"
										>
											<?php
											foreach ($view_data['opening_hours']['hours'] as $hour) {
												$selected = ($hour == $splitted_selected_time[0]) ? ' selected' : '';
												echo '<option value="' . esc_attr($hour) . '"' . $selected .'>' . esc_html($hour) . '</option>';
											}
											?>
										</select> :
										<select
											id="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_<?php echo esc_attr($day_id); ?>"
											name="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_<?php echo esc_attr($day_id); ?>"
										>
											<?php
											foreach ($view_data['opening_hours']['minutes'] as $minute) {
												$selected = ($minute == $splitted_selected_time[1]) ? ' selected' : '';
												$minute = ($minute == '0') ? '00' : $minute;
												echo '<option value="' . esc_attr($minute) . '"' . $selected .'>' . esc_html($minute) . '</option>';
											}
											?>
										</select>
										<?php
										if ($view_data['opening_hours']['format'] == 12) :
										?>
											<select
												id="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_<?php echo esc_attr($day_id); ?>"
												name="lbc_<?php echo esc_attr($first_second); ?>_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_<?php echo esc_attr($day_id); ?>"
											>
												<?php
												foreach ($view_data['opening_hours']['am_pms'] as $am_pm) {
													$selected = ($am_pm == $splitted_selected_time[2]) ? ' selected' : '';
													echo '<option value="' . esc_attr($am_pm) . '"' . $selected .'>' . esc_html($am_pm) . '</option>';
												}
												?>
											</select>
										<?php
										endif;
										?>
									</td>
								<?php
								endforeach;
							endforeach;
							?>
						</tr>
					<?php
					endforeach;
					?>
				</tbody>
			</table>
			<div class="lbc-setting-desc">
				<?php
				echo esc_html(__("If the 'Open?' checkbox is not ticked for a set of opening hours then that set of opening hours won't be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("If any of the time options (hour, minute, am/pm*) are not selected for an Opening/Closing time then that Opening/Closing time (for that day) won't be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("* am/pm options are only available if '12 hours format' is selected on the 'Settings' tab.", 'lbc-local-seo'));
				?>
			</div>
		</div>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Special opening hours', 'lbc-local-seo'); ?></h3>
		<div class="lbc-form-panel">
			<label for="lbc_show_on_location_page_spec_opening_hours"><?php echo esc_html(__('Show special opening hours on location page: ')); ?></label>
			<input
				type="checkbox"
				id="lbc_show_on_location_page_spec_opening_hours"
				name="lbc_show_on_location_page_spec_opening_hours"
				value="0"
				<?php echo ($view_data['location_settings']['on_location_page_spec_opening_hours'] == 1) ? 'checked' : ''; ?>
			>
			<table class="lbc-opening-hours">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th colspan="3"><?php echo esc_html(__('1st set of opening hours', 'lbc-local-seo')); ?></th>
						<th>&nbsp;</th>
						<th colspan="3"><?php echo esc_html(__('2nd set of opening hours', 'lbc-local-seo')); ?></th>
					</tr>
					<tr>
						<th><?php echo esc_html(__('Title', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Date', 'lbc-local-seo')); ?><br/><span class="lbc-th-desc">(yyyy-mm-dd)</span></th>
						<th><?php echo esc_html(__('Closed', 'lbc-local-seo')) . '<br/>' . esc_html(__('all day?', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Open?', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Opens at', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Closes at', 'lbc-local-seo')); ?></th>
						<th>&nbsp;</th>
						<th><?php echo esc_html(__('Open?', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Opens at', 'lbc-local-seo')); ?></th>
						<th><?php echo esc_html(__('Closes at', 'lbc-local-seo')); ?></th>
					</tr>
				</thead>
				<tbody id="lbc_special_opening_hours_wrapper">
					<?php
					$spec_oh_ndx = 0;
					foreach ($view_data['location_settings']['spec_opening_hours'] as $date => $spec_oh_data) :
					?>
						<tr>
							<td>
								<input
									type="text"
									id="lbc_spec_opening_hours_title_<?php echo esc_attr($spec_oh_ndx); ?>"
									name="lbc_spec_opening_hours_title_<?php echo esc_attr($spec_oh_ndx); ?>"
									class="lbc-spec-opening-hours-title"
									value="<?php echo (isset($spec_oh_data['title'])) ? esc_attr($spec_oh_data['title']) : ''; ?>"
								/>
							</td>
							<td>
								<input
									type="text"
									id="lbc_spec_opening_hours_date_<?php echo esc_attr($spec_oh_ndx); ?>"
									name="lbc_spec_opening_hours_date_<?php echo esc_attr($spec_oh_ndx); ?>"
									class="lbc-date"
									value="<?php echo esc_attr($date); ?>"
								/>
							</td>
							<td class="lbc-checkbox-cell">
								<input
									type="checkbox"
									id="lbc_spec_opening_hours_close_all_day_<?php echo esc_attr($spec_oh_ndx); ?>"
									name="lbc_spec_opening_hours_close_all_day_<?php echo esc_attr($spec_oh_ndx); ?>"
									value="1"
									<?php echo (isset($spec_oh_data['closed_all_day']) && $spec_oh_data['closed_all_day'] == 1) ? 'checked' : ''; ?>
								/>
							</td>
							<?php
							foreach (array('1st', '2nd') as $first_second) :
								if ($first_second == '2nd') :
							?>
									<td>&nbsp;</td>
								<?php
								endif;
								?>
								<td class="lbc-checkbox-cell">
									<input
										type="checkbox"
										id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_open_<?php echo esc_attr($spec_oh_ndx); ?>"
										name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_open_<?php echo esc_attr($spec_oh_ndx); ?>"
										value="<?php echo esc_attr($spec_oh_ndx); ?>"
										<?php echo (isset($spec_oh_data[$first_second . '_open']) && $spec_oh_data[$first_second . '_open'] == 1) ? 'checked' : ''; ?>
									/>
								</td>
								<?php
								foreach (array('opens', 'closes') as $open_close) :
									$splitted_selected_time = array('', '', '');
									if (isset($spec_oh_data[$first_second . '_' . $open_close . '_at'])) {
										if ($view_data['settings']['opening_hours_format'] == 12 &&
											($ts = strtotime($spec_oh_data[$first_second . '_' . $open_close . '_at'])) != false)
										{
											$splitted_selected_time = explode('-', date('g-i-a', $ts));
										}
										elseif ($view_data['settings']['opening_hours_format'] == 24) {
											$splitted_selected_time = explode(':', $spec_oh_data[$first_second . '_' . $open_close . '_at']);
										}
									}
								?>
									<td>
										<select
											id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_<?php echo esc_attr($spec_oh_ndx); ?>"
											name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_<?php echo esc_attr($spec_oh_ndx); ?>"
										>
											<?php
											foreach ($view_data['opening_hours']['hours'] as $hour) {
												$selected = ($hour == $splitted_selected_time[0]) ? ' selected' : '';
												echo '<option value="' . esc_attr($hour) . '"' . $selected . '>' . esc_html($hour) . '</option>';
											}
											?>
										</select> :
										<select
											id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_<?php echo esc_attr($spec_oh_ndx); ?>"
											name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_<?php echo esc_attr($spec_oh_ndx); ?>"
										>
											<?php
											foreach ($view_data['opening_hours']['minutes'] as $minute) {
												$selected = ($minute == $splitted_selected_time[1]) ? ' selected' : '';
												$minute = ($minute == '0') ? '00' : $minute;
												echo '<option value="' . esc_attr($minute) . '"' . $selected . '>' . esc_html($minute) . '</option>';
											}
											?>
										</select>
										<?php
										if ($view_data['opening_hours']['format'] == 12) :
										?>
											<select
												id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_<?php echo esc_attr($spec_oh_ndx); ?>"
												name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_<?php echo esc_attr($spec_oh_ndx); ?>"
											>
												<?php
												foreach ($view_data['opening_hours']['am_pms'] as $am_pm) {
													$selected = ($am_pm == $splitted_selected_time[2]) ? ' selected' : '';
													echo '<option value="' . esc_attr($am_pm) . '"' . $selected . '>' . esc_html($am_pm) . '</option>';
												}
												?>
											</select>
										<?php
										endif;
										?>
									</td>
								<?php
								endforeach;
							endforeach;
							?>
						</tr>
					<?php
						++$spec_oh_ndx;
					endforeach;
					?>
				</tbody>
			</table>
			<input type="button" value="<?php echo esc_html(__('Add new special opening hours', 'lbc-local-seo')); ?>" class="button lbc-action-button" id="lbc_spec_opening_hours_add_new">
			<div class="lbc-setting-desc">
				<?php
				echo esc_html(__("The 'Date' is mandatory! The rows of special opening hours without 'Date' won't be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("'Date' format: ", 'lbc-local-seo') . 'yyyy-mm-dd ' . __("(e.g.: 2014-05-22)", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("If the 'Closed all day?' checkbox is ticked then none of the set of opening hours will be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("If the 'Open?' checkbox is not ticked for a set of opening hours then that set of opening hours won't be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("If any of the time options (hour, minute, am/pm*) are not selected for an Opening/Closing time then that Opening/Closing time (for that day) won't be saved.", 'lbc-local-seo')) .
					'<br/>' .
					esc_html(__("* am/pm options are only available if '12 hours format' is selected on the 'Settings' tab.", 'lbc-local-seo'));
				?>
			</div>
			<table class="lbc-hidden">
				<tbody id="lbc_special_opening_hours_new_row">
					<tr>
						<td>
							<input type="hidden" id="lbc_spec_opening_hours_ndx" value="<?php echo esc_attr($spec_oh_ndx); ?>" />
							<input type="text" id="lbc_spec_opening_hours_title_" name="lbc_spec_opening_hours_title_" class="lbc-spec-opening-hours-title" value="" />
						</td>
						<td>
							<input type="text" id="lbc_spec_opening_hours_date_" name="lbc_spec_opening_hours_date_" class="lbc-date" value="" />
						</td>
						<td class="lbc-checkbox-cell">
							<input type="checkbox" id="lbc_spec_opening_hours_close_all_day_" name="lbc_spec_opening_hours_close_all_day_" value="1" />
						</td>
						<?php
						foreach (array('1st', '2nd') as $first_second) :
							if ($first_second == '2nd') :
						?>
							<td>&nbsp;</td>
							<?php
							endif;
							?>
							<td class="lbc-checkbox-cell">
								<input
									type="checkbox"
									id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_open_"
									name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_open_"
									value=""
								/>
							</td>
							<?php
							foreach (array('opens', 'closes') as $open_close) :
							?>
								<td>
									<select
										id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_"
										name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_hour_"
									>
										<?php
										foreach ($view_data['opening_hours']['hours'] as $hour) {
											echo '<option value="' . esc_attr($hour) . '">' . esc_html($hour) . '</option>';
										}
										?>
									</select> :
									<select
										id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_"
										name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_minute_"
									>
										<?php
										foreach ($view_data['opening_hours']['minutes'] as $minute) {
											$minute = ($minute == '0') ? '00' : $minute;
											echo '<option value="' . esc_attr($minute) . '">' . esc_html($minute) . '</option>';
										}
										?>
									</select>
									<?php
									if ($view_data['opening_hours']['format'] == 12) :
									?>
										<select
											id="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_"
											name="lbc_<?php echo esc_attr($first_second); ?>_spec_opening_hours_<?php echo esc_attr($open_close); ?>_at_am_pm_"
										>
											<?php
											foreach ($view_data['opening_hours']['am_pms'] as $am_pm) {
												echo '<option value="' . esc_attr($am_pm) . '">' . esc_html($am_pm) . '</option>';
											}
											?>
										</select>
									<?php
									endif;
									?>
								</td>
							<?php
							endforeach;
						endforeach;
						?>
					</tr>
				</tbody>
			</table>
		</div>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
		<h3 class="title"><?php echo esc_html('Social media links', 'lbc-local-seo'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<td colspan="2">
						<label for="lbc_show_on_location_page_social_links"><?php echo esc_html(__('Show social media links on location page: ')); ?></label>
						<input
							type="checkbox"
							id="lbc_show_on_location_page_social_links"
							name="lbc_show_on_location_page_social_links"
							value="0"
							<?php echo ($view_data['location_settings']['on_location_page_social_links'] == 1) ? 'checked' : ''; ?>
						>
					</td>
				</tr>
				<?php
				foreach ($view_data['social_icons'] as $social_icon_id => $social_icon_data) :
					$social_link = '';
					if (isset($view_data['location_settings']['social_link_' . $social_icon_id])) {
						$social_link = $view_data['location_settings']['social_link_' . $social_icon_id];
					}
				?>
					<tr>
						<th scope="row">
							<span
								title="<?php echo esc_html($social_icon_data['title']); ?>"
								class="lbc-social-icon lbc-social-icon-<?php echo esc_attr($social_icon_data['icon']); ?>"
							></span>
							<label for="lbc_var_local_seo_social_link_<?php echo esc_attr($social_icon_id); ?>" class="lbc-social-icon-label">
								<?php echo esc_html($social_icon_data['title']); ?>:
							</label>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php echo esc_html($social_icon_data['title']); ?></span></legend>
								<input
									type="text"
									id="lbc_var_local_seo_social_link_<?php echo esc_attr($social_icon_id); ?>"
									name="lbc_var_local_seo_social_link_<?php echo esc_attr($social_icon_id); ?>"
									class="lbc-input-medium"
									value="<?php echo esc_url($social_link); ?>"
								/>
							</fieldset>
						</td>
					</tr>
				<?php
				endforeach;
				?>
				<tr>
					<td colspan="2">
						<div class="lbc-setting-desc"><?php echo esc_html(__("If you don't have a social media link leave the field next to it empty. Social icons without a link will not be displayed.", 'lbc-local-seo')); ?></div>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<input type="button" value="<?php echo esc_attr(__('Save Location', 'lbc-local-seo')); ?>" class="button button-primary lbc-save-location-settings-changes-button">
			<span class="lbc-save-location-settings-changes-msg"></span>
		</p>
	</form>
	<div id="lbc_accepted_currencies_dialog_form" title="Accepted currencies" style="display: none;">
		<?php
		$lbc_accepted_currencies_arr = explode(', ', $view_data['location_settings']['accepted_currencies']);
		foreach ($view_data['currencies'] as $c_code => $c_title) :
			$lbc_checked = (in_array($c_code, $lbc_accepted_currencies_arr)) ? ' checked' : '';
		?>
			<input type="checkbox" id="lbc_accepted_currency_<?php echo esc_attr($c_code); ?>" name="lbc_accepted_currencies" value="<?php echo esc_attr($c_code); ?>"<?php echo $lbc_checked; ?> />
			&nbsp;<label for="lbc_accepted_currency_<?php echo esc_attr($c_code); ?>"><?php echo esc_html('[' . $c_code . '] ' . $c_title); ?></label>
			<br/>
		<?php
		endforeach;
		?>
	</div>
	<div class="lbc-created-by">
		<?php echo esc_html(__('Created by', 'lbc-local-seo')); ?> <a href="http://www.localbizcommando.com" target="_blank">Local Biz Commando</a>
	</div>
</div>