<!-- <hr width="100%">
<div class="{VAR:style_form_caption}">Lisa kommentaar</div> -->
<table border="0" cellspacing="0" cellpadding="3" width="100%">
<!-- SUB: ERROR -->
<tr>
<td colspan="2" style="color:red">{VAR:error_message}</td>
</tr>
<!-- END SUB: ERROR -->
<tr>
<td colspan="2" class="{VAR:style_form_caption}"><a name="kommentaar"></a>Lisa kommentaar</td>
</tr>
<tr>
<td class="{VAR:style_form_text}" nowrap width="16%">Pealkiri:</td>
<td class="{VAR:style_form_text}" align="left"><input type="text" name="name" class="{VAR:style_form_element}" value="{VAR:title}" /></td>
</tr>
<tr>
	<td class="{VAR:style_form_text}" nowrap width="16%">Autori nimi:</td>
	<!-- SUB: a_name -->
	<td class="{VAR:style_form_text}">{VAR:author}</td>
	<!-- END SUB: a_name -->
	<!-- SUB: a_name_logged -->
	<td><input type="text" name="uname" value="{VAR:author}" class="{VAR:style_form_element}"></td>
	<!-- END SUB: a_name_logged -->
</tr>
<tr>
	<td class="{VAR:style_form_text}">Autori e-mail:</td>
	<!-- SUB: a_email -->
	<td class="{VAR:style_form_text}">{VAR:author_email}</td>
	<!-- END SUB: a_email -->	
	<!-- SUB: a_email_logged -->
	<td><input type="text" name="uemail" value="{VAR:author_email}" class="{VAR:style_form_element}"></td>
	<!-- END SUB: a_email_logged -->

</tr>
<!-- SUB: IMAGE_UPLOAD_FIELD -->
<tr>
	<td class="{VAR:style_form_text}">Pilt:</td>
	<td><input type="file" name="uimage" class="{VAR:style_form_element}"></td>
</tr>
<!-- END SUB: IMAGE_UPLOAD_FIELD -->
<!-- SUB: IMAGE_VERIFICATION -->
<tr>
	<td class="{VAR:style_fotm_text}">Kontrollnumber:</td>
	<td><img src="{VAR:image_verification_url}" width="{VAR:image_verification_width}" height="{VAR:image_verification_height}" /><input type="text" name="ver_code" /></td>
</tr>
<!-- END SUB: IMAGE_VERIFICATION -->
<tr>
	<td colspan="2" class="{VAR:style_form_text}" nowrap>Kommentaar:</td>
</tr>
<tr>
	<td colspan="2" class="{VAR:style_form_text}"><textarea name="commtext" cols="40" rows="10" class="{VAR:style_form_element}" value="{VAR:commtext}"></textarea></td>
</tr>
<tr>
	<td colspan="2" class="{VAR:style_form_text}"><input type="submit" value="Lisa kommentaar"></td>
</tr>
</table>
