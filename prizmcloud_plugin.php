<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php. Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional. If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'pzc_prizm_document_viewer';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML. Not recommended.
$plugin['allow_html_help'] = 0;

$plugin['version'] = '1.0';
$plugin['author'] = 'Accusoft';
$plugin['author_uri'] = 'http://www.accusoft.com';
$plugin['description'] = 'Prizm Cloud is a document viewer that enables you to display hundreds of different kinds of files on your website without worrying about whether your visitors have the software to view them and without installing any additional hardware or software. The document files stay on your server, so you can update, edit and change them anytime. Prizm Cloud supports more than 300 file types, including DOC, PDF, PPT, XLS and CAD.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library : only when include_plugin() or require_plugin() is called
// 3 = admin : only on the admin side
$plugin['type'] = '3';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '0';

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/**
* Prizm Cloud Embedded Document Viewer v1.3
* Plugin URI: http://www.prizmcloud.com/
* Description: Prizm Cloud enables you to offer high-speed document viewing without worrying about additional hardware or installing software.  The documents stay on your servers, so you can delete, update, edit and change them anytime. We don't keep copies of your documents, so they are always secure!
* Author: Accusoft <prizmcloud@accusoft.com>
* Author URI: http://www.accusoft.com/
* Version: 1.0.1
*
* By default: Textpattern doesn't have WYSIWYG Editor (ex.: TinyMCE), means that you will be seeing
* only <iframe> tag while editing page. See results in "Article preview" or your site.
*/

// admin user only
if (@txpinterface == 'admin')
{
	add_privs('pzc_prizm_document_viewer.attach', '1,2,3');
	if (has_privs('pzc_prizm_document_viewer.attach'))
	{
		register_callback('pzc_append_button', 'article_ui', 'title');
	}
}

// Prizm Cloud Viewer Button
function pzc_append_button($event, $step, $data, $rs)
{
	$button = '<a href="javascript:pzc_show_form()" class="navlink">Prizm Cloud Document Viewer</a>';
	$output_result = pzc_script()."\r\n".$button."\r\n";
	return $data.$output_result;
}

