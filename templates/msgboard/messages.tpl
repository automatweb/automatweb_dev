<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="ftitle2">Sorteeri <a href="/automatweb/comments.{VAR:ext}?section={VAR:section}&type=nested"><b>VASTUSTE</b></a> või <a href="/automatweb/comments.{VAR:ext}?section={VAR:section}&type=flat"><b>AJA</b></a> järgi või <a href="/automatweb/comments.{VAR:ext}?section={VAR:section}&action=search"><b>OTSI</b></a></span><br></td>
	</tr>
	<tr>
		<td align="center" >&nbsp;<font color="#000000"><br>
			<!-- SUB: PAGES -->
			Lehed: 
			<!-- SUB: PAGE -->
			<span class="menyyLeft"><a href="/automatweb/comments.{VAR:ext}?section={VAR:section}&page={VAR:pagenum}"><b>{VAR:ltext}</b></a></span><font color="#000000"> | </font>
			<!-- END SUB: PAGE -->

			<!-- SUB: SEL_PAGE -->
		  <span class="menyyLeft"><b><font color="#000000">{VAR:ltext}</font></b></span> |
			<!-- END SUB: SEL_PAGE -->

			<!-- END SUB: PAGES -->
			</font>
			</span>
		</td>
	</tr>
</table>

<!--begin komment-->
<!-- SUB: message -->
<img src="/img/joon.gif" align="" width="370" height="1" border="0" alt="">
<table width=100% border=0 cellpadding=0 cellspacing=0>
<tr>
<td width=1><img src='/img/trans.gif' width="{VAR:level}" height="1" alt="" border="0"></td>
<td>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="textSmall"><a name="c{VAR:id}">Kes: <a href="mailto:{VAR:email}"><b>{VAR:from}</b></a> @ {VAR:time}</td>
  </tr>
  <tr>
    <td height="18" valign="top" class="textSmall">Teema: <b>{VAR:subj}</b></td>
  </tr>
  <tr>
    <td class="text">{VAR:comment}</td>
	</tr>
  <tr>
    <td valign="bottom" height="18" class="textSmall">: <a href="/automatweb/comments.{VAR:ext}?action=add&parent={VAR:id}&section={VAR:section}&page={VAR:page}"><b>Vasta</b></a>
		<!-- SUB: KUSTUTA -->
			&nbsp;&nbsp;: <a href='/automatweb/comments.{VAR:ext}?action=delete&parent={VAR:id}&section={VAR:section}&page={VAR:page}'>Kustuta</a>
		<!-- END SUB: KUSTUTA -->
		</td>
  </tr>
</table>
</td>
</tr>
</table>
<!-- END SUB: message -->
<!--end komment-->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
		<td align="center">&nbsp;{VAR:PAGES}</td>
	</tr>
</table>
<br>
<table width="400" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td class="ftitle2">: <b>Lisa uus kommentaar</b></td>
	</tr>
</table>



