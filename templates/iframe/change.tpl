<form action='reforb.{VAR:ext}' METHOD="post" name="change">
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
<td align="center" class="icontext"><IMG SRC="{VAR:baseurl}/images/trans.gif" WIDTH="2" HEIGHT="2" BORDER=0 ALT=""><br><a href="javascript:this.document.change.submit();"
onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('save','','{VAR:baseurl}/automatweb/images/blue/awicons/save_over.gif',1)"><img name="save" alt="{VAR:LC_IFRAME_SAVE}" title="{VAR:LC_IFRAME_SAVE}" border="0" SRC="{VAR:baseurl}/automatweb/images/blue/awicons/save.gif" width="25" height="25"></a><br><a href="javascript:this.document.change.submit();">{VAR:LC_IFRAME_SAVE}</a>
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


<table border=0 cellpadding=2 cellspacing=1>
<tr>
	<td align=center>


<table border=0 cellspacing=1 cellpadding=1>
	<tr>
		<td class="celltext">{VAR:LC_IFRAME_NAME}:</td>
		<td class="celltext"><input type="text" name="name" VALUE="{VAR:name}" size="40"></td>
	</tr>
	
	<tr>
		<td class="celltext">{VAR:LC_IFRAME_URL}:</td>
		<td class="celltext"><input type="text" name="url" VALUE="{VAR:url}" size="40"></td>
	</tr>
	
	<tr>
		<td class="celltext" valign="top">{VAR:LC_IFRAME_COMMENT}:</td>
		<td class="celltext">
		<textarea name="comment" cols="40" rows="5">{VAR:comment}</textarea>
		</td>
	</tr>

	<tr>
		<td class="celltext" valign="top">{VAR:LC_IFRAME_DIMENSIONS}:</td>
		<td class="celltext">
			{VAR:LC_IFRAME_WIDTH} ({VAR:min_width}-{VAR:max_width}):
			<input type="text" name="width" size="4" value="{VAR:width}" maxlength="3">
			{VAR:LC_IFRAME_HEIGHT} ({VAR:min_height}-{VAR:max_height}):
			<input type="text" name="height" size="4" value="{VAR:height}" maxlength="3">
		</td>
	</tr>

	<tr>
		<td class="celltext">{VAR:LC_IFRAME_BORDER}:</td>
		<td class="celltext">
			<input type="checkbox" name="frameborder" value="1" {VAR:frameborder}>
		</td>
	</tr>

	<tr>
		<td class="celltext">{VAR:LC_IFRAME_SCROLLBARS}:</td>
		<td class="celltext">
			<select name="scrolling">
				{VAR:scrolling}	
			</select>
		</td>
	</tr>
</table>
</td>
</tr>
</table>
{VAR:reforb}
</form>
								
