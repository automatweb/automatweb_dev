<table hspace=0 vspace=0 cellpadding=3  bgcolor=#a0a0a0>
	<tr>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=change_table&id={VAR:parent}'>Toimeta</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=settings&id={VAR:parent}'>M&auml;&auml;rangud</a></td>
		<td bgcolor=#a0a0a0><a href='tables.phtml?type=change_styles&parent={VAR:parent}'>Stiiliraamat</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=show_table&id={VAR:parent}'>Eelvaade</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=image_list&parent={VAR:parent}'>Pildid</a></td>
		<td bgcolor=#f0f0f0><a href='tables.phtml?type=delete_table&id={VAR:parent}'>Kustuta</a></td>
	</tr>
</table>
<br>
<table border=0 cellspacing=1 bgcolor=#cccccc cellpadding=2>
	<tr>
		<td class=title>Nimi</td>
		<td class=title colspan=2 align=center>Tegevus</td>
	</tr>
	<!-- SUB: LINE -->
		<tr>
			<td class=plain >{VAR:style_name}</td>
			<td class=plain><a href='tables.phtml?type=change_style&id={VAR:style_id}&parent={VAR:parent}'>Muuda</a></td>
			<td class=plain><a href='tables.phtml?type=delete_style&id={VAR:style_id}&parent={VAR:parent}'>Kustuta</a></td>
		</tr>
	<!-- END SUB: LINE -->
	<tr>
		<td class=plain colspan=3 align=center><a href='tables.phtml?type=add_style&parent={VAR:parent}'>Lisa</a></td>
	</tr>
</table>

	