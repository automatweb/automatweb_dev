<!-- SUB: group -->
<div style="margin-left: 10px; margin-top: 10px;">
Grupp: <input type="text" name="grpnames[{VAR:grpid}]" value="{VAR:grp_caption}" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC; font-weight: strong;">
</div>
	<div style="margin-left: 50px; margin-top: 5px;" >
	<table border="1" style="border-collapse: collapse; font-size: 11px; border-color: #CCC;" cellpadding="3px">
	<!-- SUB: property -->
	<tr>
		<td width="50"><input type="text" name="prop_ord[{VAR:prp_key}]" value="{VAR:prp_order}" size="2" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="100">{VAR:prp_key}</td>
		<td width="150"><input type="text" name="prpnames[{VAR:prp_key}]" value="{VAR:prp_caption}" style="border: 1px solid #EEE; padding: 2px; background-color: #FCFCEC;"></td>
		<td width="100">{VAR:prp_type}</td>
		<td width="30" align="center"><input type="checkbox" name="mark[{VAR:prp_key}]" value="1" style="border: 3px solid blue;"></td>
	</tr>
	<!-- END SUB: property -->
	</table>
	</div>
<!-- END SUB: group -->

