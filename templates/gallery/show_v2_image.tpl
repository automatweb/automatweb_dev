<html>
<head>
	<title>{VAR:name}</title>
	<link rel="stylesheet" href="{VAR:baseurl}/css/styles.css">
</head>

<body bgcolor="#CED1D3" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
        <tr>
			<td bgcolor="#B1103C"><span class="aa_galtitle">Hinne: {VAR:avg_rating}</span></td>
			<td bgcolor="#B1103C" align="right">
				<table border="0" cellpadding="1" cellspacing="0">
					<tr>
						<td bgcolor="#FFFFFF"><a href='{VAR:print_link}'><img SRC="{VAR:baseurl}/img/print.gif"  BORDER=0 ALT="Print" title="Print"></a><img SRC="{VAR:baseurl}/img/trans.gif"  width="1" height="1" BORDER=0 ALT="Print" title="Print"><a href='{VAR:email_link}'><img SRC="{VAR:baseurl}/img/email.gif" BORDER=0 ALT="E-Mail to Friend" title="E-Mail to Friend"></a></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td bgcolor="#B1103C"><a href="javascript:close();">{VAR:image}</a></td>
		</tr>
	</table>

	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td bgcolor="#F38390"><span class="text">&nbsp;Vaatamisi: <b>{VAR:views}</b></span></td>
			<td bgcolor="#F38390" align="right"> 
				<table border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="aa_galtitle">Hinda:&nbsp;</td>
						<!-- SUB: RATING_SCALE_ITEM -->
							<td bgcolor="#FFFFFF" width="25" class="galhinne"><a href='{VAR:rate_link}'>{VAR:scale_value}</a></td>
						<!-- END SUB: RATING_SCALE_ITEM -->
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
