<table width="100%" cellspacing="0" cellpadding="0" border="0">
<form method="GET" name="foo">
<tr><td class="tableborder">
{VAR:toolbar}
</td>
</tr>
</table>
<script type="text/javascript">
function aw_save()
{
	cnt = 0;
	res = "";
	
	if (document.searchform.check.length)
	{
		len = document.searchform.check.length;
		for (i = 0; i < len; i++)
		{
			if (document.searchform.check[i].checked)
			{
				if (cnt == 0)
				{
					res = document.searchform.check[i].value;
				}
				else
				{
					res = res + "," + document.searchform.check[i].value;
				}
				cnt++;
			};
		};
	}
	else
	{
		res = document.searchform.check.value;
	};

	if (res.length > 0)
	{
		link = '{VAR:saveurl}&alias=' + res;
		window.location = link;
	}
	else
	{
		alert('Ühtegi objekti pole valitud!');
	};
}

function redir()
{
	with(document.foo)
	{
		cl = aselect.options[aselect.selectedIndex].value;
		if ((cl == "capt_new_object") || (cl == "0")|| (cl == ""))
		{
			alert('Vali objekti tüüp!');
		}
		else
		{
			if (cl.indexOf("reltype_") == 0)
			{
				is_reltype = 1;
			}
			else
			{
				is_reltype = 0;
			};
			if (is_reltype)
			{
				// the string "reltype_" is 8 characters long
				reltype = cl.substr(8,2);
				window.location.href="{VAR:create_relation_url}&reltype=" + reltype;
			}
			else
			{
				window.location.href="orb.{VAR:ext}?class="+cl+"&action=new&parent={VAR:parent}&period={VAR:period}&alias_to={VAR:id}&return_url={VAR:return_url}";
			};
		};
	};
};

var chk_status = true;

function selall()
{
	len = document.searchform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.searchform.elements[i].name.indexOf("check") != -1)
		{
			document.searchform.elements[i].checked=chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
}

function create_new_object()
{
var clids = new Array();
{VAR:clids}

	with(document.foo)
	{
		cl = aselect.options[aselect.selectedIndex].value;
		if (cl == "capt_new_object")
		{
			alert('Vali objekti tüüp!');
		}
		else
		{
			rel_type = reltype.options[reltype.selectedIndex].value;
			window.location.href="orb.{VAR:ext}?class=" + clids[cl] + "&action=new&parent={VAR:parent}&period={VAR:period}&alias_to={VAR:id}&return_url={VAR:return_url}&reltype=" + rel_type;
		};
	};
};

function search_selall()
{
	selall();
}

</script>
</form>
<form method="GET" name="searchform" action="reforb.{VAR:ext}">
{VAR:form}
<table cellspacing=0 cellpadding=2>
<tr>
	<td class='chformleftcol' width='160' nowrap></td>
	<td class='chformrightcol'>
	<input type='submit' value='Otsi' onclick="javascript:document.searchform.submit()" />
	</td>
</tr></table>
{VAR:reforb}
{VAR:table}
</form>
<script language= "javascript">
init();
</script>
