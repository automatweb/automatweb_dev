<html>
<head>
<title>Vali kontaktid</title>
</head>
<style type="text/css">
	.selboxtitle {
		font-family: Tahoma,Arial,Helvetica,sans-serif;
		font-size: 11px;
	}

	.selbox {
		font-family: Tahoma,Arial,Helvetica,sans-serif;
		font-size: 11px;
	};
</style>
<script language="JavaScript">
<!-- SUB: group -->
group{VAR:oid} = new Array();
<!-- SUB: gline -->
group{VAR:oid}[{VAR:id}] = "{VAR:name}";
<!-- END SUB: gline -->

<!-- END SUB: group -->

group0 = new Array();
group1 = new Array();
group2 = new Array();

<!-- SUB: garr -->
group0[{VAR:gd}] = "{VAR:name}";
<!-- END SUB: garr -->

<!-- SUB: larr -->
group1[{VAR:gd}] = "{VAR:name}";
<!-- END SUB: larr -->

<!-- SUB: l2arr -->
group2[{VAR:gd}] = "{VAR:name}";
<!-- END SUB: l2arr -->

function empty_select(selbox)
{
	len = selbox.options.length;
	for (i = 0; i < len; i++)
	{
		selbox.options[0] = null;
	}
	//history.go(0);
};

function fill_select(selbox,arr)
{
	//alert('filling select with ' + arr.length + ' elements');
	for (i = 0; i < arr.length; i++)
	{
		opt = new Option(arr[i],i);
		selbox.options[i] = opt;
	};
};

function replace_contents(selbox)
{
	empty_select(document.picker.existing);
	idx = document.picker.group.options[document.picker.group.selectedIndex].value;
	myarr = "group"+ idx;
	kala = eval(myarr);
	fill_select(document.picker.existing,kala);
	return 1;
}

function filter(selbox)
{
	empty_select(document.picker.existing);
	idx = document.picker.group.options[document.picker.group.selectedIndex].value;
	// nyyd on meil koikide source grupis olevate aadresside list
	myarr = "group"+ idx;
	newarr = new Array();
	all = eval(myarr);
	c = 0;
	rel = new String;
	for (i = 0; i < all.length; i++)
	{
		rel = all[i];
		beg = rel.substr(0,document.picker.search.value.length);
		if (beg == document.picker.search.value)
		{
			newarr[c] = rel;
			c++;
		};
	};
	fill_select(document.picker.existing,newarr);
}

function add_selected(to,all)
{
	for (i = 0; i < document.picker.existing.length; i++)
	{
		el = document.picker.existing.options[i];
		cnt = 0;
		if (el.selected || all)
		{
			newopt = new Option(el.text);
			to.options[to.length] = newopt;
			cnt++;
		};
	};
}

function remove_selected(from,all)
{
	for (i = (from.length - 1); i >= 0; i--)
	{
		if ((from.options[i].selected) || (all))
		{
			from.options[i] = null;
		};
	};
};

function post_contacts()
{
	addr = new Array();
	cc = new Array();

	any_lists_selected=0;

	for (i = 0; i < document.picker.use.length; i++)
	{
		addr[i] = document.picker.use.options[i].text;
		j=0;
		while (addr[i].substr(j,1)==' ' && j<addr[i].length)
		{
			j++;
		};
		
		if (addr[i].substr(j,1)==':')
			any_lists_selected=1;
	};

	oldval = window.opener.document.writemessage.mtargets1.value;
	if (oldval.length > 0)
	{
		oldval = oldval + ',';
	};

	oldval = oldval + addr.join(",");
	window.opener.document.writemessage.mtargets1.value = oldval;

	for (i = 0; i < document.picker.cc.length; i++)
	{
		cc[i] = document.picker.cc.options[i].text;
	};

	oldval = window.opener.document.writemessage.mtargets2.value;

	if (oldval.length > 0)
	{
		oldval = oldval + ',';
	};

	oldval = oldval + cc.join(",");

	window.opener.document.writemessage.mtargets2.value = oldval;
	if ('{VAR:is_list_msg}'!='1' && any_lists_selected)
	{
		window.opener.make_it_a_list_msg();
	};
	window.close();
}

</script>
</head>
<body bgcolor="#FFFFFF" onLoad="fill_select(document.picker.existing,group0)">
<form name="picker" method="POST"  action='reforb.{VAR:ext}'>
<table border="1" cellspacing="0" cellpadding="0">
<tr>
<td colspan="3" class="selboxtitle">
Otsi: <input type="textbox" name="search" onKeyUp="filter()" size="30"><br>
Grupp: <select class="selbox" name="group" onChange="replace_contents(this)">
{VAR:groups}
</select>
</td>
</tr>
<tr>
<td rowspan="2" valign="top" class="selboxtitle">
Kontaktid:<br>
<select class="selbox" style="width:200px" name="existing" multiple size="20">
</select>
</td>
<td align="center">
<input type="button" onClick="add_selected(document.picker.use,0)" value=" &gt; "><br>
<input type="button" onClick="add_selected(document.picker.use,1)" value=" &gt;&gt; "><br>
<input type="button" onClick="remove_selected(document.picker.use,1)" value=" &lt;&lt; "><br>
<input type="button" onClick="remove_selected(document.picker.use,0)" value=" &lt; "><br>
</td>
<td valign="top" class="selboxtitle">
To:<br>
<select class="selbox" style="width:200px" name="use" multiple size="10">
</select>
</td>
</tr>
<tr>
<td align="center" class="selbox">
<input type="button" onClick="add_selected(document.picker.cc,0)" value=" &gt; "><br>
<input type="button" onClick="add_selected(document.picker.cc,1)" value=" &gt;&gt; "><br>
<input type="button" onClick="remove_selected(document.picker.cc,1)" value=" &lt;&lt; "><br>
<input type="button" onClick="remove_selected(document.picker.cc,0)" value=" &lt; "><br>
</td>
<td class="selboxtitle">
Cc:<br>
<select class="selbox" style="width:200px" name="cc" multiple size="10">
</select>
</td>
</tr>
<tr>
<td colspan="3" align="center">
<input type="button" value="Tee valik" onClick="post_contacts(); return false;">
</td>
</tr>
</table>
</form>
</body>
</html>
