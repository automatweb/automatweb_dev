<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<form method="POST" action="reforb.{VAR:ext}" name="htmlpopup">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">
<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.htmlpopup.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.htmlpopup.submit();">Salvesta</a>
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



<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td align=center>


<table border=0 cellspacing=1 cellpadding=1>
<tr>

<td class="celltext">Nimi:</td>
<td class="celltext"><input type="text" name="name" size="40" value="{VAR:name}" class="formtext"></td>
</tr>
<tr>
<td class="celltext">Sisu (URL):</td>
<td class="celltext"><input type="text" name="url" size="40" value="{VAR:url}" class="formtext"></td>
</tr>
<tr>
<td class="celltext">Mõõtmed:</td>
<td class="celltext">
	Laius: <input type="width" name="width" size="4" value="{VAR:width}">
	Kõrgus: <input type="height" name="height" size="4" value="{VAR:height}">	
	</td>
</tr>
<tr>
<td class="celltext" valign="top">Menüüd:</td>
<td class="celltext">
<select size="30" name="menus[]" multiple class="formselect2">
{VAR:menus}
</select>
</td>
</tr>

<!--<input type="submit" value="Salvesta">-->
{VAR:reforb}


</table>
</td></tr></table>
</form>