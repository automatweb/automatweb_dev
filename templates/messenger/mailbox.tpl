<style>
.msgtitle {font-family: Verdana,helvetica; font-size: 11px; font-weight: bold; background: #C3D0DC; }
.msgtitle2 {font-family: Verdana,helvetica; font-size: 11px; font-weight: bold; background: #C3D0DC; }
.msgline {font-family: Verdana,helvetica; font-size: 11px; background: #ffffff; }
.msgline2 {font-family: Verdana,helvetica; font-size: 11px; background: #F1F1F1; }
.msgline3 {font-family: Verdana,helvetica; font-size: 11px; background: #F1F1F1; }
</style>


{VAR:menu}

<form method="get"  action='orb.{VAR:ext}'>
{VAR:gopage_reforb}
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr>
<td class="text" bgcolor="#F1F1F1">&nbsp;<b>Lehed:</b>
<!-- SUB: prev -->
&nbsp;<a href="{VAR:pg}">&lt;eelmine</a>&nbsp;
<!-- END SUB: prev -->
<select name="page">
{VAR:pagelist}
</select>
<input type="submit" value=" Ava ">
<!-- SUB: next -->
&nbsp;<a href="{VAR:pg}">jargmine&gt;</a>&nbsp;
<!-- END SUB: next -->

</td>
</form>
<form method="get" action="orb.{VAR:ext}">

<td align="right" bgcolor="#F1F1F1">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="text">

<script language="JavaScript">
function gotoUrl(popupCtrl, noOfForm) {
	value = popupCtrl.options[popupCtrl.selectedIndex].value;
	if (value != 'header')
	{
        	top.location = '{VAR:url_no_id}&id=' + popupCtrl.options[popupCtrl.selectedIndex].value;
	};
}
</script>
</td><td>

<select name="id" onChange="gotoUrl(this)">
{VAR:folders_dropdown}
</select>
</td>
<td>

<input type="hidden" name="class" value="messenger">
</td>
</tr>
</table>
</td></tr>
</form>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<form method="post" action="orb.{VAR:ext}" name="foo">
<tr>
<td bgcolor="#F1F1F1">

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td>
<input type="submit" name="mark_as_read" class="formbutton" value="Loetuks">
<input type="submit" name="mark_as_new" class="formbutton" value="Uueks">
<input type="submit" name="delete" class="formbutton" value="Kustuta">
</td>
<td>&nbsp;&nbsp;</td>
<td align="right">
<select name="folder1" class="formbutton">
{VAR:folders_dropdown2}
</select></td>
<td>
<input type="submit" name="move_to1" value=" ok " class="formbutton">
</td></tr></table>
</td></tr></table>


<script language="JavaScript">
function toggle_all()
{
	with(document.foo)
	{
		for (i = 0; i < elements.length; i++)
		{
			if (elements[i].type == 'checkbox')
			{
				if (allchecked.value == 1)
				{
					elements[i].checked = false;
				}
				else
				{
					elements[i].checked = true;
				}
			};
		};
		if (allchecked.value == 1)	
		{
			allchecked.value = 0;		
		}
		else
		{
			allchecked.value = 1;
		};
		
	};
}
</script>

<!-- SUB: attach -->
<img src="{VAR:baseurl}/img/attach.gif" width="6" height="12" border="0">
<!-- END SUB: attach -->
<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#DDDDDD">
<tr>
<td>

<table border="0" cellspacing="1" cellpadding="2" width="100%" bgcolor="#FFFFFF">
{VAR:table}
</table>

</td>
</tr>
</table>

<br>
<input type="hidden" name="allchecked" value="0">
<input type="hidden" name="total" value="{VAR:total}">


<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr>
<td bgcolor="#F1F1F1">

<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><td>
<input type="submit" name="mark_as_read" class="formbutton" value="Loetuks">
<input type="submit" name="mark_as_new" class="formbutton" value="Uueks">
<input type="submit" name="delete" class="formbutton" value="Kustuta">
</td>
<td>&nbsp;&nbsp;</td>
<td align="right">
<select name="folder2" class="formbutton">
{VAR:folders_dropdown2}
</select>
<input type="submit" name="move_to2" value=" ok " class="formbutton">
{VAR:reforb}
</td></tr></table>
</td></tr></table>


</form>
