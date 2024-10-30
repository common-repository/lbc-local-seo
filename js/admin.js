var lbc_settings_ajax_interval, lbc_location_settings_ajax_interval, lbc_file_frame, lbc_geocoder, lbc_map, lbc_default_lat_lng, lbc_marker;
var lbc_datepicker_settings = {
	'dateFormat': 'yy-mm-dd',
	'firstDay': 1
};

jQuery(document).ready(function($) {
	$('.lbc-save-settings-changes-button').click(function() {
		lbc_save_ajax(
			'settings',
			settings_ajax_object,
			'lbc_local_seo_settings_form',
			'lbc-save-settings-changes-button',
			'lbc-save-settings-changes-msg'
		);
	});
	
	$('.lbc-save-location-settings-changes-button').click(function() {
		var spec_vars = {
			'opening_hours': lbc_get_opening_hours_data(),
			'spec_opening_hours': lbc_get_spec_opening_hours_data(),
			'show_on_location_page': lbc_get_show_on_location_page_data()
		};
		lbc_save_ajax(
			'location-settings',
			location_settings_ajax_object,
			'lbc_local_seo_location_settings_form',
			'lbc-save-location-settings-changes-button',
			'lbc-save-location-settings-changes-msg',
			spec_vars
		);
	});
	
	$('#lbc_var_local_seo_business_meta_desc').keyup(function() {
		lbc_update_char_countdown(this, 'local_seo_business_meta_desc_char_countdown', location_settings_ajax_object.meta_desc_max_len);
	});
	
	$('.lbc-media-upload-button').click(function(event) {
		event.preventDefault();
		
		// If the media frame already exists, reopen it.
		if (lbc_file_frame) {
			lbc_file_frame.open();
			return;
		}
		  
		// Create the media frame.
		lbc_file_frame = wp.media.frames.lbc_file_frame = wp.media({
			'title': $(this).data('uploader_title'),
			'button': {
				'text': $(this).data('uploader_button_text')
			},
			'multiple': false // Set to true to allow multiple files to be selected
		});
		
		// When an image is selected, run a callback.
		lbc_file_frame.on('select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = lbc_file_frame.state().get('selection').first().toJSON();
			
			// Do something with attachment.id and/or attachment.url here
			if (attachment.url) {
				$('#lbc_var_local_seo_business_logo').val(attachment.url);
				$('#lbc_img_local_seo_business_logo').attr('src', attachment.url);
				$('#lbc_img_local_seo_business_logo_wrapper').removeAttr('style');
			}
		});
		
		// Finally, open the modal
		lbc_file_frame.open();
	});
	
	$('#lbc_button_remove_business_logo').click(function() {
		$('#lbc_var_local_seo_business_logo').val('');
		$('#lbc_img_local_seo_business_logo').attr('src', '');
		$('#lbc_img_local_seo_business_logo_wrapper').css('display', 'none');
	});
	
	if ($('#lbc_map_canvas').length > 0) {
		lbc_map_initialize();
		
		lbc_code_address();
		
		$('#lbc_map_get_coordinates').click(function() {
			if (lbc_marker != undefined) {
				lbc_marker.setMap(null);
			}
			lbc_code_address(true);
		});
	}
	
	$('#lbc_phone_numbers_add_new').click(function() {
		lbc_add_new_number_field('phone');
	});
	
	$('#lbc_fax_numbers_add_new').click(function() {
		lbc_add_new_number_field('fax');
	});
	
	$('#lbc_accepted_currencies_dialog_form').dialog({
		'autoOpen': false,
		'height': 400,
		'width': '60%',
		'modal': true,
		'draggable': false,
		'buttons': {
			'Set accepted currencies': function() {
				var lbc_accepted_currencies_str = '';
				$('input[name=lbc_accepted_currencies]:checked').each(function() {
					if (lbc_accepted_currencies_str.length > 0) {
						lbc_accepted_currencies_str += ', ';
					}
					lbc_accepted_currencies_str += $(this).val();
				});
				$('#lbc_var_local_seo_accepted_currencies_str').val(lbc_accepted_currencies_str);
				
				$(this).dialog('close');
			},
			'Cancel': function() {
				$(this).dialog('close');
			}
		}
	});
	
	$('#lbc_var_local_seo_accepted_currencies_str').click(function() {
		lbc_open_accepted_currencies_dialog();
	});
	$('#lbc_currencies_dialog').click(function() {
		lbc_open_accepted_currencies_dialog();
	});
	
	$('#lbc_1st_opening_hours_all_none_open').click(function() {
		lbc_opening_hours_all_none_open('1st');
	});
	$('#lbc_2nd_opening_hours_all_none_open').click(function() {
		lbc_opening_hours_all_none_open('2nd');
	});
	
	$('#lbc_spec_opening_hours_add_new').click(function() {
		lbc_add_new_spec_opening_hours();
	});
	
	$('#lbc_special_opening_hours_wrapper .lbc-date').datepicker(lbc_datepicker_settings);
});

