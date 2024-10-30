<?php
/*
Plugin Name: LBC Local SEO
Description: A simple tool that helps your local business to get more customers via the website of the business. Provides an easy way to display the address, the location on a map, the contact details, the opening hours and the special opening hours of your local business. This plugin uses special markups (microdata [schema.org], hCard) to help your website rank higher in local searches in the search engines. 
Version: 1.1.3
Author: Local Biz Commando
Author URI: http://www.localbizcommando.com
Text Domain: lbc-local-seo
License: GPLv2 or later
*/

/*
Copyright 2014   Local Biz Commando   (email: info@localbizcommando.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('LBC_LS_PLUGIN_VERSION', '1.1.3');
define('LBC_LS_ADVERT_SRC', 'http://www.localbizcommando.com/');

require_once plugin_dir_path(__FILE__) . 'includes/common.php';
require_once plugin_dir_path(__FILE__) . 'controllers/controller.php';

$lbc_controller = new LBCLocalSEOController(plugin_dir_path(__FILE__), plugins_url('', __FILE__));

register_activation_hook(__FILE__, array($lbc_controller, 'activate'));