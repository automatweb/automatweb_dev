
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<td class="cal_sub2">
   <div class="cal_month_name"><a href="{VAR:prev_month_url}"><img src="/img/noolback.gif" border="0"></a> {VAR:begin_month_name} {VAR:begin_year} <a href="{VAR:next_month_url}"><img src="/img/noolforw.gif" border="0"></a></div>
   <hr class="cal_hr">
   
   <!-- SUB: next_weeks --> 
   	<a href="{VAR:week_url}">{VAR:week_nr}. nädal</a> - 
   <!-- END SUB: next_weeks -->
   
   <!-- SUB: next_weeks_end --> 
   	<a href="{VAR:week_url}">{VAR:week_nr}. nädal</a>
   <!-- END SUB: next_weeks_end -->
  </td>
</table>

<table border="0" cellpadding="4" width="100%">
<tbody>
<!-- SUB: COLHEADER -->
<th class="cal_tulp">
	{VAR:colcaption}
</th>
<!-- END SUB: COLHEADER -->
	</tr>
	<!-- SUB: EVENT -->
	<tr class="cal_rida{VAR:fuck}">
	<!-- SUB: CELL -->
		<td>{VAR:cell}</td>
	<!-- END SUB: CELL -->
<!--
	<tr><td colspan="4" class="cal_rida{VAR:fuck}">
		MÄRKUS: {VAR:fulltext}
	</td></tr>
-->
	</tr>
<!-- END SUB: EVENT -->
<tbody>
</table>