function lbc_save_ajax(type, ajax_object, form_id, button_class, msg_class, spec_vars)
{
	jQuery.ajax({
		'type': 'POST',
		'url': ajax_object.ajax_url,
		'dataType': 'json',
		'data': {
			'action': ajax_object.action,
			'nonce': ajax_object.nonce,
			'vars': jQuery('input[name^=lbc_var_], select[name^=lbc_var_], textarea[name^=lbc_var_]', jQuery('#' + form_id)).serialize(),
			'spec_vars': spec_vars
		},
		'beforeSend': function() {
			lbc_hide_ajax_msg(type, msg_class);
			jQuery('.' + button_class).attr('disabled', true);
			jQuery('.' + msg_class).html(ajax_object.texts.save_in_progress);
		},
		'complete': function() {
			jQuery('.' + button_class).attr('disabled', false);
		},
		'success': function(data) {
			if (data == 1) {
				if (jQuery('#lbc_original_location_page_slug').val() != jQuery('#lbc_var_local_seo_location_page_slug').val()) {
					jQuery('.' + msg_class).html(ajax_object.texts.success + ' ' + ajax_object.texts.reload);
					location.reload();
				}
				else {
					jQuery('.' + button_class).attr('disabled', false);
					jQuery('.' + msg_class).html(ajax_object.texts.success);
					lbc_set_ajax_msg_interval(type, 10000);
				}
			}
			else {
				jQuery('.' + msg_class).html(ajax_object.texts.server_error);
				lbc_set_ajax_msg_interval(type, 30000);
			}
		},
		'error': function() {
			jQuery('.' + msg_class).html(ajax_object.texts.ajax_error);
			lbc_set_ajax_msg_interval(type, 30000);
		}
	});
}

function lbc_set_ajax_msg_interval(type, interval)
{
	switch (type) {
		case 'settings':
			lbc_settings_ajax_interval = setInterval(function() {
				lbc_hide_ajax_msg(type, '.lbc-save-settings-changes-msg');
			}, interval);
		break;
		
		case 'location-settings':
			lbc_location_settings_ajax_interval = setInterval(function() {
				lbc_hide_ajax_msg(type, '.lbc-save-location-settings-changes-msg');
			}, interval);
		break;
	}
}

function lbc_hide_ajax_msg(type, msg_class)
{
	jQuery(msg_class).html('');
	
	switch (type) {
		case 'settings':
			clearInterval(lbc_settings_ajax_interval);
		break;
		
		case 'location-settings':
			clearInterval(lbc_location_settings_ajax_interval);
		break;
	}
}

function lbc_map_initialize() {
	lbc_geocoder = new google.maps.Geocoder();
	lbc_default_lat_lng = new google.maps.LatLng(51.5238100, -0.1584410);
	var mapOptions = {
		zoom: 1,
		center: lbc_default_lat_lng,
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		}
	}
	lbc_map = new google.maps.Map(document.getElementById('lbc_map_canvas'), mapOptions);
}

function lbc_code_address(force_refresh_based_on_address) {
    google.maps.event.trigger(lbc_map, 'resize');
    
    if (force_refresh_based_on_address != true &&
    	jQuery('#lbc_var_local_seo_business_latitude').val() != '' &&
		jQuery('#lbc_var_local_seo_business_longitude').val() != '')
    {
    	var lat_lng = new google.maps.LatLng(
    		jQuery('#lbc_var_local_seo_business_latitude').val(),
    		jQuery('#lbc_var_local_seo_business_longitude').val()
    	);
    	
    	lbc_set_marker_on_the_map(lat_lng, 13);
    }
    else {
	    var address = lbc_get_address_for_geocoding();
		if (address != '') {
			lbc_geocoder.geocode({'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					lbc_set_marker_on_the_map(results[0].geometry.location, 13);
					
					jQuery('#lbc_var_local_seo_business_latitude').val(results[0].geometry.location.lat());
			    	jQuery('#lbc_var_local_seo_business_longitude').val(results[0].geometry.location.lng());
				}
				else {
					alert('Geocode was not successful for the following reason: ' + status);
				}
			});
		}
		else {
			lbc_map.setCenter(lbc_default_lat_lng);
			lbc_map.setZoom(lbc_map.getZoom());
		}
    }
}

