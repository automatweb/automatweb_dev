<form method="POST" action="reforb.{VAR:ext}" name="doc">

<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">

<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:document.doc.submit();" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:document.doc.submit();" >Salvesta</a></td>
<td><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a target="_blank" href="{VAR:baseurl}/index.aw?section={VAR:id}" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('preview','','{VAR:baseurl}/automatweb/images/blue/awicons/preview_over.gif',1)"><img name="preview" alt="Eelvaade" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/preview.gif" width="25" height="25"></a><br><a
target="_blank" href="{VAR:baseurl}/index.aw?section={VAR:id}">Eelvaade</a></td>
<td><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

</table>


		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>










<table border=0 cellspacing=1 cellpadding=2 width="100%">

<tr>
<td COLSPAN=2>


<table border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td colspan=3><img src='{VAR:baseurl}/images/transa.gif' width=1 height=10 border=0></td>
	</tr>
	<tr>
		<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=113 height=1 border=0><br><B>&nbsp;M‰‰rangud&nbsp;</b></td>
		<td class="fcaption2_nt" bgcolor="#CCCCCC"><img src='{VAR:baseurl}/images/transa.gif' width=1 height=10 border=0></td>
		<td class="celltext">&nbsp;	
			Aktiivne: <input type='checkbox' name='status' value='2' {VAR:cstatus}>&nbsp;&nbsp;&nbsp;
			Foorum:	<input type='checkbox' name='is_forum' value='1' {VAR:is_forum}>&nbsp;&nbsp;&nbsp;
	N‰ita leadi: <input type='checkbox' name='showlead' value=1 {VAR:showlead}>&nbsp;&nbsp;&nbsp;
	<!--
	Ilma parema paanita: <input type='checkbox' name='no_right_pane' value=1 {VAR:no_right_pane}>&nbsp;&nbsp;&nbsp;
	-->
	Pealkiri klikitav: <input type='checkbox' name="title_clickable" {VAR:title_clickable} value=1>&nbsp;&nbsp;&nbsp;
	T&uuml;hista stiilid:	<input type='checkbox' name="clear_styles" value=1><br>

	<!--
	&nbsp;&nbsp;Lingi vıtmesınad:	<input type='checkbox' name="link_keywords" value=1>&nbsp;&nbsp;&nbsp;
	Esilehel:	<input type='checkbox' name="esilehel" value=1 {VAR:esilehel}>&nbsp;&nbsp;&nbsp;
	Esilehel tulbas:	<input type='checkbox' name="frontpage_left" value=1 {VAR:frontpage_left}>&nbsp;&nbsp;&nbsp;
	Cache otsingu jaoks: <input type='checkbox' name='dcache' value=1 {VAR:dcache}>&nbsp;&nbsp;
	-->
		</td>
	</tr>
</table>

</td>
</tr>

<script language="javascript">
function doSubmit()
{
	document.doc.submit();
	return true;
}
</script>

<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Pealkiri&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="title" size="80" value="{VAR:title}"></td>
</tr>


<tr>
<td class="celltext"><img src='{VAR:baseurl}/images/transa.gif' width=110 height=1><Br><B>&nbsp;Autor:&nbsp;</b></td>
<td class="celltext"><input class='tekstikast' type="text" name="author" size="80" value="{VAR:author}"></td>
</tr>

<tr>
<td class="celltext" valign="top"><b>&nbsp;Lead&nbsp;</b></td>
<td class="celltext">
<textarea name="lead" cols="70" rows="5">{VAR:lead}</textarea>
</td>
</tr>

<tr>
<td class="celltext" valign="top"><b>&nbsp;Sisu&nbsp;</b></td>
<td class="celltext"><textarea name="content" cols="70" rows="30">{VAR:content}</textarea>
</td>
</tr>

</table>


<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">

<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:document.doc.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save2','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save2" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:document.doc.submit();">Salvesta</a></td>
<td><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="{VAR:preview}"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('preview2','','{VAR:baseurl}/automatweb/images/blue/awicons/preview_over.gif',1)"><img name="preview2" alt="Eelvaade" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/preview.gif" width="25" height="25"></a><br><a
href="{VAR:preview}">Eelvaade</a></td>
<td><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>

</table>


		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>

<table width="100%" border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
<td>
<iframe width="100%" height="800" frameborder="0" src="{VAR:aliasmgr_link}">
</iframe>
</td>
</tr>
</table>
{VAR:reforb}
</form>
