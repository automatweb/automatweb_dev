<style type="text/css">
DIV {
	position: absolute;
}
DIV.navigation { visibility; visible; left: 10px; top: 50px; font-family: Verdana; width: 750px; font-weight: bold; font-size: 9px; }
DIV.tab {  visibility: hidden;   left: 0px;   top: 15px;  height: 100px;   width: 400px;}
DIV.tab1 {  visibility: visible;   left: 0px;   top: 15px;  height: 100px;   width: 400px;}
</style>
<script language="javascript">
var stab = 1;
function show_tab(tid) {
	theobjs["xtab" + stab].objHide();
	stab = tid;
	theobjs["xtab" + stab].objShow();
}
</script>

<script language="Javascript">
function savemenu() {
	document.menuinfo.submit();
}
</script>

<div id="navi" class="navigation">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr><td class="fgtitle">&nbsp;<img src='/images/trans.gif' width=1 height=12>
<a href="javascript:savemenu()"><b><font color=red>{VAR:LC_MENUEDIT_SAVE}</font></b></a>&nbsp;
<a href="javascript:show_tab(1)"><B>{VAR:LC_MENUEDIT_MENU_GENERAL}</b></a> |
<a href="javascript:show_tab(2)"><b>{VAR:LC_MENUEDIT_MENU_TEMPLATES}</b></a> |
<a href="javascript:show_tab(3)"><B>{VAR:LC_MENUEDIT_MENU_AUTOMATIC}</b></a> |
<a href="javascript:show_tab(9)"><b>{VAR:LC_MENUEDIT_MENU_LOOK_MORE}</b></a> | 
<!-- SUB: CAN_BROTHER -->
<a href="javascript:show_tab(4)"><b>{VAR:LC_MENUEDIT_BROTHERING}</b></a> |
<!-- END SUB: CAN_BROTHER -->

<!-- SUB: IS_LAST -->
<a href="javascript:show_tab(5)"><b>{VAR:LC_MENUEDIT_DOCUMENTS}</b></a> | 
<!-- END SUB: IS_LAST -->
<a href="javascript:show_tab(6)"><b>{VAR:LC_MENUEDIT_PICTURE}</b></a> | 
<a href="javascript:show_tab(7)"><b>{VAR:LC_MENUEDIT_EXPORT}</b></a> | 
<!-- SUB: IS_SHOP -->
<a href="javascript:show_tab(8)"><b>{VAR:LC_MENUEDIT_CHOOSE_SHOP}</b></a> | 
<!-- END SUB: IS_SHOP -->

</td>
</tr>
</table>
</div>
<div id="frame" style="position: absolute; left: 0; top: 0; visibility: visible">
<form action='reforb.{VAR:ext}' name="menuinfo" method=post enctype='multipart/form-data'>
<input type='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
<div id="xtab1" class="tab1">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_OBJECT}:&nbsp;</td>
		<td class="fgtext_g">&nbsp;<b>ID:</b>&nbsp;{VAR:id}</td>
		<td class="fgtext_g" colspan=3>&nbsp;<b>{VAR:LC_MENUEDIT_CREATED}:</b>&nbsp;{VAR:createdby} @ {VAR:created}</td>
		<td class="fgtext_g" colspan=3>&nbsp;<b>{VAR:LC_MENUEDIT_MODIFIED_BY}:</b>&nbsp;{VAR:modifiedby} {VAR:modified}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_NAME}:&nbsp;</td>
		<td class="fgtext_g" colspan=11><input type='text' NAME='name' VALUE='{VAR:name}' size=35></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Link:&nbsp;</td>
		<td class="fgtext_g" colspan=11><input type='text' NAME='link' VALUE='{VAR:link}' size=35></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Alias:&nbsp;</td>
		<td class="fgtext_g" colspan=11><input type='text' NAME='alias' VALUE='{VAR:alias}' size=50></td>
	</tr>
	<tr>
		<td class="title" colspan=11>&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS_AMOUNT}:&nbsp; <input type='text' NAME='ndocs' VALUE='{VAR:ndocs}' size=3> &nbsp;{VAR:LC_MENUEDIT_NO_TEST}:&nbsp;<input type='text' NAME='number' VALUE='{VAR:number}' size=3>{VAR:LC_MENUEDIT_WIDTH}:&nbsp;<input type='text' NAME='width' VALUE='{VAR:width}' size=3></td>
	</tr>
	<tr>
		<td class="title" >&nbsp;<a href='config.{VAR:ext}?type=sel_icon&rtype=menu_icon&rid={VAR:id}'>AW {VAR:LC_MENUEDIT_ICON}:</a>&nbsp;</td>
		<td class="fgtext_g" colspan=11>{VAR:icon}</td>
	</tr>
