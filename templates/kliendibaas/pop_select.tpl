<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
	<HEAD>
		<TITLE> Vali {VAR:mida}</TITLE>
		<SCRIPT language=JavaScript>	
			function SendValue(value) 	
			{		
				opener.put_value(document.selectform.tyyp.value,value);
				window.close();
			}
		</SCRIPT>
	</HEAD>
	<BODY onload="document.selectform.selector.focus()" bgcolor=#777777>
		<form name=selectform>
			 Vali {VAR:mida}<br/>
			<input type=hidden name=tyyp value="{VAR:tyyp}">
			<select name=selector size=10 width=35>
<!--			<select name=selector onchange="javascript:SendValue(this.value)" size=10 width=35>-->
{VAR:options}
			</select><br>
			<input type=button value="cancel" onclick="javascript:window.close()"><br>
			<input type=button value="ok" onclick="javascript:SendValue(document.selectform.selector.value)"><br>
			<a href="{VAR:add}" target=_blank>lisa andmebaasi uus {VAR:mida}</a>
		</form>
	</BODY>
</HTML>