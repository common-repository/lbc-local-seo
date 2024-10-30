jQuery(document).ready(function($) {
	if ($('#lbc_map_canvas').length > 0) {
		lbc_map_initialize();
	}
});

function lbc_map_initialize() {
	var lat_lng = new google.maps.LatLng(
		location_settings_object.latitude,
		location_settings_object.longitude
	);
	var mapOptions = {
		zoom: 13,
		center: lat_lng,
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		}
	}
	var map = new google.maps.Map(document.getElementById('lbc_map_canvas'), mapOptions);
	
	var marker = new google.maps.Marker({
		'map': map,
		'position': lat_lng,
		'title': location_settings_object.title
	});
	
	google.maps.event.trigger(map, 'resize');
}