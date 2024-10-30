<?php

// integer
define('LBC_LS_META_DESC_MAX_LEN', 155);

/**
 * Common functions and variables.
 * 
 * @since 1.0
 * 
 * @author Local Biz Commando
 */
class LBCLocalSEOCommon
{
	/**
	 * Root path of the plugin.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private static $_plg_root_path = '';
	
	/**
	 * Root path of the URL.
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private static $_plg_root_url = '';
	
	
	/**
	 * Returns current URL using WP functions.
	 * 
	 * @since 1.0
	 */
	protected function _get_current_url()
	{
		global $wp;
		
		return add_query_arg($wp->query_string, '', home_url($wp->request));
	}
	
	/**
	 * Sets plugin root path.
	 * 
	 * @param string $plg_root_path Plugin root path.
	 * 
	 * @since 1.0
	 */
	protected function _set_plg_root_path($plg_root_path)
	{
		self::$_plg_root_path = trailingslashit($plg_root_path);
	}
	
	/**
	 * Sets plugin root URL.
	 *
	 * @param string $plg_root_url Plugin root URL.
	 *
	 * @since 1.0.1
	 */
	protected function _set_plg_root_url($plg_root_url)
	{
		self::$_plg_root_url = trailingslashit($plg_root_url);
	}
	
	/**
	 * Returns plugin root path.
	 * 
	 * @since 1.0
	 *
	 * @return string Plugin root path.
	 */
	protected function _get_plg_root_path()
	{
		return self::$_plg_root_path;
	}
	
	/**
	 * Returns plugin root URL.
	 *
	 * @since 1.0.1
	 *
	 * @return string Plugin root URL.
	 */
	protected function _get_plg_root_url()
	{
		return self::$_plg_root_url;
	}
	
	/**
	 * Returns a link (HTML code) with the link and link text.
	 *
	 * @param string $link
	 * @param string $link_text
	 * 
	 * @since 1.0
	 *
	 * @return string Link (HTML code).
	 */
	private function _get_link_html($link, $link_text)
	{
		return '<a href="' . esc_attr($link) . '">' . esc_html($link_text) . '</a>';
	}
	
	/**
	 * Terminates the process.
	 * 
	 * @param string $err_msg Error message.
	 * @param string $back_link Back link URL.
	 * @param string $back_link_text Back link text.
	 * 
	 * @since 1.0
	 */
	protected function _die($err_msg, $back_link = '', $back_link_text = '')
	{
		wp_die(
			$err_msg .
			(($back_link != '' && $back_link_text != '') ? ' ' . $this->_get_link_html($back_link, $back_link_text) : '')
		);
	}
	
	/**
	 * Returns if a url is accessible or not.
	 * 
	 * @param string $url
	 * 
	 * @since 1.0
	 * 
	 * @return boolean If the given url is accessible then true, otherwise false.
	 */
	protected function _is_url_accessible($url)
	{
		$header = @get_headers($url);
		if (is_array($header)) {
			return ($header[0] == 'HTTP/1.1 200 OK');
		}
		
		return false;
	}
	
	/**
	 * Checks if a date is vaild (ISO8601, YYYY-MM-DD).
	 *
	 * @param string $date Format: "YYYY-MM-DD".
	 * 
	 * @since 1.0
	 *
	 * @return boolean True if valid, otherwise false.
	 */
	protected function _is_valid_date_iso8601($date)
	{
		if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
			return false;
		}
	
		$y = (int)substr($date, 0, 4);
		$m = (int)substr($date, 5, 2);
		$d = (int)substr($date, 8, 2);
	
		return checkdate($m, $d, $y);
	}
	
	/**
	 * Converts a multi-line string into single line string and returns the result.
	 *
	 * @param string $str
	 *
	 * @since 1.1
	 *
	 * @return string Single line string.
	 */
	protected function _convert_to_single_line($str)
	{
		$str = str_replace(array("\r", "\n"), ' ', $str);
	
		return trim(preg_replace('/[ ]{2,}/', ' ', $str));
	}
	
	/**
	 * Shortens the given string. The result will be shorter or equal to the given $max_num_of_chars. If the last word would be cut in half then the whole last word will be removed.
	 *
	 * @param string $str
	 * @param int $max_num_of_chars
	 *
	 * @since 1.1
	 *
	 * @return string Shortened string.
	 */
	protected function _shorten_str($str, $max_num_of_chars)
	{
		$str = trim($str);
	
		if (strlen($str) <= $max_num_of_chars) {
			return $str;
		}
	
		$str = substr($str, 0, $max_num_of_chars);
	
		return trim(substr($str, 0, strrpos($str, ' ')));
	}
}