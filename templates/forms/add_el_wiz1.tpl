<script language="javascript">
allels_v = new Array();
allels_n = new Array();
allels_s = new Array();

function init()
{
	for (i=0; i < f.el.options.length; i++)
	{
		allels_v[i] = f.el.options[i].value;
		allels_n[i] = f.el.options[i].text;
		name = f.el.options[i].text.lastIndexOf("/");
		if (name == -1)
		{
			name = 0;
		}
		allels_s[i] = " "+f.el.options[i].text.slice(name);
	}
}

function search()
{
	se = document.f.jssearch.value;
	selen = se.length;
	first = true;

	clearList(document.f.el);
	for (i=0; i < allels_n.length; i++)
	{
		if (allels_s[i].indexOf(se) != -1)
		{
			// if one contains another add it
			addItem(document.f.el,allels_n[i],allels_v[i],first);
			first = false;
		}
	}
}

function clearList(list)
{
	var listlen = list.length;

	for(i=0; i < listlen; i++)
		list.options[0] = null;
}

function addItem(list, text,value,sel)
{
	list.options[list.length] = new Option(text,""+value,false,sel);
}
</script>
<form method=POST action='reforb.{VAR:ext}' name='f'>
<table border=0 cellspacing=1 cellpadding=2 bgcolor="#CCCCCC">
<tr>
	<td colspan=3 class="fform"><input checked type='radio' name='type' value='add'>&nbsp;Lisa uus element</td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Vali kataloog, kuhu element lisada:</td>
	<td class="fform"><select name='parent' class='small_button'>{VAR:folders}</select></td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Elemendi nimi:</td>
	<td class="fform"><input type="text" name="name"></td>
</tr>
<tr>
	<td colspan=3 class="fform"><input type='radio' name='type' value='select'>&nbsp;Lisa olemasolev element
	&nbsp;&nbsp;&nbsp;Otsi:<input type='text' name='jssearch' class='small_button' onKeyDown="setTimeout('search()',10);">
	</td>
</tr>
<tr>
	<td class="fform">&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td class="fform">Vali element:</td>
	<td class="fform"><select size=10 class='small_button' name='el'>{VAR:elements}</select></td>
</tr>
<tr>
	<td class="fform" colspan="3" align="center">
	{VAR:reforb}
	<input type="submit" class='small_button' value="Lisa">
	</td>
</tr>
</table>
</form>
<script language="javascript">
init();
</script>