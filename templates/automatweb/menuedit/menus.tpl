<script language="Javascript">
<!--
function remote(toolbar,width,height,file) {
	self.name = "root";
	var wprops = "toolbar=" + toolbar + ",location=0,directories=0,status=0, "+
	"menubar=0,scrollbars=1,resizable=1,width=" + width + ",height=" + height;
	openwindow = window.open(file,"remote",wprops);
}

function box2(caption,url){
var answer=confirm(caption)
if (answer)
window.location=url
}

var chk_status = 1;

function selall()
{
	len = document.foo.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.foo.elements[i].name.indexOf("sel") != -1)
		{
			document.foo.elements[i].checked=chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
	return false;
}

function cut()
{
	document.foo.action.value="cut";
	document.foo.submit();
}

function ddelete()
{
	// first, if nothing is selected, say so. or maybe we shouldn't? naah. better tell the user about his/her errors
	chk = false;
	for(i = 0; i < document.foo.elements.length; i++)
	{
		if (document.foo.elements[i].type == "checkbox")
		{
			if (document.foo.elements[i].name.substr(0,3) == "sel" && document.foo.elements[i].checked)
			{
				chk = true;
			}
		}
	}
	if (!chk)
	{
		alert("Vali menyy(d), mida soovid kustutada!");
		return false;
	}

	if (confirm("Oled kindel et soovid valitud menyy(d) kustutada?"))
	{
		document.foo.action.value="mdelete";
		document.foo.submit();
		return true;
	}
	return false;
}

function paste()
{
	document.foo.action.value="paste";
	document.foo.submit();
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v3.0
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

// -->
</script>

<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/site.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/fg_menu.css">
<link rel="stylesheet" href="{VAR:baseurl}/automatweb/css/bench.css">




<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<form action='reforb.{VAR:ext}' METHOD=POST NAME='foo'>
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td width="5"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>




<!-- SUB: ADD_CAT -->
<td width="30" class="celltext">
<b>
{VAR:LC_MENUEDIT_MENU_CAPS}:&nbsp;
</b>
</td>
<td width="25" valign="middle"><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:addmenu}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('new','','{VAR:baseurl}/automatweb/images/blue/awicons/new_over.gif',1)"><img
name="new" alt="{VAR:LC_MENUEDIT_ADD}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/new.gif" width="25" height="25"></a></td>
<!-- END SUB: ADD_CAT -->


<!--ikoonid-->
<td valign="bottom" class="celltext">
<table border="0" cellpadding="0" cellspacing="0"><tr>

<td><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="{VAR:addpromo}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('promo','','{VAR:baseurl}/automatweb/images/blue/awicons/promo_over.gif',1)"><img
name="promo" alt="{VAR:LC_MENUEDIT_ADD_PROMO_BOX}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/promo.gif" width="25" height="25"></a></td>


<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a href="javascript:document.foo.submit()"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a></td>

<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"></td>

<td><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--import--><a href="{VAR:import}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('import','','{VAR:baseurl}/automatweb/images/blue/awicons/import_over.gif',1)"><img
name="import" alt="{VAR:LC_MENUEDIT_IMPORT}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/import.gif" width="25" height="25"></a></td>


<td><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><!--cut--><a
href="javascript:cut()"  onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('cut','','{VAR:baseurl}/automatweb/images/blue/awicons/cut_over.gif',1)"><img name="cut" alt="Cut" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/cut.gif" width="25" height="25"></a></td>

<!-- SUB: PASTE -->
<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:paste()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('paste','','{VAR:baseurl}/automatweb/images/blue/awicons/paste_over.gif',1)"><img name="paste" alt="Paste" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/paste.gif" width="25" height="25"></a></td>
<!-- END SUB: PASTE -->

