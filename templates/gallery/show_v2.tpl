<table width="100%" border="0" cellpadding="2" cellspacing="2">
	<tr>
		<td BGCOLOR="#FCF3DC" colspan="{VAR:num_cols}">
			<table width="100%" border="0" cellpadding="2" cellspacing="0">
				<tr>
					<td BGCOLOR="#FCF3DC" class="textpealkiri">{VAR:name}</td>
					<td align="right" class="textmiddle">
						<!-- SUB: PAGESEL_BACK -->
						<a href="{VAR:link}"><</a>
						<!-- END SUB: PAGESEL_BACK -->
					
						<!-- SUB: SEL_PAGE -->
						 <b>{VAR:page_num}</b> 
						<!-- END SUB: SEL_PAGE -->
	
						<!-- SUB: PAGE -->
						<a href="{VAR:link}"><b>{VAR:page_num}</b></a> 
						<!-- END SUB: PAGE -->


						<!-- SUB: PAGE_SEP -->
						|
						<!-- END SUB: PAGE_SEP -->

						<!-- SUB: PAGESEL_FWD -->
            			<a href="{VAR:link}">></a>
						<!-- END SUB: PAGESEL_FWD -->
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td bgcolor="#FFFFFF" colspan="{VAR:num_cols}">
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td class="textmiddle">
					<!-- SUB: RATE_OBJ -->
						<a href="{VAR:link}">{VAR:name}</a> 
					<!-- END SUB: RATE_OBJ -->

					<!-- SUB: RATE_OBJ_SEP -->
					|
					<!-- END SUB: RATE_OBJ_SEP -->
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{VAR:layout}
	<tr>
		<td bgcolor="#FFFFFF" colspan="{VAR:num_cols}">
			<table width="100%" border="0" cellpadding="2" cellspacing="0">
				<tr>
					<td class="aa_weekday">&nbsp; </td>
					<td align="right" class="textmiddle">{VAR:PAGESEL_BACK} {VAR:PAGE} {VAR:PAGESEL_FWD}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
