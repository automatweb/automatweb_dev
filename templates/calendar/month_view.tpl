<table border="0" style="border-collapse: collapse; border: 1px solid #8AAABE;" cellspacing="0">
<tr>
<!-- SUB: HEADER -->
	<!-- SUB: HEADER_CELL -->
	<th width="150" class="caldayheadday">
		{VAR:dayname}
	</th>
	<!-- END SUB: HEADER_CELL -->
<!-- END SUB: HEADER -->
</tr>
<!-- SUB: WEEK -->
<tr>
	<!-- SUB: DAY -->
	<td width="150" valign="top" style="border: 1px solid #8AAABE; background-color: #FFF;">
	<div align="right"><small><strong><a href="{VAR:daylink}">{VAR:daynum}</a></strong></small></div>
	<span style="font-size: 11px;">
	<p>
		{VAR:EVENT}
	</span>
	</td>
	<!-- END SUB: DAY -->
</tr>
<!-- END SUB: WEEK -->
</table>
