<div itemscope itemtype="http://schema.org/<?php echo esc_attr($view_data['location_settings']['type']); ?>" class="lbc-location-wrapper vcard">
	<div class="lbc-row">
		<div<?php echo ($view_data['location_settings']['on_location_page_social_links'] == 1) ? ' class="lbc-m-col-full lbc-col-half"' : ''; ?>>
			<div>
				<?php
				if ($view_data['location_settings']['logo'] != '') :
				?>
					<img
						itemprop="logo"
						class="logo"
						alt="<?php echo esc_attr($view_data['location_settings']['name'] . ' ' . __('logo', 'lbc-local-seo')); ?>"
						src="<?php echo esc_url($view_data['location_settings']['logo']); ?>"
					/>
				<?php
				endif;
				
				if ($view_data['location_settings']['name'] != '') :
				?>
					<div class="lbc-business-name-url">
						<a itemprop="url" class="url" href="<?php echo esc_attr($view_data['location_settings']['read_more_url']); ?>"><span itemprop="name" class="fn org lbc-business-name"><?php echo esc_html($view_data['location_settings']['name']); ?></span></a>
						<?php
						if ($view_data['location_settings']['url'] != '') :
						?>
							&nbsp;[<a href="<?php echo esc_url($view_data['location_settings']['url']); ?>"><?php echo esc_html(__('website', 'lbc-local-seo-pro')); ?></a>]
						<?php
						endif;
						?>
					</div>
				<?php
				endif;
				?>
			</div>
		</div>
		<div class="lbc-m-col-full lbc-col-half">
			<?php
			if ($view_data['location_settings']['on_location_page_social_links'] == 1) :
				?>
				<div class="lbc-social-icons-wrapper">
					<?php
					$c = 0;
					foreach ($view_data['social_icons'] as $social_icon_id => $social_icon_data) :
						if (isset($view_data['location_settings']['social_link_' . $social_icon_id]) &&
							$view_data['location_settings']['social_link_' . $social_icon_id] != '')
						:
							?>
							<a href="<?php echo esc_attr($view_data['location_settings']['social_link_' . $social_icon_id]); ?>">
								<span
									title="<?php echo esc_html($social_icon_data['title']); ?>"
									class="lbc-social-icon lbc-social-icon-<?php echo esc_attr($social_icon_data['icon']); ?>"
								></span>
							</a>
							<?php
							if (++$c == 8) {
								echo '<br/>';
								$c = 0;
							}
						endif;
					endforeach;
					?>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>
	<div class="lbc-row">
		<div class="lbc-m-col-full lbc-col-half">
			<?php
			if ($view_data['location_settings']['short_desc'] != '') :
			?>
				<div itemprop="description">
					<?php echo esc_html($view_data['location_settings']['short_desc']); ?>
				</div>
			<?php
			endif;
			?>
		</div>
		<div class="lbc-m-col-full lbc-col-half">
			<div itemscope itemtype="http://schema.org/PostalAddress" itemprop="address" class="adr lbc-business-addr">
				<?php
				if ($view_data['settings']['addr_format'] != '') {
					foreach ($view_data['addr_params'] as $addr_param) {
						$escaped_prefix = '';
						$escaped_postfix = '';
						if ($addr_param['name'] == 'country') {
							$escaped_prefix = $view_data['countries'][ $view_data['location_settings'][ $addr_param['name'] ] ] . ' (';
							$escaped_postfix = ')';
						}
						
						${'lbc_escaped_' . $addr_param['name']} = (esc_html($view_data['location_settings'][ $addr_param['name'] ]) != '')
						? $escaped_prefix . '<span itemprop="' . esc_attr($addr_param['itemprop']) . '" class="' . esc_attr($addr_param['h_card_class']) . '">' . esc_html($view_data['location_settings'][ $addr_param['name'] ]) . '</span>' . $escaped_postfix
						: '';
					}
				
					$lbc_business_addr = str_replace(
						array('{street address}', '{region}', '{state}', '{city}', '{postcode}', '{zipcode}', '{country}', ' / '),
						array(
							$lbc_escaped_street_address,
							$lbc_escaped_state,
							$lbc_escaped_state,
							$lbc_escaped_city,
							$lbc_escaped_postcode,
							$lbc_escaped_postcode,
							$lbc_escaped_country,
							'<br/>'
						),
						$view_data['settings']['addr_format']
					);
				
					echo preg_replace('#(<br/>){2,}#', '<br/>', $lbc_business_addr);
				}
				?>
			</div>
			<div itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates" class="geo lbc-business-coordinates">
				<div>
					<span class="lbc-row-title"> 
						<?php echo esc_html(__('Latitude', 'lbc-local-seo') . ': '); ?>
					</span>
					<span class="latitude">
						<?php echo esc_html($view_data['location_settings']['latitude']); ?>
					</span>
				</div>
				<div>
					<span class="lbc-row-title"> 
						<?php echo esc_html(__('Longitude', 'lbc-local-seo') . ': '); ?>
					</span>
					<span class="longitude">
						<?php echo esc_html($view_data['location_settings']['longitude']); ?>
					</span>
				</div>
				<meta itemprop="latitude" content="<?php echo esc_html($view_data['location_settings']['latitude']); ?>" />
				<meta itemprop="longitude" content="<?php echo esc_html($view_data['location_settings']['longitude']); ?>" />
			</div>
		</div>
	</div>
	<?php
	if ($view_data['location_settings']['on_location_page_map'] == 1) :
		?>
		<div class="lbc-row">
			<div class="lbc-m-col-full">
				<div id="lbc_map_canvas" class="lbc-map-canvas"></div>
			</div>
		</div>
		<?php
	endif;
	
	if ($view_data['location_settings']['on_location_page_contacts'] == 1 ||
		$view_data['location_settings']['on_location_page_payments'] == 1)
	:
		if ($view_data['location_settings']['on_location_page_contacts'] == 1 &&
			$view_data['location_settings']['on_location_page_payments'] == 1)
		{
			$col_class = 'lbc-m-col-full lbc-col-half';
		}
		else {
			$col_class = 'lbc-m-col-full';
		}
	?>
		<div class="lbc-row">
			<?php
			if ($view_data['location_settings']['on_location_page_contacts'] == 1) :
			?>
				<div class="<?php echo esc_attr($col_class); ?>">
					<div class="lbc-table">
						<?php
						if (count($view_data['location_settings']['phone_numbers']) > 0) :
							?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell lbc-location-title">
									<?php echo esc_html(__('Telephone: ', 'lbc-local-seo')); ?>
								</div>
								<div class="lbc-table-cell">
									<?php
									foreach ($view_data['location_settings']['phone_numbers'] as $phone_number) :
										?>
										<a itemprop="telephone" class="tel" href="tel:<?php echo esc_attr($phone_number); ?>"><?php echo esc_html($phone_number); ?></a><br/>
										<?php
									endforeach;
									?>
								</div>
							</div>
							<?php
						endif;
						
						if (count($view_data['location_settings']['fax_numbers']) > 0) :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell lbc-location-title">
									<?php echo esc_html(__('Fax: ', 'lbc-local-seo')); ?>
								</div>
								<div class="lbc-table-cell">
									<?php
									foreach ($view_data['location_settings']['fax_numbers'] as $fax_number) :
										?>
										<a itemprop="faxNumber" href="tel:<?php echo esc_attr($fax_number); ?>"><?php echo esc_html($fax_number); ?></a><br/>
										<?php
									endforeach;
									?>
								</div>
							</div>
							<?php
						endif;
						
						if ($view_data['location_settings']['email'] != '') :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell lbc-location-title">
									<?php echo esc_html(__('Email: ', 'lbc-local-seo')); ?>
								</div>
								<div class="lbc-table-cell">
									<a itemprop="email" class="email" href="mailto:<?php echo esc_attr($view_data['location_settings']['email']); ?>"><?php echo esc_html($view_data['location_settings']['email']); ?></a>
								</div>
							</div>
						<?php
						endif;
						?>
					</div>
				</div>
			<?php
			endif;
			
			if ($view_data['location_settings']['on_location_page_payments'] == 1) :
			?>
				<div class="<?php echo esc_attr($col_class); ?>">
					<div class="lbc-table">
						<?php
						if ($view_data['location_settings']['accepted_payment_type'] != '') :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell">
									<div class="lbc-location-title"><?php echo esc_html(__('Accepted payment types: ', 'lbc-local-seo')); ?></div>
									<div itemprop="paymentAccepted"><?php echo esc_html($view_data['location_settings']['accepted_payment_type']); ?></div>
								</div>
							</div>
						<?php
						endif;
						
						if ($view_data['location_settings']['accepted_currencies'] != '') :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell">
									<div class="lbc-location-title"><?php echo esc_html(__('Accepted currencies: ', 'lbc-local-seo')); ?></div>
									<div itemprop="currenciesAccepted"><?php echo esc_html($view_data['location_settings']['accepted_currencies']); ?></div>
								</div>
							</div>
						<?php
						endif;
			
						if ($view_data['location_settings']['price_range'] != '') :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell">
									<div class="lbc-location-title"><?php echo esc_html(__('Price range: ', 'lbc-local-seo')); ?></div>
									<div itemprop="priceRange"><?php echo esc_html($view_data['location_settings']['price_range']); ?></div>
								</div>
							</div>
						<?php
						endif;
						?>
					</div>
				</div>
			<?php
			endif;
			?>
		</div>
	<?php
	endif;
	
	if ($view_data['location_settings']['on_location_page_opening_hours'] == 1 ||
		$view_data['location_settings']['on_location_page_spec_opening_hours'] == 1)
		:
			if ($view_data['location_settings']['on_location_page_opening_hours'] == 1 &&
				$view_data['location_settings']['on_location_page_spec_opening_hours'] == 1)
			{
				$col_class = 'lbc-m-col-full lbc-col-half';
			}
			else {
				$col_class = 'lbc-m-col-full';
			}
	?>
		<div class="lbc-row">
			<?php
			if ($view_data['location_settings']['on_location_page_opening_hours'] == 1) :
			?>
				<div class="<?php echo esc_attr($col_class); ?>">
					<div class="lbc-location-title"><?php echo esc_html(__('Opening hours: ', 'lbc-local-seo')); ?></div>
					<div class="lbc-table">
						<?php
						foreach ($view_data['location_settings']['opening_hours'] as $day_id => $oh_data) :
						?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell lbc-row-title"><?php echo esc_html($view_data['opening_hours']['days'][$day_id]); ?>: </div>
								<?php
								foreach (array('1st', '2nd') as $first_second) :
									$time_to_echo = '';
									$time_for_schema = '';
									if (isset($oh_data[$first_second . '_open']) && $oh_data[$first_second . '_open'] == 1) :
										$time_for_schema = ucfirst(substr($day_id, 0, 2)) . ' ';
										foreach (array('opens', 'closes') as $open_close) :
											$time_to_add = '';
											if (isset($oh_data[$first_second . '_' . $open_close . '_at'])) {
												if ($view_data['settings']['opening_hours_format'] == 12 &&
													($ts = strtotime($oh_data[$first_second . '_' . $open_close . '_at'])) != false)
												{
													$splitted_oh_time = explode('-', date('g-i-a', $ts));
													$time_to_add = $splitted_oh_time[0] . ':' . $splitted_oh_time[1] . $splitted_oh_time[2];
												}
												elseif ($view_data['settings']['opening_hours_format'] == 24) {
													$time_to_add = $oh_data[$first_second . '_' . $open_close . '_at'];
												}
											}
											if ($time_to_add != '') :
												$time_to_echo .= $time_to_add;
												$time_for_schema .= $oh_data[$first_second . '_' . $open_close . '_at'];
											else :
												$time_to_echo .= '&nbsp;';
											endif;
											$time_to_echo .= ($open_close == 'opens') ? '-' : '';
											$time_for_schema .= ($open_close == 'opens') ? '-' : '';
										endforeach;
									else :
										$time_to_echo .= '&nbsp;';
									endif;
									
									if ($time_for_schema != '') :
										?>
										<time itemprop="openingHours" datetime="<?php echo esc_attr($time_for_schema); ?>" class="lbc-table-cell">
											<?php
											echo esc_html($time_to_echo);
											?>
										</time>
										<?php
									else :
										?>
										<div class="lbc-table-cell"><?php echo esc_html($time_to_echo); ?></div>
										<?php
									endif;
								endforeach;
								?>
							</div>
							<?php
						endforeach;
						?>
					</div>
				</div>
			<?php
			endif;

			if ($view_data['location_settings']['on_location_page_spec_opening_hours'] == 1 &&
				count($view_data['location_settings']['spec_opening_hours']) > 0) :
			?>
				<div class="<?php echo esc_attr($col_class); ?>">
					<div class="lbc-location-title"><?php echo esc_html(__('Special opening hours: ', 'lbc-local-seo')); ?></div>
					<div class="lbc-table">
						<?php
						foreach ($view_data['location_settings']['spec_opening_hours'] as $date => $soh_data) :
							$weekday = date('D', @strtotime($date));
							?>
							<div class="lbc-table-row">
								<div class="lbc-table-cell lbc-row-title">
									<?php
									if (isset($soh_data['title']) && $soh_data['title'] != '') :
										echo esc_html($soh_data['title']) . '<br/>(';
									endif;
									echo esc_html($date) . ', ' . $view_data['opening_hours']['days'][strtolower($weekday)];
									if (isset($soh_data['title']) && $soh_data['title'] != '') :
										echo ')';
									endif;
									?>
								</div>
								<?php
								if (isset($soh_data['closed_all_day']) && $soh_data['closed_all_day'] == 1) :
									?>
									<div class="lbc-table-row">
										<div class="lbc-table-cell"><?php echo esc_html(__('Closed all day', 'lbc-local-seo')); ?></div>
									</div>
									<?php
								else :
									foreach (array('1st', '2nd') as $first_second) :
										$time_to_echo = '';
										if (isset($soh_data[$first_second . '_open']) && $soh_data[$first_second . '_open'] == 1) :
											foreach (array('opens', 'closes') as $open_close) :
												$time_to_add = '';
												if (isset($soh_data[$first_second . '_' . $open_close . '_at'])) {
													if ($view_data['settings']['opening_hours_format'] == 12 &&
														($ts = strtotime($soh_data[$first_second . '_' . $open_close . '_at'])) != false)
													{
														$splitted_oh_time = explode('-', date('g-i-a', $ts));
														$time_to_add = $splitted_oh_time[0] . ':' . $splitted_oh_time[1] . $splitted_oh_time[2];
													}
													elseif ($view_data['settings']['opening_hours_format'] == 24) {
														$time_to_add = $soh_data[$first_second . '_' . $open_close . '_at'];
													}
												}
												if ($time_to_add != '') :
													$time_to_echo .= $time_to_add;
												else :
													$time_to_echo .= '&nbsp;';
												endif;
												$time_to_echo .= ($open_close == 'opens') ? '-' : '';
											endforeach;
										else :
											$time_to_echo .= '&nbsp;';
										endif;
											
										?>
											<div class="lbc-table-cell"><?php echo esc_html($time_to_echo); ?></div>
										<?php
									endforeach;
								endif;
								?>
							</div>
							<?php
						endforeach;
						?>
					</div>
				</div>
			<?php
			endif;
			?>
		</div>
	<?php
	endif;
	?>
	<div class="lbc-row">
		<div class="lbc-m-col-full lbc-col-full lbc-location-hatom-extra hatom-extra">
			<span class="entry-title"><?php echo esc_html($view_data['page_title']); ?></span>
			<?php echo esc_html(__('Updated', 'lbc-local-seo')); ?>:
			<span class="updated"><?php echo esc_html($view_data['location_settings']['save_date']); ?></span>
		</div>
	</div>
</div>