<form action='reforb.{VAR:ext}' method="POST" name='boo'>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr>
	<td class="fcaption">Kasutajad:</td>
	<td class="fcaption">&nbsp;</td>
	<td class="fform">Valitud:</td>
</tr>
<tr>
	<td class="fcaption"><select name='users[]' style="width:200px" multiple size=12><option value=''>-----------------------------------------</select></td>
	<td class="fcaption" valign=center align=center>
		<a href='javascript:add_member(document.boo.elements[0],document.boo.elements[1])'>&gt;</a><br>
		<a href='javascript:remove_member(document.boo.elements[0],document.boo.elements[1])'>&lt;</a><br>
		<a href='javascript:add_all(document.boo.elements[0],document.boo.elements[1])'>&gt;&gt;&gt;</a><br>
		<a href='javascript:remove_all(document.boo.elements[0],document.boo.elements[1])'>&lt;&lt;&lt;</a><br>
	</td>
	<td class="fform"><select name='members[]' style="width:200px" multiple size=12><option value=''>-----------------------------------------</select></td>
</tr>
<tr>
<td class="fcaption" colspan=3><input class='small_button' type='submit' VALUE='Salvesta' onClick="selectall(document.boo.elements[1])"></td>
</tr>
</table>
{VAR:reforb}
</form>
<script language="javascript">
<!--
function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function addItem(list, text)
{
	list.options[list.length] = new Option(text,""+text,false,false);
}

function populate_list(el,arr)
{
	clearList(el);
	for (i = 0; i < arr.length; i++)
		addItem(el,arr[i]);
}

function in_array(n,arr)
{
	for (a = 0; a < arr.length; a++)
		if (arr[a] == n)
			return true;
	return false;
}

function populate_list_sel(el,arr,sel,el2)
{
	clearList(el);
	for (i = 0; i < arr.length; i++)
	{
		addItem(el,arr[i]);
		if (in_array(arr[i],sel))
			mk_itemsel2(el2,arr[i]);
	}
}

var users = new Array({VAR:users});
var members = new Array({VAR:members});

populate_list(document.boo.elements[0],users);
populate_list_sel(document.boo.elements[1],members,users,document.boo.elements[0]);

function add_member(fel,tel)
{
	for (i=0; i < fel.length; i++)
		if (fel.options[i].selected)
		{
			if (!is_member(tel,fel.options[i].value))
			{
				addItem(tel,fel.options[i].value);
				mk_itemsel(fel,i);
			}
		}
	unmarkAll(tel);
	unmarkAll(fel);
}

function remove_member(fel,tel)
{
	mm = new Array();
	cnt=0;
	for (i=0; i < tel.length; i++)
	{
		if (tel.options[i].selected)
		{
			rem_mk_itemsel(fel,tel.options[i].value);
		}
		else
		{
			mm[cnt++] = tel.options[i].value;
		}
	}

	clearList(tel);
	for (i=0; i < mm.length; i++)
	{
		addItem(tel,mm[i]);
	}
	unmarkAll(tel);
	unmarkAll(fel);
}

function mk_itemsel(el,num)
{
	el.options[i].text = ">>> "+ el.options[i].text;
}

function mk_itemsel2(el,num)
{
	for (a=0; a < el.length; a++)
		if (el.options[a].value == num)
			el.options[a].text = ">>> "+ el.options[a].text;
}

function rem_mk_itemsel(el,num)
{
	for (a=0; a < el.length; a++)
		if (el.options[a].value == num)
		{
			el.options[a].text = el.options[a].value;
			return;
		}
}

function is_member(el,t)
{
	for (a=0; a < el.length; a++)
		if (el.options[a].value == t)
			return true;
	return false;
}

function add_all(fel,tel)
{
	clearList(tel);
	for (i=0; i < fel.length; i++)
	{
		addItem(tel,fel.options[i].value);
		mk_itemsel(fel,i);
	}
	unmarkAll(tel);
	unmarkAll(fel);
}

function remove_all(fel,tel)
{
	for (i=0; i < fel.length; i++)
	{
		rem_mk_itemsel(fel,fel.options[i].value);
	}

	clearList(tel);
	unmarkAll(tel);
	unmarkAll(fel);
}

function unmarkAll(list)
{
	for(i=0; i < list.length; i++)
		list.options[i].selected = false;
}

function selectall(list)
{
	for(i=0; i < list.length; i++)
		list.options[i].selected = true;
}
-->
</script>