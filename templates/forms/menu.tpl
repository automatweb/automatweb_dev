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
	theobjs["menu3"].objHide();
	theobjs["menu3b"].objHide();
	theobjs["menu4"].objHide();
	theobjs["menu4b"].objHide();
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
<td align="center" height="17" class="peaLingidText"><a class="peaLingid" href="javascript:toggle1('menu1','menu1b')">Toimeta</a><img src='/images/transa.gif' width=10 height=1></td>
<td align="center" height="17" class="peaLingidText"><a href="javascript:toggle1('menu2','menu2b')" class="peaLingid" >M&auml;&auml;rangud</a><img src='/images/transa.gif' width=10 height=1></td>
<!-- 
<td align="center" height="17" class="peaLingidText">
<!-- SUB: FILLED_FORMS -->
<a href="javascript:toggle1('menu3','menu3b')" class="peaLingid">T&auml;idetud formid</a><img src='/images/transa.gif' width=10 height=1>
<!-- END SUB: FILLED_FORMS -->
</td>
<td align="center" height="17" class="peaLingidText">
<!-- SUB: OP_1 -->
<a href="javascript:toggle1('menu4','menu4b')" class="peaLingid">V&auml;ljundid</a><img src='/images/transa.gif' width=10 height=1>
<!-- END SUB: OP_1 -->
&nbsp;</td>-->
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
<div id="menu1" class="menyy1"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="javascript:toggle1('menu1','menu1b')" class="peaLingidText">Toimeta</a></td></tr></table></div>

<div id="menu1b" class="alammenyy">Toimeta> 
<!-- SUB: CAN_GRID -->
<a class="alamlingid" href='{VAR:change}'>Toimeta formi</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- SUB: SEARCH_SEL -->
<a class="alamlingid" href='{VAR:sel_search}'>Vali otsitavad formid</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: SEARCH_SEL -->

<!-- END SUB: CAN_GRID -->

<!-- SUB: CAN_PREVIEW -->
<a class="alamlingid" href='{VAR:show}'>Eelvaade</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_PREVIEW -->

<!-- SUB: CAN_ALL -->
<a class="alamlingid" href='{VAR:all_elements}'>K&otilde;ik elemendid</a>
<!-- END SUB: CAN_ALL -->

<a class="alamlingid" href='{VAR:import_entries}'>Impordi andmeid</a>
</div>
<!-- end# menu 1 -->

<!-- begin# menu 2 M22rangud -->
<div id="menu2" class="menyy2"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="objects.{VAR:ext}" target="main" class="peaLingidText">M&auml;&auml;rangud</a></td></tr></table></div>

<div id="menu2b" class="alammenyy">M&auml;&auml;rangud> 
<!-- SUB: CAN_TABLE -->
<a href='{VAR:table_settings}' class="alamlingid">Tabeli stiilid</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_TABLE -->

<!-- SUB: CAN_ACTION -->
<a class="alamlingid" href='{VAR:actions}'>Formi actionid</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_ACTION -->
<!-- 
<!-- SUB: CAN_ACL -->
<a class="alamlingid" href='forms.{VAR:ext}?type=acl&id={VAR:form_id}'>ACL</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_ACL -->
-->
<!-- SUB: CAN_META -->
<a class="alamlingid" href='{VAR:metainfo}'>Meta info</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_META -->
<!--
<a class="alamlingid" href='forms.{VAR:ext}?type=html&id={VAR:form_id}'>HTML</a>
-->
</div>
<!-- end# menu 2 -->

<!-- begin# menu 3 T2idetud formid -->
<div id="menu3" class="menyy3"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="#" target="main" class="peaLingidText">T&auml;idetud formid</a></td></tr></table></div>

<div id="menu3b" class="alammenyy">T&auml;idetud formid> 
<!-- SUB: CAN_FILLED -->
<a href='forms.{VAR:ext}?type=filled_forms&id={VAR:form_id}' class="alamlingid">T&auml;idetud formid</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_FILLED -->

<!-- SUB: CAN_IMPORT_DATA -->
<a href='forms.{VAR:ext}?type=import_contents&id={VAR:form_id}' class="alamlingid">Impordi sisu</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: CAN_IMPORT_DATA -->
</div>
<!-- end# menu 3 -->

<!-- begin# menu 4 v2ljundid -->
<div id="menu4" class="menyy4"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" height="17"><a href="#" target="main" class="peaLingidText">V&auml;ljundid</a></td></tr></table></div>

<div id="menu4b" class="alammenyy">V&auml;ljundid> 
<a href='{VAR:list_op}' class="alamlingid">V&auml;ljundid</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- SUB: OP_SEL -->
<br><img src='/images/transa.gif' height=17 width=7 border=0>
V&auml;ljund> 
<a href='{VAR:change_op}' class="alamlingid">Muuda v&auml;ljundit</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<a href='{VAR:op_preview}' class="alamlingid">Eelvaade</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<a href='{VAR:op_style}' class="alamlingid">V&auml;ljundi stiil</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<a href='{VAR:op_meta}' class="alamlingid">V&auml;ljundi metainfo</a><img src='/images/transa.gif' WIDTH=8 height=1 border=0>
<!-- END SUB: OP_SEL -->
</div>
<!-- end# menu 4 -->

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

<!-- SUB: FILLED_SEL -->
toggle1('menu3', 'menu3b');
<!-- END SUB: FILLED_SEL -->

<!-- SUB: OUTPUT_SEL -->
toggle1('menu4', 'menu4b');
<!-- END SUB: OUTPUT_SEL -->
</script>
<!-- END SUB: FG_MENU -->