function lbc_get_address_for_geocoding()
{
	var address = '';
	
	if (jQuery('#lbc_var_local_seo_business_street_address').val() != '' &&
		jQuery('#lbc_var_local_seo_business_postcode').val() != '')
	{
		address += jQuery('#lbc_var_local_seo_business_street_address').val();
		
		if (jQuery('#lbc_var_local_seo_business_city').val() != '') {
			address += ', ' + jQuery('#lbc_var_local_seo_business_city').val();
		}
		
		if (jQuery('#lbc_var_local_seo_business_state').val() != '') {
			address += ', ' + jQuery('#lbc_var_local_seo_business_state').val();
		}
		
		address += ', ' + jQuery('#lbc_var_local_seo_business_postcode').val();
		
		if (jQuery('#lbc_var_local_seo_business_country').val() != '') {
			address += ', ' + jQuery('#lbc_var_local_seo_business_country').val();
		}
	}
	
	return address;
}

function lbc_set_marker_on_the_map(location, zoom)
{
	lbc_map.setCenter(location);
	lbc_map.setZoom(zoom);
	lbc_marker = new google.maps.Marker({
		'map': lbc_map,
		'position': location,
		'title': jQuery('#lbc_var_local_seo_business_name').val(),
		'draggable': true
	});
	
	google.maps.event.addListener(lbc_marker, 'dragend', function (event) {
		jQuery('#lbc_var_local_seo_business_latitude').val(this.getPosition().lat());
		jQuery('#lbc_var_local_seo_business_longitude').val(this.getPosition().lng());
	});
}

function lbc_add_new_number_field(type)
{
	var id_num = jQuery('input[id^=lbc_var_local_seo_business_' + type + '_]').length;
	
	jQuery('#lbc_' + type + '_numbers_add_new').addClass('lbc-button-add-new');
	
	jQuery('#lbc_business_' + type + '_numbers_wrapper').append(
		'<input ' +
			'type="text" ' +
			'id="lbc_var_local_seo_business_' + type + '_' + id_num + '" ' +
			'name="lbc_var_local_seo_business_' + type + '_numbers[]" ' +
			'value="" ' +
		'/><br/>'
	);
	
	jQuery('#lbc_var_local_seo_business_' + type + '_' + id_num).focus();
}

function lbc_open_accepted_currencies_dialog()
{
	jQuery('input[name=lbc_accepted_currencies]:checked').each(function() {
		jQuery(this).prop('checked', false);
	});
	
	var ticked_currencies = jQuery('#lbc_var_local_seo_accepted_currencies_str').val().split(', ');
	jQuery(ticked_currencies).each(function(ndx, item) {
		jQuery('#lbc_accepted_currency_' + item).prop('checked', true);
	});
	
	jQuery('#lbc_accepted_currencies_dialog_form').dialog('open');
}

function lbc_opening_hours_all_none_open(type)
{
	var checked = jQuery('#lbc_' + type + '_opening_hours_all_none_open').prop('checked');
	var checked_selector = ((checked) ? ':not(' : '') + ':checked' + ((checked) ? ')' : '');
	
	jQuery('input[name^=lbc_' + type + '_opening_hours_open_]' + checked_selector).each(function() {
		jQuery(this).prop('checked', checked);
	});
}

function lbc_get_opening_hours_data()
{
	var opening_hours_data = {};
	
	jQuery(['1st', '2nd']).each(function(ndx1, first_second) {
		jQuery('input[id^=lbc_' + first_second + '_opening_hours_open_]:checked').each(function() {
			var open_obj = this;
			jQuery(['opens', 'closes']).each(function(ndx2, open_close) {
				var day = jQuery(open_obj).val();
				var at_hour = jQuery('#lbc_' + first_second + '_opening_hours_' + open_close + '_at_hour_' + day).val();
				var at_minute = jQuery('#lbc_' + first_second + '_opening_hours_' + open_close + '_at_minute_' + day).val();
				var am_pm_jq_obj = jQuery('#lbc_' + first_second + '_opening_hours_' + open_close + '_at_am_pm_' + day);
				var am_pm = (am_pm_jq_obj.length > 0) ? am_pm_jq_obj.val() : '';
				
				var save_it = (at_hour != '' && at_minute != '');
				if (am_pm_jq_obj.length > 0) {
					save_it = (save_it && am_pm != '');
				}
				
				if (save_it) {
					if (opening_hours_data[day] == undefined) {
						opening_hours_data[day] = {};
					}
					opening_hours_data[day][first_second + '_open'] = 1;
					opening_hours_data[day][first_second + '_' + open_close + '_at'] = at_hour + ':' + at_minute + am_pm;
				}
			});
		});
	});
	
	return opening_hours_data;
}

