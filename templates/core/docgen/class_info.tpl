<style type="text/css">

.text {
	font-family:  Verdana, Arial, sans-serif;
	font-size: 11px;
	color: #000000;
	line-height: 18px;
	text-decoration: none;
}

.text a {
	color: #058AC1; 
	text-decoration:underline;
}

.text a:hover {
	color: #000000; 
	text-decoration: underline;
}
p {
	margin-left: 30px;
}

table {
	font-size: inherit;
}

table.class_info {
	width: 50%;
	border-collapse: collapse;
	margin-left: 30px;
}

table.class_info td {
	border: 1px solid silver;
	padding: 2px;
}

table.class_info th {
	border: 1px solid silver;
	background-color: silver;
}
</style>

<a href="{VAR:view_class}">class source</a>

<table border="0" width="100%" cellpadding="2" cellspacing="0">
<tr>
	<td valign="top" rowspan="2" class="text" width="50%">
		<b>{VAR:name}</b><br>
		<!-- SUB: EXTENDER -->
		{VAR:spacer}<img src='{VAR:baseurl}/automatweb/images/inherit.gif'><a href='{VAR:inh_link}'>{VAR:inh_name}</a><br>
		<!-- END SUB: EXTENDER -->
	</td>
	<td rowspan="2" width="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
	<td valign="top"  width="50%" class="text"><B>dependencies</b></td>
</tr>
<tr>


	<td class="text" valign="top" >
		
		<!-- SUB: DEP -->
		&nbsp; - <a href='{VAR:link}'>{VAR:name}</a><br>
		<!-- END SUB: DEP -->

		<!-- SUB: VAR_DEP -->
		&nbsp; - This class also has variable dependencies!<br>
		<!-- END SUB: VAR_DEP -->
		</td>
</tr>
<tr>
		<td colspan="3" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>

<tr>
	<td colspan="2" rowspan="4" class="text" valign="top">
		<b>functions:</b><br>
		<!-- SUB: FUNCTION -->
		<a href='{VAR:view_func}'>{VAR:name}</a><br>
		<i>{VAR:short_comment}</i>
		<!-- END SUB: FUNCTION -->

		<br><br>
		<B>orb methods</b><br>
		<b>class_base methods</b><br>
		<b>other methods</b><br>
	</td>
	<td class="text" valign="top">
		<b>properties:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
		<tr>
			<td class="text"><i>Name</i></td>
			<td class="text"><i>Type</i></td>
			<td class="text"><i>Comment</i></td>
		</tr>
		<!-- SUB: PROP -->
		<tr>
			<td class="text">{VAR:name}</td>
			<td class="text">{VAR:type}</td>
			<td class="text">{VAR:comment}</td>
		</tr>
		<!-- END SUB: PROP -->
		</table>
	</td>
</tr>
<tr>
	<td class="text" valign="top">
		<b>Reltypes:</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<td class="text"><i>Name</i></td>
			<td class="text"><i>Classes</i></td>
			<td class="text"><i>Comment</i></td>
		<!-- SUB: RELTYPE -->
		<tr>
			<td class="text">{VAR:name}</td>
			<td class="text">{VAR:clids}</td>
			<td class="text">{VAR:comment}</td>
		</tr>
		<!-- END SUB: RELTYPE -->
		</table>
	</td>


</tr>
<tr>

	<td class="text" valign="top">
		<b>Database tables</b><br>
		<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<td class="text"><i>Name</i></td>
			<td class="text"><i>Index</i></td>
			<td class="text"><i>Properties</i></td>
		<!-- SUB: TABLE -->
		<tr>
			<td class="text">{VAR:name}</td>
			<td class="text">{VAR:index}</td>
			<td class="text">{VAR:properties}</td>
		</tr>
		<!-- END SUB: TABLE -->
		</table>
	</td>


</tr>
<tr>

	<td class="text" valign="top">
		<b>Defines:</b><br>

		<br>
		<B>Templates:</b><br>
	</td>


</tr>
</table>

<br><br><br>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
<!-- SUB: LONG_FUNCTION -->
<tr>
		<td colspan="6" width="2" height="2" bgcolor="#000000"><img src='{VAR:baseurl}/automatweb/images/trans.gif'></td>
</tr>
<tr>
	<td class="text" colspan="6">
		<table border="0" width="100%">
			<tr>
				<td class="text"><a name='fn.{VAR:name}'></a><b>{VAR:proto}</b> - <a href='{VAR:view_usage}'>View usage</a> - <a href='{VAR:view_source}'>View source</a></td>
			</tr>
			<tr>
				<td class="text">
					<i>{VAR:short_comment}</i><br>
					<strong>Attributes:</strong>
					<table class="class_info">
						<tr>
							<th>Name</th>
							<th>Value</th>
						</tr>
					<!-- SUB: ATTRIB -->
						<tr>
							<td>{VAR:attrib_name}</td>
							<td>{VAR:attrib_value}</td>
						</tr>
					<!-- END SUB: ATTRIB -->
					</table>
					<strong>Parameters:</strong>
					<table class="class_info">
						<tr>
							<th>Name</th>
							<th>Required</th>
							<th>Type</th>
							<th>Comment</th>
						</tr>
					<!-- SUB: PARAM -->
						<tr>
							<td>{VAR:param_name}</td>
							<td>{VAR:param_required}</td>
							<td>{VAR:param_type}</td>
							<td>{VAR:param_comment}</td>
						</tr>
					<!-- END SUB: PARAM -->
					</table>

					<strong>Returns:</strong>
					<p class="returns">{VAR:returns}</p>
					<strong>Errors:</strong>
					<p class="errors">{VAR:errors}</p>
					<strong>Examples:</strong>
					<p class="examples">{VAR:examples}</p>
				</td>
			</tr>
			<tr>
				<td class="text">{VAR:doc}</td>
			</tr>
		</table>
	</td>
</tr>
<!-- END SUB: LONG_FUNCTION -->
</table>
