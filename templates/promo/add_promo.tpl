<form action='reforb.{VAR:ext}' method=post name="addpromokast">
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
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.addpromokast.submit();" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.addpromokast.submit();" >Salvesta</a></td>
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>



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


<table cellpadding=5 cellspacing=0 border=0 width="100%">
<tr>
<td colspan="2" class="celltext" valign="top"><b>{VAR:LC_PROMO_CHOOSE_SECTION}:</b></td>
</tr>
<tr>
<td colspan="2"><SELECT NAME='section[]' SIZE=10 class='small_button' MULTIPLE>{VAR:section}</select></td>
</tr>
<tr>
<td width="50%" valign="top">

	<table border="0" cellpadding="3" cellspacing="0">
		<tr>
		<td class="celltextbold" width="250" align="right">Nimi:</td><td width="50%"><input type='text' class="formtext" NAME='title' VALUE='{VAR:title}' size="40"></td>
		</tr>
		<tr>
		<td class="celltextbold" align="right">Pealkiri:</td><td><input type='text' class="formtext" NAME='comment' VALUE='{VAR:comment}' size="40"></td>
		</tr>
		<tr>
		<td class="celltextbold" align="right">{VAR:LC_PROMO_LINK}:</td><td><input type='text' class="formtext" NAME='link' VALUE='{VAR:link}' 	size="40"></td>
		</tr>
		<tr>
		<td class="celltextbold" align="right">Lingi kirjeldus:</td><td><input type='text'  class="formtext" NAME='link_caption' VALUE='{VAR:link_caption}' size="40"></td>
		</tr>
		<tr>
		<td class="celltext" align="right">{VAR:LC_PROMO_WHTOUT_TITLE}:</td><td><input type='checkbox' NAME='no_title' VALUE='1' {VAR:no_title}></td>
		</tr>
		
	</table>
</td>
<td width="50%" valign="top">

<table border="0" cellpadding="3" cellspacing="0"><tr><td width="100"  valign="top">
	<table border="0" cellpadding="3" cellspacing="0">
		<tr class="aste07">
		<td class="celltext" align="right">{VAR:LC_PROMO_TEMPLATE_FOR_CHANGE}</td><td><select name="tpl_edit" class="small_button">{VAR:tpl_edit}</select></td>
		</tr>
		<tr class="aste07">
			<td class="celltext" align="right" valign="top">Vali grupid kellele promo kasti n&auml;idatakse:</td>
			<td class="celltext"><select name="groups[]" class="small_button" multiple size="10">{VAR:groups}</select></td>
		</tr>

		<tr class="aste07">
		<td class="celltext" align="right">{VAR:LC_PROMO_TEMPLATE_FOR_SHOW}</td><td>
		<select name="tpl_lead" class="small_button">
		<option value="0">Default</option>
		{VAR:tpl_lead}
		</select>
		</td>
		</tr>


	</table>
</td><td valign="top">


	<table border="0" cellpadding="3" cellspacing="0">
		<tr class="aste05">
		<td class="celltext" colspan=2><b>{VAR:LC_PROMO_BOX_TYPE}:</b></td>
		</tr>
		<tr class="aste07">
		<td class="celltext" align="right">{VAR:LC_PROMO_AT_RIGHT}:</td><td><input type='radio' NAME='right' VALUE='1' {VAR:right_sel}></td>
		</tr>
		<tr class="aste07">
		<td class="celltext" align="right">{VAR:LC_PROMO_AT_LEFT}:</td><td><input type='radio' NAME='right' VALUE='0' {VAR:left_sel}></td>
		</tr>
		<tr class="aste07">
		<td class="celltext" align="right">&Uuml;leval:</td><td><input type='radio' NAME='right' VALUE='2' {VAR:up_sel}></td>
		</tr>
		<tr class="aste07">
		<td class="celltext" align="right">All:</td><td><input type='radio' NAME='right' VALUE='3' {VAR:down_sel}></td>
		</tr>
		<tr class="aste07">
		<td class="celltext" align="right">Skrolliv:</td><td><input type='radio' NAME='right' VALUE='scroll' {VAR:scroll_sel}></td>
		</tr>
	</table>

</td></tr></table>

</td>
</tr>
<tr>
	<td colspan=2 class="celltext"><b>Vali men&uuml;d, mille alt viimaseid dokumente n&auml;idatakse:</b></td>
</tr>
<tr>
	<td colspan=2 class="celltext"><select size="10" name="last_menus[]" multiple class="small_button">{VAR:last_menus}</select></td>
</tr>
<tr>
	<td class="celltext" colspan="2">Mitu viimast dokumenti:<input type="text" class="formtext" size="2" class="small_button" name="num_last" value='{VAR:num_last}'></td>
</tr>
<!--<tr>
<td class="celltext" colspan=2><input type='submit' VALUE='{VAR:LC_PROMO_SHOW}' CLASS="formbutton"></td>
</tr>-->
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
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.addpromokast.submit();" 
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.addpromokast.submit();" >Salvesta</a></td>
<td><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="10" HEIGHT="2" BORDER=0 ALT=""></td>



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

{VAR:reforb}
</form>
