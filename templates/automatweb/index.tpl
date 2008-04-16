<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={VAR:charset}" />
<title>{VAR:html_title} {VAR:title_action}</title>
<link rel="shortcut icon" href="{VAR:baseurl}/automatweb/images/aw06/favicon.ico" />

<!-- SUB: MINIFY_JS_AND_CSS -->
<link href="{VAR:baseurl}/automatweb/css/stiil.css" rel="stylesheet" type="text/css" />
<link href="{VAR:baseurl}/automatweb/css/sisu.css" rel="stylesheet" type="text/css" />
<link href="{VAR:baseurl}/automatweb/css/aw06.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/jquery-1.2.3.min.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_timer.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_dump.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_formreset.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/aw.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/browserdetect.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/ajax.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/CalendarPopupMin.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/popup_menu.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/BronCalendar.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/url.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/shortcuts.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/other.js"></script>
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/defs.js"></script>
<!-- END SUB: MINIFY_JS_AND_CSS -->

<link href="{VAR:baseurl}/automatweb/css/jquery_aw_object_quickadd.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_aw_object_quickadd.js"></script>
<script src="http://hannes.dev.struktuur.ee/automatweb/orb.aw?class=aw_object_quickadd&action=get_objects" type="text/javascript"></script>

<script type="text/javascript" src="{VAR:baseurl}/automatweb/js/jquery/plugins/jquery_aw_releditor.js"></script>

<script type="text/javascript">
shortcut("Ctrl+Shift+A",function() {
	desc = prompt("Kirjeldus", "nimetu");
	if(desc){
		aw_popup_scroll("{VAR:stop_pop_url_add}&name=" + desc, "quick_task_entry", 800,600);
	}
});

shortcut("Ctrl+Shift+Q",function() {
	aw_popup_scroll("{VAR:stop_pop_url_quick_add}", "quick_task_entry", 800,600);
});

shortcut("Ctrl+Shift+E", function() {
	aw_popup_scroll("{VAR:stop_pop_url_qw}", "quick_task_entry", 800,600);
});
</script>


<!--[if lt IE 7]>
    <link rel="stylesheet" type="text/css" href="{VAR:baseurl}/automatweb/css/iefix.css" />
<![endif]-->

</head>
<div style="padding:10px 20px; width:200px; left: 50%; margin-left: -100px; height:200; top:50%; margin-top:-100; background-color:white; border:1px solid silver; position:absolute; text-align:center; color:gray; font-size:12px; display:none;" id="ajax_loader_div"><img src="{VAR:baseurl}/automatweb/images/ajax-loader.gif"><br/><br/>Laadin...</div>
<body onLoad="check_generic_loader();">

<div id="aw_object_quickadd" style="display: none;">
<!--	<div class="icon"><img src="http://register.automatweb.com/automatweb/images/icons/class_1.gif" width="40" alt="" /></div>-->
	<!--<div class="icon"><img src="http://register.automatweb.com/automatweb/images/icons/class_129.gif" width="40" alt="" /></div>-->
	<div class="icon"><img src="http://register.automatweb.com/automatweb/images/aw06/blank.gif" width="40" alt="" /></div>
	<div class="selected_object_name"></div>
	<input type="text" class="text" /></div>
<div id="aw_object_quickadd_results" style="display: none;" ></div>

<script type="text/javascript">
var options = {
	maxresults : 8,
	baseurl    : "{VAR:baseurl}",
	parent     : '{VAR:parent}'
};
$("#aw_object_quickadd").AWObjectQuickAdd(items, options);
</script>

<!-- päis -->
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
<!--	<div class="top-left-menyy">{VAR:cur_p_url} | {VAR:cur_co_url} | {VAR:cur_class} | <a href="{VAR:cur_obj_url}">{VAR:cur_obj_name}</a></div>-->
	<div class="top-right-menyy">
		{VAR:lang_pop}
		{VAR:settings_pop}

		<a href="{VAR:baseurl}/orb.aw?class=users&action=logout" class="logout">{VAR:logout_text}</a>
	</div>
	<div class="olekuriba">{VAR:location_text}
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
		{VAR:footer_l2} <a href="http://www.struktuur.ee">Struktuur Meedia</a>, <a href="http://www.automatweb.com">AutomatWeb</a>.
	</div>
<!-- END SUB: YAH2 -->
<!--//jalus -->

<!-- SUB: POPUP_MENUS -->
<!-- END SUB: POPUP_MENUS -->

</body>
</html>
