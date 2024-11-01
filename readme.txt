=== XHTML5 Support ===
Contributors: westonruter
Tags: xhtml, html5, shim
Tested up to: 2.9
Requires at least: 2.7
Stable tag: trunk

Allows templates to serve XHTML5 markup by adding the necessary JavaScript shim
for IE7+ and by converting all new elements to divs and spans with appropriate
class names for IE6.

== Description ==

<em>This plugin is developed at
<a href="http://www.shepherd-interactive.com/" title="Shepherd Interactive specializes in web design and development in Portland, Oregon">Shepherd Interactive</a>
for the benefit of the community. <b>No support is available. Please post any questions to the <a href="http://wordpress.org/tags/xhtml5-support?forum_id=10">support forum</a>.</b></em>

Allows templates to serve XHTML5 markup by adding the necessary JavaScript shim
for IE7+, and for IE6 by converting all new elements to <code>div</code>s and <code>span</code>s
with class names that contain the original
HTML5 element name prefixed by "<code>html5-</code>", for example: `<article>` becomes <code>&lt;div class="html5-article"&gt;</code>
and `<time>` becomes <code>&lt;span class="html5-time"&gt;</code>

If the browser supports XHTML, the content is served with the <code>application/xhtml+xml</code>
MIME type. This can be disabled by placing <code>remove_filter('option_html_type', 'xhtml5support_filter_html_type')</code>
in <code>functions.php</code>.

Note that print stylesheets do not get applied to IE7+ even with the shim, so if you are
relying on the ability for IE users to print properly-styled pages, you must force
the IE6 renaming behavior for all versions of IE by doing <code>update_option('xhtml_support_add_msie_shims', false);</code>

== Changelog ==

= 2010-02-12: 0.2.4 =
* Removed `pre` from the list of new HTML5 block elements

= 2010-01-22: 0.2.3 =
* Preventing behavior when at a "/wp-*" url
* Preventing `xhtml5support_filter_html_type` if `empty($_SERVER['HTTP_ACCEPT'])`

= 2009-09-28: 0.2.2 =
* Initial release