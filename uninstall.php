<?php

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
	die('Permisssion denied!');
}

// TODO delete location page

// delete options
delete_option('lbc_local_seo_settings');
delete_option('lbc_local_seo_location_settings');