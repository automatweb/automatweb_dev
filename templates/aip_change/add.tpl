
<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}' name="imageadd">


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
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.imageadd.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_MENUEDIT_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.imageadd.submit();">Salvesta</a>
</td></tr>
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

















<input type="hidden" name="MAX_FILE_SIZE" value="1000000">

<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td align=center>


<table border=0 cellspacing=1 cellpadding=1>
<tr>
	<td class="celltext" align="right">Nimi:</td>
	<td class="celltext"><input type="text" name="name"  value="{VAR:name}" class="formtext" ></td>
</tr>
<tr>
	<td class="celltext" align="right">T&uuml;&uuml;p:</td>
	<td class="celltext"><select name="type"  class="formselect">{VAR:types}</select></td>
</tr>
<tr>
	<td class="celltext" align="right">J&otilde;ustumise kuup&auml;ev:</td>
	<td class="celltext">{VAR:act_time}</td>
</tr>
<tr>
	<td colspan="2" class="celltext" align="left">Failid mis muudatuse hulka kuuluvad:</td>
</tr>
<tr>
	<td colspan="2" class="celltext" align="left"><select class='formselect' name='files[]' size="20" multiple>{VAR:files}</select></td>
</tr>
</table>
	{VAR:reforb}


	</td>
</tr>
</table>
</form>
