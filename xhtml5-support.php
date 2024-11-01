<?php
/*
Plugin Name: XHTML5 Support
Plugin URI: http://wordpress.org/extend/plugins/xhtml5-support/
Description: Allows templates to serve XHTML5 markup by adding the necessary JavaScript shim for IE7+ and by converting all new elements to divs and spans with appropriate class names for IE6.  <em>Plugin developed at <a href="http://www.shepherd-interactive.com/" title="Shepherd Interactive specializes in web design and development in Portland, Oregon">Shepherd Interactive</a>.</em>
Version: 0.2.4
Author: Weston Ruter
Author URI: http://weston.ruter.net/
Copyright: 2009, Weston Ruter, Shepherd Interactive <http://shepherd-interactive.com/>. GPL License.

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

add_option('xhtml_support_add_msie_shims', true);

/**
 * @global $xhtml_block_elements Array of all of the new HTML5-specific elements that are displayed as display:block
 */
$xhtml_block_elements =  array('article', 'aside', 'dialog', 'figure', 'footer', 'header', 'hgroup', 'menu', 'nav', 'rp', 'section');

/**
 * @global $xhtml_inline_elements Array of all of the new HTML5-specific elements that are displayed with display:inline
 */
$xhtml_inline_elements = array('audio', 'bb', 'canvas', 'datagrid', 'datalist', 'eventsource', 'mark', 'meter', 'output', 'progress', 'time', 'video');

/**
 * Upon initializing, turn on output buffering if using old MSIE so that shims can be added to the content
 */
function xhtml5support(){
	if(is_admin() || is_feed() || strpos($_SERVER['REQUEST_URI'], '/wp-') === 0)
		return;
	
	if(preg_match("{MSIE [1-8]\D}", $_SERVER['HTTP_USER_AGENT']))
		ob_start("xhtml5support_add_shims");
}
add_action('init', 'xhtml5support');

/**
 * Filter the MIME type to be XHTML if requested
 */
function xhtml5support_filter_html_type($mime){
	if(is_admin() || is_feed() || strpos($_SERVER['REQUEST_URI'], '/wp-') === 0)
		return $mime;

	if(@$_GET['http-content-type'])
		return $_GET['http-content-type'];
	else if(empty($_SERVER['HTTP_ACCEPT']))
		return $mime;
	else if($_SERVER['HTTP_ACCEPT'] == 'text/xml')
		return 'text/xml';
	else if(strpos($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator/') !== false)
		return 'application/xhtml+xml';
	else if(strpos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') !== false) // || preg_match("{MSIE (8|9|1\d+)}", $_SERVER["HTTP_USER_AGENT"])
		return 'application/xhtml+xml';
	#else if(strpos($_SERVER['HTTP_ACCEPT'], 'application/xml') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
	return $mime;
}
add_filter('option_html_type', 'xhtml5support_filter_html_type');

/**
 * Callback function for preg_replace_callback() called in output buffer callback {@see xhtml5support_add_shims}
 *
 * @todo We should replace block-level HTML5 elements with DIVs, but inline elements with SPANs.
 */
function xhtml5support_regex_callback($matches){
	global $xhtml_block_elements, $xhtml_inline_elements;
	
	if(in_array($matches[2], $xhtml_inline_elements))
		$elName = 'span';
	else
		$elName = 'div';
	
	if($matches[1])
		return "</$elName";
	else if($matches[3])
		return "<$elName$matches[3]html5-$matches[2] ";	
	else
		return "<$elName class='html5-$matches[2]'";
}

/**
 * Output buffering callback function. Adds shims for IE7 and transforms content for IE6.
 */
function xhtml5support_add_shims($src){
	global $xhtml_block_elements, $xhtml_inline_elements;
	$elsRegEx = join('|', array_merge($xhtml_block_elements, $xhtml_inline_elements));
	
	#We can't ever add shims right now because IE doesn't seem to handle HTML5 elements when printing
	#Add shims for IE7 and IE8
	if(get_option('xhtml_support_add_msie_shims') && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8') !== false)){
		$script = "<script type='text/javascript'>\n";
		$script .= "//IE7 hack so it will support HTML5 elements, see also Remy Sharp's http://remysharp.com/downloads/html5.js\n";
		$script .= "(function(){var c = document.createElement;\n";
		foreach(split('\|', $elsRegEx) as $el)
			$script .= "c('$el');";
		$script .= "\n})();\n</script>";
		return str_replace('</title>', "</title>\n$script", $src);
	}
	#Replace HTML5 elements with DIVs and class names that contain 'html5-elementName'
	else
		return preg_replace_callback("{<(/)?($elsRegEx)([^>]+class=['\"])?}", "xhtml5support_regex_callback", $src);
}
