<style type="text/css">
DIV {
	position: absolute;
}
DIV.navigation { visibility; visible; left: 10px; top: 50px; font-family: Verdana; width: 600px; font-weight: bold; font-size: 9px; }
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
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border="0" cellspacing="1" cellpadding="0" width=100%>
<tr><td class="fgtitle">&nbsp;<img src='/images/trans.gif' width=1 height=12>
<a href="javascript:show_tab(1)"><B>General</b></a> |
<a href="javascript:show_tab(2)"><b>Templated</b></a> |
<a href="javascript:show_tab(3)"><B>Automaatsed tegevused</b></a> |
<!-- SUB: CAN_BROTHER -->
<a href="javascript:show_tab(4)"><b>Vennastamine</b></a> |
<!-- END SUB: CAN_BROTHER -->
<a href="javascript:show_tab(5)"><b>Dokumendid</b></a> | 

<!-- SUB: IS_LAST -->
<a href="javascript:show_tab(5)"><b>Dokumendid</b></a> | 
<!-- END SUB: IS_LAST -->
<a href="javascript:savemenu()"><b><font color=red>Salvesta</font></b></a>&nbsp;
</td>
</tr>
</table>
</div>
<div id="frame" style="position: absolute; left: 0; top: 0; visibility: visible">
<form action='refcheck.{VAR:ext}' name="menuinfo" method=post>
<div id="xtab1" class="tab1">
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
	<tr>
		<td class="title">&nbsp;Objekt:&nbsp;</td>
		<td class="fgtext_g">&nbsp;<b>ID:</b>&nbsp;{VAR:id}</td>
		<td class="fgtext_g" colspan=3>&nbsp;<b>Loodud:</b>&nbsp;{VAR:createdby} @ {VAR:created}</td>
		<td class="fgtext_g">&nbsp;<b>Muudetud:</b>&nbsp;{VAR:modifiedby} {VAR:modified}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;Nimi:&nbsp;</td>
		<td class="fgtext_g" colspan=10><input type='text' NAME='name' VALUE='{VAR:name}' size=35></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Link:&nbsp;</td>
		<td class="fgtext_g" colspan=10><input type='text' NAME='link' VALUE='{VAR:link}' size=35></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Alias:&nbsp;</td>
		<td class="fgtext_g" colspan=10><input type='text' NAME='alias' VALUE='{VAR:alias}' size=50></td>
	</tr>
	<tr>
		<td class="title">&nbsp;M&auml;&auml;rangud:&nbsp;</td>
		<td class="fgtext_g">&nbsp;Aktiivne:&nbsp;<input type="checkbox" name="active" {VAR:active}></td>
		<td class="fgtext_g">&nbsp;Klikitav:&nbsp;<input type='checkbox' NAME='clickable' VALUE='1' {VAR:clickable}></td>
		<td class="fgtext_g" nowrap>&nbsp;Uues aknas:&nbsp;<input type='checkbox' NAME='target' VALUE='1' {VAR:target}></td>
		<td class="fgtext_g" nowrap>&nbsp;MaKDP:&nbsp;<input type='checkbox' NAME='hide_noact' VALUE='1' {VAR:hide_noact}></td>
		<td class="fgtext_g" >&nbsp;Keskel:&nbsp;<input type='checkbox' NAME='mid' VALUE='1' {VAR:mid}></td>
	</tr>
	<tr>
		<td class="title" valign="top">&nbsp;Kommentaar:&nbsp;</td>
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
See on vennastatud men&uuml;&uuml;, mille vanem vend asub <a href='menuedit.{VAR:ext}?menu=menu&parent={VAR:real_id}'>siin</a>
<!-- END SUB: IS_BROTHER -->
</div>
<div id="xtab2" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title" width=10%>&nbsp;Muutmiseks:&nbsp;</td>
		<td class="fgtext_g"><select name="tpl_edit">{VAR:tpl_edit}</select></td>
	</tr>
	<tr>
		<td class="title" width=10%>&nbsp;N&auml;itamiseks:&nbsp;</td>
		<td class="fgtext_g"><select name="tpl_view"><option value="0">Default</option>{VAR:tpl_view}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;L&uuml;hike / lead only:&nbsp;</td>
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
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Aktiveerida:&nbsp;</td>
		<td class="fgtext_g">&nbsp;<input type="checkbox" name="autoactivate" {VAR:autoactivate}>&nbsp;{VAR:activate_at}</td>
	</tr>
	<tr>
		<td class="title">&nbsp;Deaktiveerida:&nbsp;</td>
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
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
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
		<td class="fgtext_g">&nbsp;Vali mis sektsioonide all seda men&uuml;&uuml;d samuti n&auml;idatakse</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab5" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
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
		<td class="fgtext_g"><select MULTIPLE SIZE=20 name="sss[]">{VAR:sss}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;Vali mis sektsioonide alt viimased dokumendid v&otilde;etakse.</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<div id="xtab5" class="tab">
<table border="0" cellspacing="0" cellpadding="0" width=600>
<tr>
<td bgcolor="#CCCCCC">
<table border=0 cellspacing=1 cellpadding=1 width=600>
	<tr>
		<td class="title" width=10%>&nbsp;ID:&nbsp;</td>
		<td class="fgtext_g"><b>{VAR:id}</b></td>
	</tr>
	<tr>
		<td class="title">&nbsp;Mitu viimast dokumenti:&nbsp;</td>
		<td class="fgtext_g" colspan=10><input type='text' NAME='ndocs' VALUE='{VAR:ndocs}' size=3></td>
	</tr>
	<tr>
		<td class="title">Sektsioonid:</td>
		<td class="fgtext_g"><select MULTIPLE SIZE=20 name="sss[]">{VAR:sss}</select></td>
	</tr>
	<tr>
		<td class="title">Perioodid:</td>
		<td class="fgtext_g"><select MULTIPLE name="pers[]">{VAR:pers}</select></td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;</td>
		<td class="fgtext_g">&nbsp;</td>
	</tr>
	<tr>
		<td class="title" width=10% nowrap>&nbsp;<font color="red">Legend:</font>&nbsp;</td>
		<td class="fgtext_g">&nbsp;Vali mis sektsioonide alt viimased dokumendid v&otilde;etakse.</td>
	</tr>
</table>
</td>
</tr>
</table>
</div>
<input type='hidden' NAME='action' VALUE='submit_menu'>
<input type='hidden' NAME='parent' VALUE='{VAR:parent}'>
<input type='hidden' NAME='id' VALUE='{VAR:id}'>
</form>
</div>

<!-- 
<tr>
<td class="fcaption" colspan=2><input class='small_button' type='submit' VALUE='Save'></td>
</tr>
-->
</table>
</td>
</tr>
</table>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>