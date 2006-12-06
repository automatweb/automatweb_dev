<html>
	<head>
		{VAR:HTML_HEAD_HTML}
	</head>
	<form method="post" action="" enctype=multipart/form-data" name="xmlsrc">
	{VAR:HTML_FORM_BEGIN_HTML}
		<table>
			<tr>
				<td>
					Linn
				</td>
				<td>
					<input type="text" name="city"/>
				</td>
			</tr>
			<tr>
				<td>
					Maakond
				</td>
				<td>
					<input type="text" name="state"/>
				</td>
			</tr>
			<tr>
				<td>
					Postiindeks
				</td>
				<td>
					<input type="text" name="postalcode"/>
				</td>
			</tr>
			<tr>
				<td>
					Riik
				</td>
				<td>
					<input type="text" name="country"/>
				</td>
			</tr>
			<tr>
				<td>
					Roll
				</td>
				<td>
					<input type="text" name="role"/>
				</td>
			</tr>
		</table>
	{VAR:reforb}
	{VAR:HTML_FORM_END_HTML}
	</form>
	{VAR:HTML_BODY_HTML}
</html>