// Prizm Cloud Form
function pzc_form()
{
	$formHTML = "<div id=\"pzc-form\" title=\"Prizm Cloud Document Viewer\">
		<table>
			<tr>
				<td align=\"right\"><strong>Key:</strong></td>
				<td valign=\"top\"><input name=\"licenseKey\" type=\"text\" id=\"licenseKey\" /></td>
			</tr>
			<tr>
				<td align=\"right\"><strong>Document URL:</strong></td>
				<td valign=\"top\"><input name=\"viewerDocument\" type=\"text\" id=\"viewerDocument\" size=\"40\" /></td>
			</tr>
			<tr>
				<td align=\"right\"><strong>Viewer Type:</strong></td>
				<td valign=\"top\">
					<input type=\"radio\" value=\"html5\" name=\"viewerType\" onclick=\"javascript:pcSettings(this.value)\" checked=\"checked\" /> <span>HTML5</span>
					<input type=\"radio\" value=\"flash\" name=\"viewerType\" onclick=\"javascript:pcSettings(this.value)\" /> <span>Flash</span>
					<input type=\"radio\" value=\"slideshow\" name=\"viewerType\" onclick=\"javascript:pcSettings(this.value)\" /> <span>Slideshow</span>
				</td>
			</tr>
			<tr>
				<td align=\"right\"><strong>Viewer Width:</strong></td>
				<td valign=\"top\"><input name=\"viewerWidth\" type=\"text\" id=\"viewerWidth\" size=\"6\" value=\"600\" />px</td>
			</tr>
			<tr>
				<td align=\"right\"><strong>Viewer Height:</strong></td>
				<td valign=\"top\"><input name=\"viewerHeight\" type=\"text\" id=\"viewerHeight\" size=\"6\" value=\"800\" />px</td>
			</tr>
		</table>
		<div id=\"documentViewer\" style=\"display: block;\">
			<table>
				<tr>
					<td align=\"right\"><strong>Print Button:</strong></td>
					<td valign=\"top\">
						<input type=\"radio\" name=\"viewerPrintButton\" value=\"Yes\" checked=\"checked\" /> <span>Yes</span>
						<input type=\"radio\" name=\"viewerPrintButton\" value=\"No\" /> <span>No</span>
					</td>
				</tr>
				<tr>
					<td align=\"right\"><strong>Toolbar Color:</strong></td>
					<td valign=\"top\"><input type=\"text\" id=\"viewerToolbarColor\" name=\"viewerToolbarColor\" value=\"#CCCCCC\" class=\"color\" /></td>
				</tr>
			</table>
		</div>
		<div id=\"slideshowViewer\" style=\"display: none;\">
			<table>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Animation Type:</strong></td>
					<td valign=\"top\">
						<select id=\"viewerAnimType\" name=\"viewerAnimType\">
						<option value=\"slide\">Slide</option>
						<option value=\"fade\">Fade</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\" valign=\"top\" class=\"gray dwl_gray\"><strong>Animation Duration:</strong></td>
					<td valign=\"top\">
						<input type=\"text\" id=\"viewerAnimDuration\" name=\"viewerAnimDuration\" value=\"450\" /><br /><em>(Note: # in milliseconds)</em>
					</td>
				</tr>
				<tr>
					<td align=\"right\" valign=\"top\" class=\"gray dwl_gray\"><strong>Animation Speed:</strong></td>
					<td valign=\"top\">
						<input type=\"text\" id=\"viewerAnimSpeed\" name=\"viewerAnimSpeed\" value=\"4000\" /><br /><em>(Note: # in milliseconds)</em>
					</td>
				</tr>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Start Automatically:</strong></td>
					<td valign=\"top\">
						<select id=\"viewerAutomatic\" name=\"viewerAutomatic\">
						<option value=\"yes\">Yes</option>
						<option value=\"no\">No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Show Controls:</strong></td>
					<td valign=\"top\">
						<select id=\"viewerShowControls\" name=\"viewerShowControls\">
						<option value=\"yes\">Yes</option>
						<option value=\"no\">No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Center Controls: (if shown)</strong></td>
					<td valign=\"top\">
						<select id=\"viewerCenterControls\" name=\"viewerCenterControls\">
						<option value=\"yes\">Yes</option>
						<option value=\"no\">No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Allow Keyboard Navigation:</strong></td>
					<td valign=\"top\">
						<select id=\"viewerKeyboardNav\" name=\"viewerKeyboardNav\">
						<option value=\"yes\">Yes</option>
						<option value=\"no\">No</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\" class=\"gray dwl_gray\"><strong>Pause on Hover:</strong></td>
					<td valign=\"top\">
						<select id=\"viewerHoverPause\" name=\"viewerHoverPause\">
						<option value=\"yes\">Yes</option>
						<option value=\"no\">No</option>
						</select>
					</td>
				</tr>
			</table>
		</div>
	</div>";
	
	$formHTML = str_replace("\r","",$formHTML);
	$formHTML = str_replace("\n","",$formHTML);
	$formHTML = str_replace("\t","",$formHTML);
	$formHTML = str_replace('"','\"',$formHTML);
	
	return $formHTML;
}

