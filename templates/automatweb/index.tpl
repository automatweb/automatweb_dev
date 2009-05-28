<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={VAR:charset}" />
	<title>{VAR:html_title} {VAR:title_action}</title>
	<link rel="shortcut icon" href="{VAR:baseurl}/automatweb/images/aw06/favicon.ico" />
	<!-- SUB: MINIFY_JS_AND_CSS -->
	<link href="{VAR:baseurl}/automatweb/css/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/jquery-1.2.3.min.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_timer.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_aw_releditor.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_dump.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_formreset.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_aw_object_quickadd.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_tabs.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_gup.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_sup.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_bgiframe.min.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_dimensions.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_ajaxQueue.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_thickbox-compressed.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_autocomplete.min.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_hotkeys_0.0.3.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_shortcut_manager.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery-impromptu.1.5.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_init_session_modal.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery.selectboxes.min.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_aw_unload_handler.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_popup.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery.tooltip.min.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery.rightClick.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/ajax.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/CalendarPopupMin.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/popup_menu.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/BronCalendar.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/url.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw_help.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/other.js"></script>
	<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/defs.js"></script>
	<!-- END SUB: MINIFY_JS_AND_CSS -->
	{VAR:javascript}
	<script type="text/javascript">
	xchanged = 0;
	</script>
	<!--[if lt IE 7]>
	<link rel="stylesheet" type="text/css" href="{VAR:baseurl}/automatweb/css/iefix.css" />
	<![endif]-->
</head>
<body onLoad="check_generic_loader();">
<!-- SUB: MSG_POPUP -->
<div class="msg_popup" style="background: #ffffff; width: 150px; min-height: 76px; font-size: 11px; padding: 0px; visibility: hidden; position: fixed; right: 0px;  border: #05A6E9 2px solid;">
<div style="background: url('{VAR:baseurl}/automatweb/images/aw06/layout_t.gif') repeat-x top; height:14px; color: white; font-weight: bold; padding: 4px;">
<div style="float: left;">{VAR:msg_popup_title}</div>
<div style="float: right; font-family: arial; font-weight: bold;">
<a href="#" style="color: #ffffff;" id="msg_popup_close">X</a>
</div></div>

<div style="padding:10px;">
<a href="{VAR:msg_popup_url}" style="color: #666666;";>{VAR:msg_popup_content}</a>
</div>
</div>
<script type="text/javascript">
jQuery(function(){
	jQuery("div.msg_popup").popup();
});
</script>
<!-- END SUB: MSG_POPUP -->


<script type="text/javascript">

// aw object quickadd. use ctrl+alt+u to use
var options = {
	maxresults : 8,
	baseurl    : "{VAR:baseurl}",
	parent     : "{VAR:parent}"
};

var recKp = [];

function aw_keyhandler_rec(event)
{
	recKp[recKp.length] = event;
}

function aw_keyhandler_init(event)
{
	recKp[recKp.length] = event;
	$(window).unbind("keydown", aw_keyhandler_init);
	$(window).keydown(aw_keyhandler_rec);

	var html = '<div id="aw_object_quickadd" style="display: none;">\
		<div class="icon"><img src="/automatweb/images/aw06/blank.gif" width="40" alt="" /></div>\
		<div class="selected_object_name"></div>\
		<input type="text" id="aw_object_quickadd_input" class="text" /></div>\
		<div id="aw_object_quickadd_results" style="display: none;" ></div>';
	$("body").append(html);

	$.get("{VAR:baseurl}/automatweb/orb.aw?class=shortcut_manager&action=parse_shortcuts_from_xml", {}, function (d) 
		{ 
			eval(d); 
			// fetch items on demand
			$("#aw_object_quickadd").aw_object_quickadd(null, options);

			jQuery.hotkeys.add('Ctrl+Shift+a', function(){
				desc = prompt("Kirjeldus", "nimetu");
				if(desc){
					aw_popup_scroll("{VAR:stop_pop_url_add}&name=" + desc, "quick_task_entry", 800,600);
				}
			});

			jQuery.hotkeys.add('Ctrl+Shift+q', function(){
				aw_popup_scroll("{VAR:stop_pop_url_quick_add}", "quick_task_entry", 800,600);
			});

			jQuery.hotkeys.add('Ctrl+Shift+e', function(){
				aw_popup_scroll("{VAR:stop_pop_url_qw}", "quick_task_entry", 800,600);
			});

			$(window).unbind("keydown", aw_keyhandler_rec);
			for(var i = 0; i < recKp.length; i++)
			{
				$(window).trigger("keydown", recKp[i]);
			}
		}
	);
}
$(window).keydown(aw_keyhandler_init);


