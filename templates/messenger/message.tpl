<script language="javascript">
function show_attach(id) {
        toolbar = 0;
        width = 200;
        height = 400;
        file = "{VAR:showatt}&id=" + id;
        self.name = "main";
        aw_popup(file,"remote",width,height);
};
</script>

{VAR:menu}


<img src="{VAR:baseurl}/automatweb/img/trans.gif" align="" width="1" height="2" border="0" alt="">

<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td bgcolor="#F1F1F1" align="center">

<!-- SUB: show -->
<input class="formbutton" type="submit" value="Vasta" onClick="window.location.href='{VAR:reply}';return false;">

<input class="formbutton" type="submit" value="Vasta kõigile" onClick="window.location.href='{VAR:reply_all}';return false;">

<input class="formbutton" type="submit" value="Forward" onClick="window.location.href='{VAR:forward}';return false;">

<input class="formbutton" type="submit" value="Kustuta" onClick="window.location.href='{VAR:delete}';return false;">

<input class="formbutton" type="submit" value="Headerid" onClick="aw_popup_s('{VAR:headers}','headers',500,200);return false;">
<input class="formbutton" type="submit" name="edit" value="Muuda" onClick="window.location.href='{VAR:edit}';return false;">
<!-- END SUB: show -->
<!-- SUB: preview -->
<input type="submit" name="post" value="Saada" onClick="window.location.href='{VAR:post}';return false;">
<!-- END SUB: preview -->
</td>

<form method="get" action="orb.{VAR:ext}">
<td align="right" bgcolor="#F1F1F1">




<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<script language="JavaScript">
function gotoUrl(popupCtrl, noOfForm) {
        top.location = '{VAR:gotourl}&id=' + popupCtrl.options[popupCtrl.selectedIndex].value;
}
</script>
<select name="id" onChange="gotoUrl(this)">
{VAR:folders_dropdown}
</select>
</td>
<td>

<input type="hidden" name="class" value="messenger">
</td>
</tr>
</table>
</td>
</form>
</tr></table>

<!-- SUB: import_contact -->
<a href="javascript:aw_popup_s('{VAR:imp_contact}','contact',300,500)">{VAR:addr}</a>
<!-- END SUB: import_contact -->

<table border="0" cellspacing="0" cellpadding="0" width="100%" bgcolor="#EEEEEE">
<tr>
<td>
<table border=0 cellspacing=1 cellpadding=1 width="100%" bgcolor="#FFFFFF">
<tr>
<td width="20%" class="textsmall" align="right">Kellelt:&nbsp;</td>
<td class="textsmallbold">
<a href="javascript:aw_popup_s('{VAR:imp_c_2}','contact',300,500)">{VAR:mfrom}</a>
&nbsp;&nbsp;
<a href="{VAR:search}">[ ? ]</a>
</td>
</tr>
<tr>
<td width="20%" class="textsmall" align="right">Kellele:&nbsp;</td>
<td class="textsmallbold">{VAR:mtargets1}</td>
</tr>
<!-- SUB: cc -->
<tr>
<td width="20%" class="textsmall" align="right">CC:&nbsp;</td>
<td class="textsmallbold">{VAR:mtargets2}</td>
</tr>
<!-- END SUB: cc -->
<!--
<tr>
<td width="20%" class="textsmall" align="right">Kellele (grupid): &nbsp;</td>
<td class="textsmallbold">{VAR:mtargets2}</td>
</tr>
-->
<tr>
<td width="20%" class="textsmall" align="right">Teema:&nbsp;</td>
<td class="textsmallbold">{VAR:subject}</td>
</tr>
<tr>
<td width="20%" class="textsmall" align="right">Aeg:&nbsp;</td>
<td class="textsmallbold">{VAR:tm}</td>
</tr>
<tr>
<td colspan="2">
<font face="{VAR:msg_font}" size="{VAR:msg_font_size}">
{VAR:message}
</font>
</td>
</tr>
<!-- SUB: attach -->
<tr>
<td colspan="2" class="text">{VAR:cnt}. <img src="{VAR:icon}"><a href="{VAR:get_attach}" target="_new">{VAR:name}</a>
&nbsp;&nbsp;
<a href="#" onClick="aw_popup_s('{VAR:pick_folder}','pick',250,500)">Salveta AW-sse</a></td>
</td>
</tr>
<!-- END SUB: attach -->
<!-- SUB: event_attach -->
<tr>
<td colspan="2" class="text">
{VAR:cnt}. <img src="{VAR:icon}"> <i>{VAR:start} - {VAR:end}</i> : <b>{VAR:name}</b>&nbsp;&nbsp; &nbsp;<a href='{VAR:save_cal}'>Salvesta päevaplaani</a>
</td>
</tr>
<!-- END SUB: event_attach -->
</table>
</td>
</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td bgcolor="#F1F1F1" align="left">
    {VAR:show}
    </td>
   </tr>
</table>
