<font size="+1"><b>{VAR:name}</b></font><br>
<!-- SUB: EXTENDER -->
{VAR:spacer}<img src='{VAR:baseurl}/automatweb/images/inherit.gif'><a href='{VAR:inh_link}'>{VAR:inh_name}</a><br>
<!-- END SUB: EXTENDER -->

<br>
Functions: <br>
<br>
<!-- SUB: FUNCTION -->
proto: {VAR:proto} <br>
<br>
<table border="1">
<tr>
	<td>name: </td><td>{VAR:name}</td>
</tr>
<tr>
	<td>start line:</td><td>{VAR:start_line}</td>
</tr>
<tr>
	<td>end line:</td><td>{VAR:end_line}</td>
</tr>
<tr>
	<td>returns reference:</td><td>{VAR:returns_ref}</td>
</tr>
</table>

<br>
arguments: <br>
<table border="1">
	<tr>
		<td>Name</td>
		<td>default value</td>
		<td>is reference</td>
	</tr>

<!-- SUB: ARG -->
	<tr>
		<td>{VAR:arg_name}&nbsp;</td>
		<td>{VAR:def_val}&nbsp;</td>
		<td>{VAR:is_ref}&nbsp;</td>
	</tr>
<!-- END SUB: ARG -->
</table>
<hr>
<!-- END SUB: FUNCTION -->
