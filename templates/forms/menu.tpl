<!-- SUB: FG_MENU -->
<!-- see on formgeni ylemine menyy-->
<script language="Javascript" src="{VAR:baseurl}/automatweb/js/cbobjects.js"></script>
<script language="Javascript">
function init() {
    create_objects();
    toggle('menu1');
    toggle('menu1b');
}

function hideall() {
	theobjs["menu1"].objHide();
	theobjs["menu1b"].objHide();
	theobjs["menu2"].objHide();
	theobjs["menu2b"].objHide();
};

function toggle(layer) {
        hideall();
        theobjs[layer].objShow();
};

function toggle1(layer1,layer2) {
	hideall();
        theobjs[layer1].objShow();
        theobjs[layer2].objShow();
};
</script>

<div id="muh" class="muh">

<div id="mainmenu" class="mainmenu">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
<td>
<table border="0" cellspacing="0" cellpadding="0">
<tr> 
<td align="center" height="17" class="peaLingidText"><a class="peaLingid" href="javascript:toggle1('menu1','menu1b')">{VAR:LC_FORMS_TOIMETA}</a><img src='/images/transa.gif' width=10 height=1></td>
<td align="center" height="17" class="peaLingidText"><a href="javascript:toggle1('menu2','menu2b')" class="peaLingid" >{VAR:LC_FORMS_SETTINGS}</a><img src='/images/transa.gif' width=10 height=1></td>
</tr>
</table>
</td>
</tr>
<tr> 
<td background="/images/transa.gif" height="17" class="alamLingidText"><img src="/images/menu/transparent.gif" border="0" width="100" height="2"></td>
</tr>
<tr> 
<td background="/images/transa.gif" height="17" class="alamLingidText"><img src="/images/menu/transparent.gif" border="0" width="100" height="2"></td>
</tr>
</table>
</div>

<!-- begin# menu 1 Toimeta -->
<div id="menu1" class="menyy1"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="javascript:toggle1('menu1','menu1b')" class="peaLingidText">{VAR:LC_FORMS_TOIMETA}</a></td></tr></table></div>

<div id="menu1b" class="alammenyy">{VAR:LC_FORMS_TOIMETA}> 
<!-- SUB: CAN_GRID -->
<a class="alamlingid" href='{VAR:change}'>{VAR:LC_FORMS_ADMIN_FORM}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- SUB: SEARCH_SEL -->
<a class="alamlingid" href='{VAR:sel_search}'><!-- IMHO: LOOKED != OTSITAVAD aga hui sellega-->{VAR:LC_FORMS_CHOOSE_LOOKED_FORMS}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: SEARCH_SEL -->
<!-- SUB: FILTER_SEARCH_SEL -->
<a class="alamlingid" href='{VAR:sel_filter_search}'>{VAR:LC_FORMS_CHOOSE_USEABLE_FILTER}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: FILTER_SEARCH_SEL -->

<!-- END SUB: CAN_GRID -->

<!-- SUB: CAN_PREVIEW -->
<a class="alamlingid" href='{VAR:show}'>{VAR:LC_FORMS_PREVIEW}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_PREVIEW -->

<!-- SUB: CAN_ALL -->
<a class="alamlingid" href='{VAR:all_elements}'>{VAR:LC_FORMS_ALL_ELEMENTS}</a>
<!-- END SUB: CAN_ALL -->

<a class="alamlingid" href='{VAR:import_entries}'>{VAR:LC_FORMS_IMPORT_DATA}</a>
</div>
<!-- end# menu 1 -->

<!-- begin# menu 2 M22rangud -->
<div id="menu2" class="menyy2"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="objects.{VAR:ext}" target="main" class="peaLingidText">{VAR:LC_FORMS_SETTINGS}</a></td></tr></table></div>

<div id="menu2b" class="alammenyy">{VAR:LC_FORMS_SETTINGS}> 
<!-- SUB: CAN_TABLE -->
<a href='{VAR:table_settings}' class="alamlingid">{VAR:LC_FORMS_TABLE_STYLES}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_TABLE -->

<!-- SUB: CAN_ACTION -->
<a class="alamlingid" href='{VAR:actions}'>{VAR:LC_FORMS_FORM_ACTIONS}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_ACTION -->
<!-- 
<!-- SUB: CAN_ACL -->
<a class="alamlingid" href='forms.{VAR:ext}?type=acl&id={VAR:form_id}'>ACL</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_ACL -->
-->
<!-- SUB: CAN_META -->
<a class="alamlingid" href='{VAR:metainfo}'>Metainfo</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_META -->

<a class="alamlingid" href='{VAR:set_folders}'>{VAR:LC_FORMS_CHOOSE_CATALOGUES}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>

<a class="alamlingid" href='{VAR:translate}'>{VAR:LC_FORMS_LANGS}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>

<a class="alamlingid" href='{VAR:tables}'>{VAR:LC_FORMS_TABLES}</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
</div>
<!-- end# menu 2 -->

</div>
<img src='/images/transa.gif' width=1 height=60>
<script language = javascript>
init();
<!-- SUB: GRID_SEL -->
toggle1('menu1', 'menu1b');
<!-- END SUB: GRID_SEL -->

<!-- SUB: SETTINGS_SEL -->
toggle1('menu2', 'menu2b');
<!-- END SUB: SETTINGS_SEL -->
</script>
<!-- END SUB: FG_MENU -->
