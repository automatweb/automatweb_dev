<table width="100%" border=0 cellspacing=0 cellpadding=1 bgcolor="#EEEEEE">
<tr>
<td colspan="2" class="title">
<a href='pickobject.{VAR:ext}?docid={VAR:id}'>Lisa uus objekt&gt;&gt;&gt;</a></td>
</tr>
<form name="aform">
<!-- SUB: table -->
<tr>
	<td colspan=2><img src='{VAR:baseurl}/images/transa.gif' width=10 height=16></td>
</tr>
	<script language="JavaScript">
		var sel_{VAR:type} = 0;
		function ch_{VAR:type}()
		{
			len = document.aform.elements.length;
			cnt = 0;
			chk = 0;
			for (i = 0; i < len; i++)
			{
				with(document.aform.elements[i])
				{
					if (type == "checkbox" 
						&& name.indexOf("_{VAR:type}") != -1
						&& checked )
						{
							cnt++;
							chk = value;
						}	
				}
			};
			use = 0;
			if (sel_{VAR:type} > 0)
			{
				if (cnt == 0)
				{
					use = sel_{VAR:type};
				}
			}
			else
			{
				if (cnt == 1)
				{
					use = chk;
				};
			};
			if (use > 0)
			{
				loc = '{VAR:chlink}&{VAR:field}=' + use;
				window.location = loc;
			}
			else
			{
				alert('Palun valige _üks_ objekt muutmiseks');
			};
		}

		function del_{VAR:type}()
		{
			len = document.aform.elements.length;
			cnt = 0;
			chk = 0;
			for (i = 0; i < len; i++)
			{
				with(document.aform.elements[i])
				{
					if (type == "checkbox" 
						&& name.indexOf("_{VAR:type}") != -1
						&& checked )
						{
							cnt++;
							chk = value;
						}	
				}
			};
			if (cnt != 1)
			{
				alert('Palun valige _üks_ objekt kustutamiseks');
			}
			else
			{
				if (confirm('Kustutada see objekt?'))
				{
					window.location = '{VAR:dellink}&id=' + chk;
				};
			};
		}
	</script>
<tr>
	<td valign=top>
		<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC" width="100%">
		<tr>
			<td class="title">{VAR:title}</td>
		</tr>
		</table>
	</td>
	<td>
		<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC" width="100%">
		<tr>
			<td class="title" colspan=7><a href="{VAR:add_link}">Lisa uus</a> | <a href="javascript:ch_{VAR:type}()">Muuda</a> | <a href="javascript:del_{VAR:type}();">Kustuta</a></td>
		</tr>
		<tr>
		<td>{VAR:contents}</td>
		</tr>
		</table>
	</td>
</tr>
<!-- END SUB: table -->
</form>
</table>
