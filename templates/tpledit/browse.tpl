<table border="0" cellspacing="1" cellpadding="3" bgcolor="#CCCCCC">
<tr>
<td class="fgtitle" colspan="9"><b>{VAR:path}</b></td>
</tr>
<!-- SUB: directory -->
<tr>
<td class="fgtext" align="center"><img src="images/ftv2folderclosed.gif"></td>
<td class="fgtext" colspan="7"><a href="{VAR:dirlink}">{VAR:name}</a></td>
<td class="fgtext">{VAR:date}
</tr>
<!-- END SUB: directory -->
<tr>
<td class="fgtitle" colspan="9"><b>Failid</b></td>
</tr>
<tr>
<td class="fgtext2">ID</td>
<td class="fgtext2">Nimi</td>
<td class="fgtext2">Muudetud</td>
<td class="fgtext2">Faili suurus</td>
<td class="fgtext2">Muutja</td>
<td class="fgtext2" colspan="4" align="center">Tegevus</td>
</tr>
<!-- SUB: file -->
<tr>
<td class="fgtext">{VAR:oid}</td>
<td class="fgtext">{VAR:name}</td>
<td class="fgtext">{VAR:date}</td>
<td class="fgtext" align="right">{VAR:size}</td>
<td class="fgtext">{VAR:modifiedby}&nbsp;</td>
<td class="fgtext" align="center"><a href="{VAR:edlink}">Muuda</a></td>
<td class="fgtext" align="center">{VAR:arclink}&nbsp;</td>
<td class="fgtext" align="center"><a href="{VAR:uplink}">Upload</a></td>
<td class="fgtext" align="center"><a href="{VAR:dnlink}">Download</a></td>
</tr>
<!-- END SUB: file -->
</table>
