<script src="/automatweb/js/popup_menu.js" type="text/javascript">
</script>

<script language="javascript">
var chk_status = true;

function selall()
{
	len = document.foo.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.foo.elements[i].name.indexOf("sel") != -1)
		{
			document.foo.elements[i].checked=chk_status;
			window.status = ""+i+" / "+len;
		}
	}
	chk_status = !chk_status;
}

function add()
{
	if (document.foo.type.selectedIndex < 2)
	{
		alert("Valige objekt, mida lisada soovite!");
	}
	else
	{
		url = "orb.{VAR:ext}?class="+document.foo.type.options[document.foo.type.selectedIndex].value+"&action=new&parent={VAR:parent}&period={VAR:period}";
		window.location = url;
	}
}

function submit(val)
{
	document.foo.action.value=val;
	document.foo.submit();
}

function change(val)
{
	cnt = 0;
	len = document.foo.elements.length;
	for (i=0; i < len; i++)
	{
		if (document.foo.elements[i].name.indexOf("sel") != -1)
		{
			if (document.foo.elements[i].checked)
			{
				cnt++;
			}
		}
	}

	if (cnt == 1)
	{
		document.foo.action.value="change_redir";
		document.foo.submit();
	}
	else
	{
		alert("Valige ainult yks objekt palun!");
	}
}
</script>






<!-- begin ICONS table -->
<form action='reforb.{VAR:ext}' method="post" name="foo">
{VAR:toolbar}
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="awmenuedittableborder">
{VAR:table}
</td></tr></table>
{VAR:reforb}
</form>
