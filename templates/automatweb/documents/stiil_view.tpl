<IMG SRC="{VAR:baseurl}/img/trans.gif" border="0" width="1" height="15" alt=""><br>
<span style="position:relative"><span id="element11"><!-- {VAR:parent_name} --></span><span id="element2"><font class="pealkiri">{VAR:title}</font></span><img src="{VAR:baseurl}/gfx/shim.gif" height="22" width="1" valign="bottom"> </span> 
      <p> 
<font class="txt">

<table width="315" border="0" cellpadding="0" cellspacing="0">
<tr><td>
<div align="right" class="smalltext">
<!-- SUB: ablock -->
<b>Tekst <a href="asd" class="autor">{VAR:author}</a></b><br>
<!-- END SUB: ablock -->
<!-- SUB: pblock -->
<b>Foto <a href="asdd" class="autor">{VAR:photos}</a></b>
<!-- END SUB: pblock -->
</div><br>
</td></tr></table>

</font>
<p><font class="txt">{VAR:text}</font></p>
<!-- SUB: FORUM_ADD -->
<p>
<font class="txt">
<b>Sellel artiklil on <a href="{VAR:baseurl}/comments.{VAR:ext}?section={VAR:docid}&forum_id=11030" title="Loe kommentaare!">{VAR:num_comments}</a> kommentaari</b>
</p>
</font>
<table border="0" cellpadding="4" cellspacing="0">
	<tr>  <form method="post" action="{VAR:baseurl}/refcheck.{VAR:ext}">
		<td><img src="{VAR:baseurl}/gfx/shim.gif" width="30" height="1"></td>
		<td><img src="{VAR:baseurl}/gfx/shim.gif" width="152" height="1"></td>
		<td><img src="{VAR:baseurl}/gfx/shim.gif" width="33" height="1"></td>
		<td><img src="{VAR:baseurl}/gfx/shim.gif" width="122" height="1"></td>
	</tr>
	<tr> 
		<td class="txt" colspan="2"><font class="txt"><span class="boldhead">Lisa kommentaar:</span> </font> </td>
		<td align="right" class="txt">Nimi:&nbsp;</td>
		<td align="right"> <input type="text" name="from" class="frm" size="16">
		</td>
	</tr>
	<tr bgcolor="#E1E6EB"> 
		<td class="txt" colspan="2" valign="bottom">&nbsp;</td>
		
			<td align="right" class="txt">E-mail:</td>
			<td align="right"> 
				<input type="text" name="email" class="frm" size="16">
			</td>
		
	</tr>
	<tr bgcolor="#E1E6EB"> 
	 
			<td align="center" class="date" colspan="4" valign="middle"> 
				<textarea name="comment" cols="42" rows="6" style=" width: 410; height: 100; font-family: Arial;"></textarea>
			</td>
		
	</tr>
	<tr bgcolor="#E1E6EB"> 
		<td colspan="4" valign="bottom" height="28" align="right"> 
			<input type="submit" name="Submit2" value="Lisa kommentaar" class="btn">
		</td>
	</tr>
</table>
	<input type='hidden' NAME='action' VALUE='addcomment'>
	<input type='hidden' NAME='parent' VALUE='0'>
	<input type='hidden' NAME='section' VALUE='{VAR:docid}'>
	<input type='hidden' NAME='page' VALUE='0'>
	<input type='hidden' NAME='subj' VALUE='&nbsp;'>
</form></font>
<!-- END SUB: FORUM_ADD -->
      <font class="txt"> 
      <br>
        <a href="javascript:window.history.go(-1)" class="punane">tagasi</a><br>
        <br>
      <p></p>
      </font>

<!-- SUB: image -->

<br><table border="0" cellpadding="1" cellspacing="0" {VAR:align}>
<tr><td bgcolor="#DF0029"><IMG src="{VAR:imgref}" BORDER=0 ALT=""></td><td width="5"></td></tr>
<tr><td class="smalltext">{VAR:imgcaption}</td><td width="5"></td></tr>
</table>
<!-- END SUB: image -->

<!-- SUB: image_linked -->

<br><table border="0" cellpadding="1" cellspacing="0" {VAR:align}>
<tr><td bgcolor="#DF0029"><a {VAR:target} href="{VAR:plink}"><IMG src="{VAR:imgref}" BORDER=0 ALT=""></a></td><td width="5"></td></tr>
<tr><td class="smalltext">{VAR:imgcaption}</td><td width="5"></td></tr>
</table>
<!-- END SUB: image_linked -->

<!-- SUB: link -->
<span class="smalltext"><a {VAR:target} href="{VAR:url}" class="autor">{VAR:caption}</a></span>
<!-- END SUB: link -->