// init session modal which pops up 5 minutes before session end
$.init_session_modal({
	session_end_msg			: "{VAR:session_end_msg}",
	btn_session_end_continue	: "{VAR:btn_session_end_continue}",
	btn_session_end_cancel		: "{VAR:btn_session_end_cancel}",
	session_length			: {VAR:session_length}
});
</script>

<!-- p2is -->
<!-- SUB: YAH -->
<div id="pais">
	<div class="logo">
		<span>{VAR:prod_family}</span>
		<a href="{VAR:prod_family_href}" title="AutomatWeb"><img src="{VAR:baseurl}/automatweb/images/aw06/aw_logo.gif" alt="AutomatWeb.com" width="183" height="34" border="0" /></a>
	</div>
	<div class="top-left-menyy">
		<!-- SUB: SHOW_CUR_P -->
		<a href="{VAR:cur_p_url}">{VAR:cur_p_name}</a> |
		<!-- END SUB: SHOW_CUR_P -->
		<!-- SUB: SHOW_CUR_CO -->
		<a href="{VAR:cur_co_url}">{VAR:cur_co_name}</a> |
		<!-- END SUB: SHOW_CUR_CO -->
		<!-- SUB: SHOW_CUR_CO_VIEW -->
		<a href="{VAR:cur_co_url_view}">{VAR:cur_co_name}</a> |
		<!-- END SUB: SHOW_CUR_CO_VIEW -->
		<!-- SUB: SHOW_CUR_CLASS -->
		{VAR:cur_class} |
		<!-- END SUB: SHOW_CUR_CLASS -->
		<!-- SUB: SHOW_CUR_OBJ -->
		<a href="{VAR:cur_obj_url}">{VAR:cur_obj_name}</a>
		<!-- END SUB: SHOW_CUR_OBJ -->
	</div>
	<div class="top-right-menyy">
		{VAR:lang_pop}
		{VAR:settings_pop}
		<a title="{VAR:msg_title}" href="{VAR:msg_url}" class="quickmessagebox_link"><img src="{VAR:baseurl}/automatweb/images/icons/mail_send.gif" border="0" alt="{VAR:msg_title}" /></a>
		<a href="{VAR:baseurl}/orb.aw?class=users&action=logout" class="logout">{VAR:logout_text}</a>
	</div>

	<div class="olekuriba">
		{VAR:location_text}
		{VAR:site_title}
	</div>
	<!-- END SUB: YAH -->

		<!-- SUB: NO_HEADER -->
		<div id="pais2">
		<!-- END SUB: NO_HEADER -->

	{VAR:content}
<!-- //sisu -->
<!-- jalus -->
<!-- SUB: YAH2 -->
	<div id="jalus">
		{VAR:footer_l1} <br />
		{VAR:footer_l2} <a href="http://www.struktuur.ee">Struktuur Varahaldus</a>, <a href="http://www.automatweb.com">AutomatWeb</a>.
	</div>
<!-- END SUB: YAH2 -->
<!--//jalus -->

<!-- SUB: POPUP_MENUS -->
<!-- END SUB: POPUP_MENUS -->

{VAR:javascript_bottom}

</body>
</html>
