<script language="javascript">
function setLink(li,title)
{
	document.b88.url.value=li;
	document.b88.elements[2].value=title; // nime element
}

function toggle_javascript()
{
	if (document.b88.use_javascript.checked)
	{
		with (document.b88)
		{
			newwinwidth.disabled = false;
			newwinheight.disabled = false;
			newwintoolbar.disabled = false;
			newwinlocation.disabled = false;
			newwinmenu.disabled = false;
			newwinscroll.disabled = false;
		};
	}
	else
	{
		with (document.b88)
		{
			newwinwidth.disabled = true;
			newwinheight.disabled = true;
			newwintoolbar.disabled = true;
			newwinlocation.disabled = true;
			newwinmenu.disabled = true;
			newwinscroll.disabled = true;
		};
	};
};
</script>

<form enctype="multipart/form-data" method="POST" action="reforb.{VAR:ext}" name='b88'>


<!--tabelraam-->
<table width="100%" cellspacing="0" cellpadding="1">
<tr><td class="tableborder">

	<!--tabelshadow-->
	<table width="100%" cellspacing="0" cellpadding="0">
	<tr><td width="1" class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""></td><td class="tableshadow"><IMG SRC="{VAR:baseurl}/automatweb/images/trans.gif" WIDTH="1" HEIGHT="1" BORDER=0 ALT=""><br>
		<!--tabelsisu-->
		<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td><td class="tableinside">


<table border="0" cellpadding="0" cellspacing="2">
<tr>
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.b88.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="Salvesta" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a
href="javascript:this.document.b88.submit();">Salvesta<!--{VAR:LC_EXTLINKS_ADD} {VAR:LC_EXTLINKS_LINK}--></a>
</td></tr>
</table>


		</td>
		</tr>
		</table>


	</td>
	</tr>
	</table>

</td>
</tr>
</table>


<table width="100%" cellspacing="0" cellpadding="5">
<tr><td>



<table border="0" cellspacing="1" cellpadding="2">
<tr>
	<td>

<table border="0" cellspacing="1" cellpadding="2">
<tr>
	<td class="celltext">URL</td>
	<td class="celltext"><input type="text" name="url" size="40" value='{VAR:url}' class="formtext"></td>
</tr>
<tr>
	<td class="celltext" colspan=2><input type='hidden' name='type' value='ext'><a href="javascript:remote('no',500,400,'{VAR:search_doc}')">{VAR:LC_EXTLINKS_INSIDE_WEB}</a></td>
</tr>
<tr>
	<td class="celltext">{VAR:LC_EXTLINKS_NAME}</td>
	<td class="celltext"><input type="text" name="name" size="40" value='{VAR:name}' class="formtext"></td>
</tr>
<tr>
	<td class="celltext" colspan=2>{VAR:LC_EXTLINKS_COMMENT_TO_LC}</td>
</tr>					
<tr>
	<td colspan=2 class="celltext"><textarea name="comment" rows=5 cols=40  class="formtext">{VAR:comment}</textarea></td>
</tr>
<tr>
	<td colspan=2 class="celltext">{VAR:LC_EXTLINKS_DOC_LC}? <input type="checkbox" name="doclinkcollection" value=1 {VAR:doclinkcollection}></td>
</tr>
<tr>
	<td colspan=2 class="celltext">
			{VAR:link_image}<br>
			Uploadi pilt: <input type='file' size='30' name='link_image'>
	</td>
</tr>
<tr>
	<td colspan=2 class="celltext">
		Pilt on aktiivne: <input type='checkbox' name='link_image_check_active' value='1' {VAR:link_image_check_active}><br>
		{VAR:link_image_active_until}
	</td>
</tr>
</table>

	</td>
	<td valign="top">

<table border="0" cellspacing="1" cellpadding="1">
<tr>
	<td class="celltext" colspan="2">{VAR:LC_EXTLINKS_NEW_WINDOW} <input type="checkbox" name="newwindow" value=1 {VAR:newwindow}></td>
</tr>
<tr>
	<td colspan="2" bgcolor="#FFFFFF">
	

		<table cellspacing="0" cellpadding="5">
		<tr><td colspan="2" class="celltext" bgcolor="#EEEEEE">
Kasutada lingi loomisel Javascripti? <input type="checkbox" name="use_javascript" value=1 onClick="toggle_javascript()" {VAR:use_javascript}>		
		</td></tr>

<tr>
	<td class="celltext" bgcolor="#EEEEEE">Uue akna mõõtmed</td>
	<td class="celltext" bgcolor="#EEEEEE"><input type="text" name="newwinwidth" value="{VAR:newwinwidth}" size="4"> x 
	<input type="text" name="newwinheight" value="{VAR:newwinheight}" size="4"></td>
</tr>
<tr>
	<td class="celltext" bgcolor="#EEEEEE" valign="top">Uue akna määrangud</td>
	<td class="celltext" bgcolor="#EEEEEE">

	<table border="0" cellpadding="0" cellspacing="0">
	<tr><td><input type="checkbox" name="newwintoolbar" value=1 {VAR:newwintoolbar}></td><td class="celltext">Toolbar</td></tr>
	<tr><td><input type="checkbox" name="newwinlocation" value=1 {VAR:newwinlocation}></td><td class="celltext">Address bar</td></tr>
	<tr><td><input type="checkbox" name="newwinmenu" value=1 {VAR:newwinmenu}></td><td class="celltext">Menüüd</td></tr>
	<tr><td><input type="checkbox" name="newwinscroll" value=1 {VAR:newwinscroll}></td><td class="celltext">Scrollbar</td></tr>
	</table>

</td>
</tr>
		</table>
	
	
	
	</td>
</tr>


</table>

		</td>
		</tr>
		</table>
<span class="celltext">{VAR:LC_EXTLINKS_CHOOSE_CATALOGUE}</span><br>
<select class='small_button' name='parent'>{VAR:parent}</select>

{VAR:reforb}
</form>
<script language="JavaScript">
toggle_javascript();
</script>


</td></tr></table>