<!-- SUB: ADMIN_FEATURE -->
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_CHOOSE_PROGRAM}:&nbsp;</td>
		<td class="fgtext_g" colspan=11><select name=admin_feature><option value=0>{VAR:admin_feature}</select></td>
	</tr>
<!-- END SUB: ADMIN_FEATURE -->
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_SETTINGS}:&nbsp;</td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_ACTIVE}:&nbsp;<input type="checkbox" name="active" {VAR:active}></td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_CLICKABLE}:&nbsp;<input type='checkbox' NAME='clickable' VALUE='1' {VAR:clickable}></td>
		<td class="fgtext_g" nowrap>&nbsp;{VAR:LC_MENUEDIT_NEW_WINDOW}:&nbsp;<input type='checkbox' NAME='target' VALUE='1' {VAR:target}></td>
		<td class="fgtext_g" nowrap>&nbsp;{VAR:LC_MENUEDIT_HIDE_NOACT}:&nbsp;<input type='checkbox' NAME='hide_noact' VALUE='1' {VAR:hide_noact}></td>
		<td class="fgtext_g" >&nbsp;{VAR:LC_MENUEDIT_CENTERED}:&nbsp;<input type='checkbox' NAME='mid' VALUE='1' {VAR:mid}></td>
		<td class="fgtext_g" >&nbsp;{VAR:LC_MENUEDIT_LINK_COLLECTION}:&nbsp;<input type='checkbox' NAME='links' VALUE='1' {VAR:links}></td>
		<td class="fgtext_g" >&nbsp;{VAR:LC_MENUEDIT_SHOP}:&nbsp;<input type='checkbox' NAME='is_shop' VALUE='1' {VAR:is_shop}></td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g" >&nbsp;{VAR:LC_MENUEDIT_LEFT_PANE}:&nbsp;<input type="checkbox" value=1 name="left_pane" {VAR:left_pane}></td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_RIGHT_PANE}:&nbsp;<input type="checkbox" value=1 name="right_pane" {VAR:right_pane}></td>
		<td class="fgtext_g" >&nbsp;Users only:&nbsp;<input type="checkbox" value=1 name="users_only" {VAR:users_only}></td>
		<td class="fgtext_g" >&nbsp;Show lead:&nbsp;<input type="checkbox" value=1 name="show_lead" {VAR:show_lead}></td>
		<td class="fgtext_g" >{VAR:LC_MENUEDIT_ITEMS_SBS}:&nbsp;<input type='checkbox' name='shop_parallel' value=1 {VAR:shop_parallel}></td>
		<td class="fgtext_g" >Ilma men&uuml;&uuml;deta:&nbsp;<input type='checkbox' name='no_menus' value=1 {VAR:no_menus}></td>
		<td class="fgtext_g" colspan=20>{VAR:LC_MENUEDIT_IGNORE_NEXT}:&nbsp;<input type='checkbox' name='shop_ignoregoto' value=1 {VAR:shop_ignoregoto}></td>
	</tr>
	<tr>
		<td class="title" valign="top">&nbsp;{VAR:LC_MENUEDIT_COMMENT}:&nbsp;</td>
		<td class="fgtext_g" colspan=10><textarea NAME='comment' cols=50 rows=3>{VAR:comment}</textarea></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap >&nbsp;</td>
		<td class="fgtext_g" colspan=10>&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g" colspan=10>&nbsp;</td>
	</tr>