<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="javascript:document.foo.submit()" onclick="return ddelete()" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('delete','','{VAR:baseurl}/automatweb/images/blue/awicons/delete_over.gif',1)"><img name="delete" alt="{VAR:LC_MENUEDIT_DELETE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/delete.gif" width="25" height="25"></a></td>


<td><IMG
SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"></td>

<!--referesh-->
<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="#" onClick='window.location.reload()' onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('refresh','','{VAR:baseurl}/automatweb/images/blue/awicons/refresh_over.gif',1)"><img name="refresh" alt="{VAR:LC_MENUEDIT_REFRESH}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/refresh.gif" width="25" height="25"></a></td>

<!--bugtrack-->
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><a
href="orb.aw?action=list&class=bugtrack&filt=all" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('bugtrack','','{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack_over.gif',1)"><img name="bugtrack" alt="Bugtrack" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/bugtrack.gif" width="25" height="25"></a></td>

<td><IMG
SRC="{VAR:baseurl}/images/trans.gif" WIDTH="4" HEIGHT="1" BORDER=0 ALT=""><img
SRC="{VAR:baseurl}/automatweb/images/blue/awicons/seperator.gif" width="6" height="25"></td>

</tr></table>
</td>
<td align="right" class="celltext">&nbsp;&nbsp;[ <a target="list" href='{VAR:baseurl}/automatweb/orb.{VAR:ext}?class=languages&action=admin_list'><b>{VAR:lang_name}</b></a> ]&nbsp;&nbsp;</td>
</tr>
</table>





		</td></tr>
		</table>

	</td></tr>
	</table>

</td></tr>
</table>



















<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#FFFFFF">


<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr class="aste05">
<td width="1%" height="15" class="celltext">&nbsp;</td>
<td width="40%" height="15" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=name&order={VAR:order1}&period={VAR:period}'>{VAR:LC_MENUEDIT_NAME}</a>{VAR:sortedimg1}&nbsp;</td>
<td width="4%" align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=jrk&order={VAR:order2}&period={VAR:period}'>{VAR:LC_MENUEDIT_ORDER}</a>{VAR:sortedimg2}&nbsp;</td>
<td width="6%" align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=status&order={VAR:order5}&period={VAR:period}'>{VAR:LC_MENUEDIT_ACTIVE}</a>{VAR:sortedimg5}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=modifiedby&order={VAR:order3}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED_BY}</a>{VAR:sortedimg3}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=modified&order={VAR:order4}&period={VAR:period}'>{VAR:LC_MENUEDIT_MODIFIED}</a>{VAR:sortedimg4}&nbsp;</td>
<td align="center" class="celltext">&nbsp;<a href='orb.{VAR:ext}?class=menuedit&action=menu_list&parent={VAR:parent}&sortby=periodic&order={VAR:order6}&period={VAR:period}'>{VAR:LC_MENUEDIT_PERIODIC}</a>{VAR:sortedimg6}&nbsp;</td>
<td align="center" colspan="4" class="celltext">&nbsp;{VAR:LC_MENUEDIT_ACTION}&nbsp;</td>
<td align="center" class="celltext"><b>&nbsp;<a href='#' onClick="selall()">{VAR:LC_MENUEDIT_CHOOSE}</a>&nbsp;</b></td>
</tr>
<!-- SUB: CUT -->
aste03
<!-- END SUB: CUT -->
<!-- SUB: NORMAL -->
aste07
<!-- END SUB: NORMAL -->

<!-- SUB: LINE -->
<tr class="{VAR:is_cut}">
<td height="15" class="celltext" align=center><a href='{VAR:clicker}' target='list'><img border=0 src='{VAR:imgref}'></a></td>
<td height="15" onMouseOver="this.style.backgroundColor='#A2BCCC';" onMouseOut="this.style.backgroundColor='#DBE8EE';" class="celltext">&nbsp;<a href='{VAR:clicker_r}' target='list'>{VAR:name}</a>&nbsp;</td>
<td class="celltext" align=center>
<!-- SUB: NFIRST -->
<input class='small_button' type=text NAME='ord[{VAR:menu_id}]' VALUE='{VAR:menu_order}' SIZE=3 MAXLENGTH=4><input type='hidden' name='old_ord[{VAR:menu_id}]' value='{VAR:menu_order}'>
<!-- END SUB: NFIRST -->
</td>
<td align="center" class="celltext">
<!-- SUB: CAN_ACTIVE -->
<input type='checkbox' NAME='act[{VAR:menu_id}]' {VAR:menu_active}><input type='hidden' NAME='old_act[{VAR:menu_id}]' VALUE='{VAR:menu_active2}'>
<!-- END SUB: CAN_ACTIVE -->
</td>
<td align="center" class="celltext" nowrap>{VAR:modifiedby}</td>
<td align="center" class="celltext" nowrap>{VAR:modified}</td>
<td align="center" class="celltext">
<!-- SUB: PERIODIC -->
<input type='checkbox' NAME='prd[{VAR:menu_id}]' {VAR:prd1}><input type='hidden' NAME='old_prd[{VAR:menu_id}]' VALUE='{VAR:prd2}'>
<!-- END SUB: PERIODIC -->
</td>
<td class="celltext" align="center">
<!-- SUB: CAN_CHANGE -->
<a href='{VAR:properties}'><img src="{VAR:baseurl}/automatweb/images/blue/obj_settings.gif" alt="{VAR:LC_MENUEDIT_PROPERTIES}" border="0"></a>
<!-- END SUB: CAN_CHANGE -->
</td>
<td class="celltext" align="center">
<!-- SUB: CAN_DELETE -->
<a href="javascript:box2('{VAR:LC_MENUEDIT_SURE_DELETE_MENU}?','{VAR:delete}')"><img src="{VAR:baseurl}/automatweb/images/blue/obj_delete.gif" border="0" alt="{VAR:LC_MENUEDIT_DELETE}"></a>
<!-- END SUB: CAN_DELETE -->
</td>
<td class="celltext" align="center">
<!-- SUB: CAN_SEL_PERIOD -->
<a href="periods.{VAR:ext}?oid={VAR:menu_id}"><img src="{VAR:baseurl}/automatweb/images/blue/obj_period.gif" border="0" alt="{VAR:LC_MENUEDIT_PERIOD}"></a>
<!-- END SUB: CAN_SEL_PERIOD -->
</td>
<td class="celltext" align="center">
<!-- SUB: CAN_ACL -->
<a href='editacl.{VAR:ext}?oid={VAR:r_menu_id}&file=menu.xml'><img src="{VAR:baseurl}/automatweb/images/blue/obj_acl.gif" border="0" alt="ACL"></a>
<!-- END SUB: CAN_ACL -->
</td>
<td class="celltext"><input type='checkbox' NAME='sel[{VAR:menu_id}]' VALUE=1></td>
</tr>
<!-- END SUB: LINE -->
</table>



</td>
</tr>
</table>




<input type="hidden" name="period" value="{VAR:period}">
{VAR:reforb}
</form>