function lbc_add_new_spec_opening_hours()
{
	var dom = jQuery(jQuery('#lbc_special_opening_hours_new_row').html());
	var spec_opening_hours_ndx = parseInt(jQuery('#lbc_spec_opening_hours_ndx', dom).val());
	var spec_oh_fields = [
		'lbc_spec_opening_hours_title_',
		'lbc_spec_opening_hours_date_',
		'lbc_spec_opening_hours_close_all_day_'
	];
	jQuery(['1st', '2nd']).each(function(ndx1, first_second) {
		spec_oh_fields.push('lbc_' + first_second + '_spec_opening_hours_open_');
		
		jQuery(['opens', 'closes']).each(function(ndx2, open_close) {
			spec_oh_fields.push('lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_hour_');
			spec_oh_fields.push('lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_minute_');
			spec_oh_fields.push('lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_am_pm_');
		});
	});
	
	jQuery('#lbc_spec_opening_hours_ndx').val(spec_opening_hours_ndx + 1);
	jQuery('#lbc_spec_opening_hours_ndx', dom).remove();
	
	var element_name = '';
	jQuery(spec_oh_fields).each(function(ndx1, field_id) {
		var e_jq_obj = jQuery('#' + field_id, dom);
		if (e_jq_obj.length > 0) {
			element_name = e_jq_obj.attr('name');
			jQuery(['name', 'id']).each(function(ndx2, item) {
				e_jq_obj.attr(item, element_name + spec_opening_hours_ndx);
			});
		}
	});
	
	jQuery('#lbc_special_opening_hours_wrapper').append(dom.wrap('<tbody>').parent().html());
	
	jQuery('#lbc_spec_opening_hours_date_' + spec_opening_hours_ndx).focus(function() {
		jQuery(this).datepicker(lbc_datepicker_settings);
	});
	jQuery('#lbc_spec_opening_hours_date_' + spec_opening_hours_ndx).focus();
	jQuery('#lbc_spec_opening_hours_title_' + spec_opening_hours_ndx).focus();
}

function lbc_get_spec_opening_hours_data()
{
	var spec_opening_hours_data = {};
	
	jQuery('input[id^=lbc_spec_opening_hours_date_]').each(function() {
		if (jQuery(this).val() != '') {
			var ndx = jQuery(this).attr('id').substr(('lbc_spec_opening_hours_date_').length);
			var title = jQuery('#lbc_spec_opening_hours_title_' + ndx).val();
			var date = jQuery(this).val();
			
			if (jQuery('#lbc_spec_opening_hours_close_all_day_' + ndx + ':checked').length > 0) {
				if (spec_opening_hours_data[date] == undefined) {
					spec_opening_hours_data[date] = {};
				}
				
				if (title != '') {
					spec_opening_hours_data[date]['title'] = title;
				}
				
				spec_opening_hours_data[date]['closed_all_day'] = 1;
			}
			else {
				jQuery(['1st', '2nd']).each(function(ndx1, first_second) {
					if (jQuery('#lbc_' + first_second + '_spec_opening_hours_open_' + ndx + ':checked').length > 0) {
						jQuery(['opens', 'closes']).each(function(ndx2, open_close) {
							var at_hour = jQuery('#lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_hour_' + ndx).val();
							var at_minute = jQuery('#lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_minute_' + ndx).val();
							var am_pm_jq_obj = jQuery('#lbc_' + first_second + '_spec_opening_hours_' + open_close + '_at_am_pm_' + ndx);
							var am_pm = (am_pm_jq_obj.length > 0) ? am_pm_jq_obj.val() : '';
							
							var time = '';
							if (at_hour != '' && at_minute != '') {
								time = at_hour + ':' + at_minute;
							}
							if (time != '' && am_pm_jq_obj.length > 0 && am_pm != '') {
								time += am_pm;
							}
							
							if (time != '') {
								if (spec_opening_hours_data[date] == undefined) {
									spec_opening_hours_data[date] = {};
								}
								
								if (spec_opening_hours_data[date]['title'] == undefined && title != '') {
									spec_opening_hours_data[date]['title'] = title;
								}
								
								if (spec_opening_hours_data[date][first_second + '_open'] == undefined) {
									spec_opening_hours_data[date][first_second + '_open'] = 1;
								}
								
								spec_opening_hours_data[date][first_second + '_' + open_close + '_at'] = time;
							}
						});
					}
				});
			}
		}
	});
	
	return spec_opening_hours_data;
}

function lbc_get_show_on_location_page_data()
{
	var show_on_location_page_data = {};
	
	jQuery('input[id^=lbc_show_on_location_page_]:checked').each(function() {
		show_on_location_page_data[jQuery(this).attr('id').substr(26)] = 1;
	});

	return show_on_location_page_data;
}

function lbc_update_char_countdown(textarea_obj, countdown_id, max_count)
{
	jQuery('#' + countdown_id).html(max_count - jQuery(textarea_obj).val().length);
}