// Prizm Cloud JavaScript
function pzc_script()
{
	$script = '<script type="text/javascript">
	if (typeof jQuery.ui === \'undefined\')
	{
		var script_ui = document.createElement(\'script\');
		script_ui.type = "text/javascript";
		script_ui.src = "http://code.jquery.com/ui/1.10.3/jquery-ui.js";
		document.getElementsByTagName(\'head\')[0].appendChild(script_ui);
		
		var css_ui = document.createElement(\'link\');
		css_ui.rel = "stylesheet";
		css_ui.href = "http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css";
		document.getElementsByTagName(\'head\')[0].appendChild(css_ui);
	}
	function isInt(n)
	{
		return typeof n === \'number\' && n % 1 == 0;
	}
	function pzc_show_form()
	{
		jQuery("<div style=\"display: none;\">'.pzc_form().'</div>").appendTo("body");
		jQuery("#pzc-form").dialog({ width: 450, buttons: { "Add Viewer": pzc_add_viewer, "Cancel": pzc_hide_form } });
	}
	function pcSettings(viewerType)
	{
		if (viewerType == "slideshow")
		{
			jQuery("#slideshowViewer").css("display", "block");
			jQuery("#documentViewer").css("display", "none");
		}
		else
		{
			jQuery("#slideshowViewer").css("display", "none");
			jQuery("#documentViewer").css("display", "block");
		}
	}
	function pzc_hide_form()
	{
		jQuery("#pzc-form").dialog("close");
		jQuery("#pzc-form").remove();
	}
	function pzc_add_viewer()
	{
		var licenseKey = jQuery("#pzc-form").find("#licenseKey").val();
		var viewerDocument = jQuery("#pzc-form").find("#viewerDocument").val();
		var viewerType = jQuery("#pzc-form").find("input[name=viewerType]:checked").val();
		var viewerWidth = jQuery("#pzc-form").find("#viewerWidth").val();
		if (!isInt(viewerWidth)) { viewerWidth = 600; }
		var viewerHeight = jQuery("#pzc-form").find("#viewerHeight").val();
		if (!isInt(viewerHeight)) { viewerHeight = 800; }
		var viewerPrintButton = jQuery("#pzc-form").find("input[name=viewerPrintButton]:checked").val();
		if (viewerPrintButton != "No") { viewerPrintButton = "Yes"; }		
		var viewerToolbarColor = jQuery("#pzc-form").find("#viewerToolbarColor").val();
		if (viewerToolbarColor.length == 0) { viewerToolbarColor = "CCCCCC"; }
		viewerToolbarColor = viewerToolbarColor.replace("#","");
		
		var animType = jQuery("#pzc-form").find("#viewerAnimType").val();
		var animDuration = jQuery("#pzc-form").find("#viewerAnimDuration").val();
		if (!isInt(animDuration)) { animDuration = 450; }
		var animSpeed = jQuery("#pzc-form").find("#viewerAnimSpeed").val();
		if (!isInt(animSpeed)) { animSpeed = 5000; }
		var automatic = jQuery("#pzc-form").find("#viewerAutomatic").val();
		var showControls = jQuery("#pzc-form").find("#viewerShowControls").val();
		var centerControls = jQuery("#pzc-form").find("#viewerCenterControls").val();
		var keyboardNav = jQuery("#pzc-form").find("#viewerKeyboardNav").val();
		var hoverPause = jQuery("#pzc-form").find("#viewerHoverPause").val();
		
		var iframeWidth = viewerWidth + 20;
		var iframeHeight = viewerHeight + 40;
		
		var pzc_url = "//connect.ajaxdocumentviewer.com/?key="+licenseKey+"&amp;viewertype="+viewerType+"&amp;document="+encodeURIComponent(viewerDocument)+"&amp;viewerheight="+viewerHeight+"&amp;viewerwidth="+viewerWidth;
		if (viewerType == "slideshow")
		{
			pzc_url += "&amp;animtype=" + animType + "&amp;animduration=" + animDuration + "&amp;animspeed=" + animSpeed + "&amp;automatic=" + automatic + "&amp;showcontrols=" + showControls + "&amp;centercontrols=" + centerControls + "&amp;keyboardnav=" + keyboardNav + "&amp;hoverpause=" + hoverPause;
		}
		else
		{
			pzc_url += "&amp;printButton="+viewerPrintButton+"&amp;toolbarColor="+viewerToolbarColor;
		}
		pzc_url += "&amp;integration=Textpattern";
		
		var viewerCode = "\\n <iframe src=\""+pzc_url+"\" width=\""+iframeWidth+"\" height=\""+iframeHeight+"\"></iframe>";
		
		// insert in the end of <textarea id="body">
		var contentBody = jQuery("textarea#body").val() + viewerCode;
		jQuery("textarea#body").val(contentBody);
		
		pzc_hide_form();
	}
	</script>';
    return $script;
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

# --- END PLUGIN HELP ---
-->
<?php
}
?>

