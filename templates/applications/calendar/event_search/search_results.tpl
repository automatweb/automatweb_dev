
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<td class="cal_sub2">
   <div class="cal_month_name"><a href="{VAR:prev_month_url}"><img src="/img/noolback.gif" border="0"></a> {VAR:begin_month_name} {VAR:begin_year} <a href="{VAR:next_month_url}"><img src="/img/noolforw.gif" border="0"></a></div>
   <hr class="cal_hr">
   <!-- SUB: next_weeks --> 
   	<a href="{VAR:week_url}">{VAR:week_nr}. nädal</a> - 
   <!-- END SUB: next_weeks -->
    <!-- SUB: next_weeks_b --> 
   	<a href="{VAR:week_url}"><b>{VAR:week_nr}. nädal</b></a> - 
   <!-- END SUB: next_weeks_b -->
   
   <!-- SUB: next_weeks_end --> 
   	<a href="{VAR:week_url}">{VAR:week_nr}. nädal</a>
   <!-- END SUB: next_weeks_end -->
      <!-- SUB: next_weeks_end_b --> 
   	<a href="{VAR:week_url}"><b>{VAR:week_nr}. nädal</b></a>
   <!-- END SUB: next_weeks_end_b -->
  </td>
</table>

<table border="0" cellpadding="4" width="100%" cellspacing="0">
<tbody>
<!-- SUB: COLHEADER -->
<th class="cal_tulp">
	{VAR:colcaption}
</th>
<!-- END SUB: COLHEADER -->
	<!-- SUB: BLOCK -->
	<tr>
	<td class="cal_tulp" colspan="{VAR:col_count}" style="background-color:#0366a1;font-size:14px;color:#ffffff">
	{VAR:block_caption}
	</td>
	</tr>
	<!-- END SUB: BLOCK -->
	<!-- SUB: EVENT -->
	<tr class="cal_rida{VAR:num}">
	<!-- SUB: CELL -->
		<td>{VAR:cell}</td>
	<!-- END SUB: CELL -->
	</tr>
	<!-- SUB: FULLTEXT -->
	<tr><td colspan="{VAR:col_count}" class="cal_rida{VAR:num}">
		<!-- {AR:fulltext_name}: -->{VAR:fulltext}
	</td></tr>
	<!-- END SUB: FULLTEXT -->
<!-- END SUB: EVENT -->
<tbody>
</table>