<BODY bgcolor="#F0F5F8" link="#002E73" alink="#002E73" vlink="#B4CFDC" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
<table border=0 width="100%" cellspacing="0" cellpadding="0">
<tr>
<td align="left" class="yah">&nbsp;
<a href="{VAR:baseurl}/index.aw?section={VAR:rootmenu}&aip=1">AIP</a>
<!-- SUB: YAH_LINK -->
/ <a href="{VAR:baseurl}/index.aw?section={VAR:parent}&aip=1"><b>{VAR:pre}</b> {VAR:name}</a>
<!-- END SUB: YAH_LINK -->
/ <a href='{VAR:changes}'>Muudatused</a> / Lisa muudatus
</td>
<td align="right" class="yah">{VAR:date}&nbsp;&nbsp;</td>
</tr>
<form enctype="multipart/form-data" method=POST action='reforb.{VAR:ext}' name="q">
</table>


{VAR:toolbar}
<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr>
<td class="filestableborder">


<table width="100%" border="0" cellpadding="3" cellspacing="0">









<input type="hidden" name="MAX_FILE_SIZE" value="1000000">

<tr class="filestablefoldersback">
	<td class="text" align="right">Nimi:</td>
	<td class="text"><input type="text" name="name"  value="{VAR:name}" class="formtext" ></td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">Kommentaar:</td>
	<td class="text"><textarea name="comment" cols="30" rows="5" class="formtext">{VAR:comment}</textarea></td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">T&uuml;&uuml;p:</td>
	<td class="text"><select name="type"  class="formselect">{VAR:types}</select></td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">Avaldamise kuup&auml;ev:</td>
	<td class="text">{VAR:act_time}(muudatus aktiveerub avaldamise kuup&auml;eval!)</td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">J&otilde;ustumise kuup&auml;ev:</td>
	<td class="text">{VAR:j_time} </td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">Muudatuse pdf:</td>
	<td class="text"><input type='file' class='formfile' name='change_pdf_1'> 
<!-- SUB: IS_PDF1 -->
		<a href='{VAR:cur_pdf_1}'>Vaata</a> <input type='checkbox' name="del_chp_1" value="1"> Kustuta
<!-- END SUB: IS_PDF1 -->
	</td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">Muudatuse pdf:</td>
	<td class="text"><input class='formfile' type='file' name='change_pdf_2'> 
<!-- SUB: IS_PDF2 -->
		<a href='{VAR:cur_pdf_2}'>Vaata</a> <input type='checkbox' name="del_chp_2" value="1"> Kustuta
<!-- END SUB: IS_PDF2 -->
	</td>
</tr>
<tr class="filestablefoldersback">
	<td class="text" align="right">Muudatuse pdf:</td>
	<td class="text"><input class='formfile' type='file' name='change_pdf_3'> 
<!-- SUB: IS_PDF3 -->
		<a href='{VAR:cur_pdf_3}'>Vaata</a> <input type='checkbox' name="del_chp_3" value="1"> Kustuta
<!-- END SUB: IS_PDF3 -->
	</td>
</tr>
<tr class="filestablefoldersback">
	<td colspan="2" class="text" align="left">Failid mis muudatuse hulka kuuluvad:</td>
</tr>
<tr class="filestablefoldersback">
	<td colspan="2" class="text" align="left"><select class='formselect' name='files[]' size="20" multiple>{VAR:files}</select></td>
</tr>
</table>
	{VAR:reforb}


	</td>
</tr>
</table>
</form>
