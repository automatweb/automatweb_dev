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
function new_object()
{
	with(document.foo)
	{
		cl = aselect.options[aselect.selectedIndex].value;
		{
			window.location.href="orb.{VAR:ext}?class="+cl+"&action=new&parent={VAR:parent}&return_url=" + document.searchform.return_url.value;
		};
	};
};

var chk_status = true;

function selall()
{
	len = document.searchform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.searchform.elements[i].name.indexOf('sel') != -1)
		{
			document.searchform.elements[i].checked = chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
}

function search_selall()
{
	selall();
}


function SendValues()
{
	len = document.searchform.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.searchform.elements[i].name == 'sel') //indexOf('sel') != -1)
		{
			if (document.searchform.elements[i].checked == true)
			{
				str = 'selval[' + document.searchform.elements[i].value + ']';
				label = document.searchform.elements[str].value;
				opener.put_value(document.searchform.elements[i].value, label);
			}
		}
	}
	window.close();
}

</script>
</form>
<form method="GET" name="searchform" action="reforb.{VAR:ext}">
{VAR:form}
{VAR:reforb}
{VAR:table}
</form>
