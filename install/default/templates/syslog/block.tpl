<html>
<head>
<title>IP Blocker</title>
<style>
.header {
	font-family: Verdana;
	font-size: 12px;
	background: #FFCCAA;
}
.line {
	font-family: Verdana;
	font-size: 12px;
};
</style>
</head>
<body bgcolor="#FFFFFF">
Blokeeri IP aadresse statistikast
<a href="{VAR:self}">tagasi</a>
<p>
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#CCCCCC">
<tr>
<td>
	<form method="POST">
	<table border="1" cellspacing="2" cellpadding="2" bgcolor="#FFFFFF">
	<tr>
		<td colspan="2" class="header">IP blocker</td>
	</tr>
	<tr>
		<td class="line" align="center"><strong>IP</strong></center></td>
		<td class="line" align="center"><strong>Aktiivne</strong></center></td>
	</tr>
	<!-- SUB: line -->
	<tr>
		<td class="line">{VAR:ip}</td>
		<td class="line" align="center"><input type="checkbox" name="check[{VAR:id}]" value="1" {VAR:checked}></td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td class="line" align="center">
		<input type="text" name="new" size="20"><input type="submit" value="Lisa/Salvesta">
		<input type="hidden" name="op" value="saveblock">
		</td>
	</tr>
	</table>
	</form>
</td>
</tr>
</table>
</body>
</html>

