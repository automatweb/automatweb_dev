<form action='reforb.{VAR:ext}' method=post name="add">

<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside" height="29">


<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td width="5"><IMG SRC="images/trans.gif" WIDTH="5" HEIGHT="1" BORDER=0 ALT=""></td>
<td>


<table border="0" cellpadding="0" cellspacing="0">
<tr><td class="icontext" align="center"><input type='image' src="{VAR:baseurl}/automatweb/images/blue/big_save.gif" width="32" height="32" border="0" VALUE='submit' CLASS="small_button"><br>
<a href="javascript:document.add.submit()">Salvesta</a></td></tr></table>


</td>
</tr>
</table>


<br>


<table class="aste01" cellpadding=3 cellspacing=1 border=0>
<tr>
<td class="celltext">Nimi:</td><td class="celltext"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
</tr>
<tr>
<td class="celltext">Formi tabel, millega korvi kuvatakse:</td><td class="celltext"><select NAME='ftbl' class="formselect">{VAR:ftbls}</select></td>
</tr>
<tr>
<td class="celltext">Kuhu salvestatakse selle korvi tellimused:</td><td class="celltext"><select NAME='ord_parent' class="formselect">{VAR:ord_parents}</select></td>
</tr>
<tr>
<td class="celltext">Kuhu suunatakse p&auml;rast tellimuse tegemist:</td><td class="celltext"><input type='text' NAME='after_order' VALUE='{VAR:after_order}' class="formtext"></td>
</tr>
<tr>
<td class="celltext">E-mailiaadressid (eralda komaga):</td><td class="celltext"><input type='text' NAME='mail_to' VALUE='{VAR:mail_to}' class="formtext"></td>
</tr>
<tr>
<td class="celltext">Form, millega tellimus sooritatakse:</td><td class="celltext"><select NAME='order_form' class="formselect">{VAR:order_form}</select></td>
</tr>
<tr>
<td class="celltext">V&auml;ljund mida n&auml;idatakse e-mailis:</td><td class="celltext"><select NAME='order_form_op' class="formselect">{VAR:order_form_op}</select></td>
</tr>
<tr>
<td class="celltext">Formi tabel mida n&auml;idatakse e-mailis:</td><td class="celltext"><select NAME='order_ftbl' class="formselect">{VAR:order_ftbl}</select></td>
</tr>
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


