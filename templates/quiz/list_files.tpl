<form action=reforb.{VAR:ext} method=post enctype='multipart/form-data'>
<table border="0" cellspacing="0" cellpadding="0" width=100%>
<tr>
<td bgcolor="#CCCCCC">

<table border="0" cellspacing="1" cellpadding="2" width=100%>
<tr>
<td height="15" colspan="15" class="fgtitle">&nbsp;<b>TESTID:&nbsp;<a href='{VAR:upload}'>Uploadi faile</a> | Nimekiri failidest, lehek&uuml;lg: 
<!-- SUB: PAGE -->
<a href='{VAR:list}'>{VAR:from} - {VAR:to}</a> | 
<!-- END SUB: PAGE -->
<!-- SUB: SEL_PAGE -->
{VAR:from} - {VAR:to} | 
<!-- END SUB: SEL_PAGE -->
</b></td>
</tr>

<tr>
	<td class="title">Faili nimi</td>
	<td class="title">Men&uuml;&uuml;1</td>
	<td class="title">Men&uuml;&uuml;2</td>
	<td class="title">Men&uuml;&uuml;3</td>
	<td class="title">Number</td>
	<td class="title">Raskus</td>
	<td class="title">Eksam?</td>
	<td class="title">T&uuml;&uuml;p</td>
	<td class="title">&Otilde;petaja</td>
	<td class="title">Tegevus</td>
</tr>

<!-- SUB: LINE -->
<tr>
	<td class="plain"><a href='{VAR:baseurl}/files.{VAR:ext}/id={VAR:fid}/{VAR:file}' target='_new'>{VAR:file}</a></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='menu1[{VAR:id}]' value='{VAR:menu1}'></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='menu2[{VAR:id}]' value='{VAR:menu2}'></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='menu3[{VAR:id}]' value='{VAR:menu3}'></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='number[{VAR:id}]' value='{VAR:number}'></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='raskus[{VAR:id}]' value='{VAR:raskus}'></td>
	<td class="plain"><input class='small_button' type='checkbox' name='exam[{VAR:id}]' value='1' {VAR:exam}></td>
	<td class="plain"><input class='small_button' type='text' size=2 name='type[{VAR:id}]' value='{VAR:type}'></td>
	<td class="plain"><input class='small_button' type='text' size=10 name='teacher[{VAR:id}]' value='{VAR:teacher}'></td>
	<td class="plain"><a href='{VAR:delete}'>Kustuta</a></td>
</tr>
<!-- END SUB: LINE -->

</table></td></tr></table>
<input type='submit' value='Salvesta'>
{VAR:reforb}
</form>
