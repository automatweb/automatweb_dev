{VAR:toolbar}
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<form action='reforb.{VAR:ext}' method=post name="add">
	<tr>
		<td class="fform">Nimi:</td><td colspan="3" class="fform"><input type='text' NAME='name' VALUE='{VAR:name}' class="formtext"></td>
	</tr>
	<tr>
		<td class="fform">Kasutaja lisamise form:</td><td colspan="3" class="fform"><select NAME='user_form[]' size="10" multiple class="small_button">{VAR:forms}</select></td>
	</tr>
	<tr>
		<td class="fform">Kasutaja otsimise form:</td><td colspan="3" class="fform"><select NAME='user_search_form' class="small_button">{VAR:search_forms}</select></td>
	</tr>
	<tr>
		<td class="fform">Root kataloogid:</td><td colspan="3" class="fform"><select multiple NAME='folder[]' size="20" class="small_button">{VAR:folders}</select></td>
	</tr>
	<!-- SUB: CHANGE -->
	<tr>
		<td colspan="4" class="fform"><Br><br>Vormide j&auml;rjekord listi liikme lisamisel:</td>
	</tr>
	<tr>
		<td class="fform">Vorm</td>
		<td class="fform"  colspan="3">Jrk</td>
	</tr>
	<!-- SUB: FORM -->
	<tr>
		<td class="fform">{VAR:form}</td>
		<td class="fform" colspan="3"><input type="text" name="jrk[{VAR:form_id}]" class="formtext" size="2" value="{VAR:jrk}"></td>
	</tr>
	<!-- END SUB: FORM -->
	<tr>
		<td colspan="4" class="fform"><br><br>Vali elemendid, mille v22rtus pannakse listi liikme objekti nimeks:</td>
	</tr>
	<tr>
		<td class="fform">Element</td>
		<td class="fform">Vali</td>
		<td class="fform">Jrk</td>
		<td class="fform">Eraldaja</td>
	</tr>
	<!-- SUB: ELEMENT -->
	<tr>
		<td class="fform">{VAR:elname}</td>
		<td class="fform"><input type="checkbox" name="name_els[]" value="{VAR:elid}" {VAR:is_name_el}></td>
		<td class="fform">
			<!-- SUB: EL_ORD -->
			<input type="textbox" name="name_els_ord[{VAR:elid}]" value="{VAR:ord}" size="5">
			<!-- END SUB: EL_ORD -->
		</td>
		<td class="fform">
			<!-- SUB: EL_SEP -->
			<input type="textbox" name="name_els_post[{VAR:elid}]" value="{VAR:post}" size="5">
			<!-- END SUB: EL_SEP -->
		</td>
	</tr>
	<!-- END SUB: ELEMENT -->

	<tr>
		<td class="fform">Element, kus on meiliaadress:</td>
		<td class="fform" colspan="3"><select name='mailto_el' class='small_button'>{VAR:mailto_el}</select></td>
	</tr>

	<!-- END SUB: CHANGE -->
</table>
{VAR:reforb}
</form>


