<style type="text/css">
.awtab {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #1664B9;
background-color: #CDD5D9;
}
.awtab a {color: #1664B9; text-decoration:none;}
.awtab a:hover {color: #000000; text-decoration:none;}

.awtabdis {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #686868;
background-color: #CDD5D9;
}

.awtabsel {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabsel a {color: #FFFFFF; text-decoration:none;}
.awtabsel a:hover {color: #000000; text-decoration:none;}

.awtabseltext {
font-family: verdana, sans-serif;
font-size: 11px;
font-weight: bold;
color: #FFFFFF;
background-color: #478EB6;
}
.awtabseltext a {color: #FFFFFF; text-decoration:none;}
.awtabseltext a:hover {color: #000000; text-decoration:none;}

.awtablecellbackdark {
font-family: verdana, sans-serif;
font-size: 10px;
background-color: #478EB6;
}

.awtablecellbacklight {
background-color: #DAE8F0;
}

.awtableobjectid {
font-family: verdana, sans-serif;
font-size: 10px;
text-align: left;
color: #DBE8EE;
background-color: #478EB6;
}


</style>
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
{VAR:class_ids}

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

{VAR:reforb}
{VAR:table}
</form>
		<script language= "javascript">
			init();
		</script>
