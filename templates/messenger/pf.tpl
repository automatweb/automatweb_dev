<html>
<head>
<title>Vali folder</title>
</head>
<style type="text/css">
	.fubartitle {font-family: Verdana,sans-serif; font-size: 9px; font-weight: bold; background: #EEEEFF; }
	.fubar {font-family: Verdana,sans-serif; font-size: 11px;};
</style>
</head>
<body bgcolor="#FFFFFF">
<h3>Folder: {VAR:name}</h3>
<h3>Objekt: {VAR:oname}</h3>
<form name="picker"  action='reforb.{VAR:ext}'>
<table border="0" cellspacing="0" cellpadding="0" bgcolor="#EEEEEE" width="100%">
<tr>
<td>
	<table border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF" width="100%">
	<tr>
		<td class="fubartitle" align="center">Folder</td>
		<td class="fubartitle" align="center">&nbsp;</td>
	</tr>
	<!-- SUB: up -->
	<tr>
		<td class="fubar" colspan="2"><a href="?class=messenger&action=pick_folder&id={VAR:id}&type=popup&msg_id={VAR:msg_id}&attach={VAR:aid}">..Üles</a></td>
	</tr>
	<!-- END SUB: up -->
	<!-- SUB: line -->
	<tr>
		<td class="fubar">{VAR:folder}</td>
		<td class="fubar">&nbsp;&nbsp;</td>
	</tr>
	<!-- END SUB: line -->
	<tr>
		<td class="fubar" align="center" colspan="2">
			<input type="submit" value="Salvesta siia">
		</td>
	</tr>
	</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
</body>
</html>
