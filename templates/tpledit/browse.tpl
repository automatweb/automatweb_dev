<table border="0" cellspacing="1" cellpadding="3" bgcolor="#CCCCCC">
<tr>
<td class="fgtitle" colspan="8"><b>{VAR:path}</b></td>
</tr>
<!-- SUB: directory -->
<tr>
<td class="fgtext"><img src="images/ftv2folderclosed.gif"></td>
<td class="fgtext" colspan="6"><a href="{VAR:dirlink}">{VAR:name}</a></td>
<td class="fgtext">{VAR:date}
</tr>
<!-- END SUB: directory -->
<tr>
<td class="fgtitle" colspan="8"><b>Failid</b></td>
</tr>
<tr>
<td class="fgtext2">Nimi</td>
<td class="fgtext2">Muudetud</td>
<td class="fgtext2">Faili suurus</td>
<td class="fgtext2">Muutja</td>
<td class="fgtext2" colspan="4" align="center">Tegevus</td>
</tr>
<!-- SUB: file -->
<tr>
<td class="fgtext">{VAR:name}</td>
<td class="fgtext">{VAR:date}</td>
<td class="fgtext">{VAR:size}</td>
<td class="fgtext">{VAR:modifiedby}&nbsp;</td>
<td class="fgtext" align="center"><a href="{VAR:edlink}">Muuda</a></td>
<td class="fgtext" align="center"><a href="{VAR:arclink}">Arhiiv</a></td>
<td class="fgtext" align="center"><a href="{VAR:uplink}">Upload</a></td>
<td class="fgtext" align="center"><a href="{VAR:dnlink}">Download</a></td>
</tr>
<!-- END SUB: file -->
</table>