</table>
</td>
</tr>
</table>
<!-- SUB: IS_BROTHER -->
<br>
{VAR:LC_MENUEDIT_BROTHER_WHICH} <a href='menuedit.{VAR:ext}?menu=menu&parent={VAR:real_id}'>{VAR:LC_MENUEDIT_HERE}</a>
<!-- END SUB: IS_BROTHER -->
</div>
<div id="xtab2" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title" width=10%>&nbsp;{VAR:LC_MENUEDIT_TEMPL_EDIT}:&nbsp;</td>
		<td class="fgtext_g"><select name="tpl_edit">{VAR:tpl_edit}</select></td>
	</tr>
	<tr>
		<td class="title" width=10%>&nbsp;{VAR:LC_MENUEDIT_TEMPL_SHOW}:&nbsp;</td>
		<td class="fgtext_g"><select name="tpl_view"><option value="0">Default</option>{VAR:tpl_view}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;{VAR:LC_MENUEDIT_TEMPL_SHORT}:&nbsp;</td>
		<td class="fgtext_g"><select name="tpl_lead"><option value="0">Default</option>{VAR:tpl_lead}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab3" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_ACTIVATE}:&nbsp;</td>
		<td class="fgtext_g">&nbsp;<input type="checkbox" name="autoactivate" {VAR:autoactivate}>&nbsp;{VAR:activate_at}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;{VAR:LC_MENUEDIT_DEACTIVATE}:&nbsp;</td>
		<td class="fgtext_g">&nbsp;<input type="checkbox" name="autodeactivate" {VAR:autodeactivate}>&nbsp;{VAR:deactivate_at}</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab4" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><select MULTIPLE SIZE=20 name="sections[]">{VAR:sections}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_MENU_SECTIONS}</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab5" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><select MULTIPLE SIZE=20 class='small_button' name="sss[]">{VAR:sss}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><select MULTIPLE SIZE=5 class='small_button' name="pers[]">{VAR:pers}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_LAST_DOCUMENTS}</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab6" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">{VAR:LC_MENUEDIT_PICTURE}:</td>
		<td class="fgtext_g">{VAR:image}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><input type='file' name='img'></td>
	</tr>
</table>
</td>
</tr>
</table>
</div>

<div id="xtab7" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">{VAR:LC_MENUEDIT_CHOOSE_MENUS}:</td>
		<td class="fgtext_g">&nbsp;<select name='ex_menus[]' multiple size=15 class='small_button'>{VAR:ex_menus}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">{VAR:LC_MENUEDIT_SELECT_ALL_MENUS}? <input type='checkbox' name='allactive' value=1> {VAR:LC_MENUEDIT_EXPORT_ICONS}? <input type='checkbox' name='ex_icons' value=1></td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><input type='submit' onClick='menuinfo.action.value="export_menus";' value='{VAR:LC_MENUEDIT_EXPORT}'></td>
	</tr>
</table>
</td>
</tr>
</table>
</div>

<div id="xtab8" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">{VAR:LC_MENUEDIT_CHOOSE_SHOP}:</td>
		<td class="fgtext_g">&nbsp;<select name='shop' size=10 class='small_button'>{VAR:shop}</select></td>
	</tr>
</table>
</td>
</tr>
</table>
</div>

<div id="xtab9" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=750>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=750>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title">&nbsp;</td>
		<td class="fgtext_g"><select class="small_button" MULTIPLE SIZE=20 name="seealso[]">{VAR:seealso}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">
			<table border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td class="fgtext_g">{VAR:LC_MENUEDIT_NAME}</td>
					<td class="fgtext_g">{VAR:LC_MENUEDIT_ORDER}</td>
				</tr>
			<!-- SUB: SA_ITEM -->
				<tr>
					<td class="fgtext_g">{VAR:sa_name}</td>
					<td class="fgtext_g">&nbsp;<input type="text" size=3 class="small_button" name='sa_ord[{VAR:sa_id}]' value='{VAR:sa_ord}'></td>
				</tr>
			<!-- END SUB: SA_ITEM -->
			</table>
		</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;{VAR:LC_MENUEDIT_SELECT_SUBMENUS}</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>


{VAR:reforb}
</form>
</div>

<!-- 
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='{VAR:LC_MENUEDIT_SAVE}'></td>
</tr>
-->
</table>
</td>
</tr>
</table>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
