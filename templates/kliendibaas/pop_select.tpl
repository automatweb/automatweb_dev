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
			<input type=hidden name=tyyp value=linn>
			<select name=selector onchange="javascript:SendValue(this.value)">
{VAR:options}
			</select>
		</form>
	</BODY>
</HTML>