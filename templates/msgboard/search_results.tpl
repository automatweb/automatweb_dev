


<span class="textPealkiri">{VAR:LC_MSGBOARD_RESULTS_OF_SEARCH}</span> - <span class='textSmall'>{VAR:LC_MSGBOARD_FOUND} <b>{VAR:count} </b>{VAR:LC_MSGBOARD_COMMENTS}</span><br>

<img src="/img/trans.gif" width="1" height="10" border="0" alt=""><br>

<!--begin VALI LEHEKYLG-->
<table width="370" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="textSmall">

: <a href='{VAR:baseurl}/automatweb/comments.{VAR:ext}?section={VAR:section}&action=search'><b>{VAR:LC_MSGBOARD_SEARCH_AGAIN}</b></a> &nbsp; : <a href='{VAR:baseurl}/automatweb/comments.{VAR:ext}?section={VAR:section}'><b>{VAR:LC_MSGBOARD_READ_COMM}</b></a>

    </td>
    <td align="right">



<span class="textSmall">&nbsp;
{VAR:PAGES}
</span>

    </td>
  </tr>
</table>


<img src="/img/joon.gif" width="370" height="1" border="0" alt=""><br>
<img src="/img/trans.gif" width="1" height="5" border="0" alt=""><br>
<!--end VALI LEHEKYLG-->



<table border=0 cellpadding=0 cellspacing=0 width=100%>
<!-- SUB: message -->
  <tr>
		<td class="vaikeVerdana">{VAR:LC_MSGBOARD_WHO}:&nbsp;&nbsp;<b><a href='mailto:{VAR:email}'>{VAR:from}</a></b>&nbsp;@&nbsp;{VAR:time}</td>
	</tr>
	<tr>
		<td bgcolor="#EFEFEF" class="vaikeVerdana">{VAR:LC_MSGBOARD_SUBJECT}:&nbsp;&nbsp;<b>{VAR:subj}</b></td>
	</tr>
	<tr>
		<td bgcolor="#ffffff" class="vaikeVerdana"><img src="/img/nool_pun.gif" width="9" height="7" border="0" alt=""><a href='{VAR:baseurl}/automatweb/comments.{VAR:ext}?section={VAR:s_section}&from=search&cid={VAR:comment_id}#c{VAR:comment_id}' class='linkSin'>{VAR:LC_MSGBOARD_READ}</a></td>
	</tr>
	<tr>
		<td><img src="/img/trans.gif" width="1" height="5" border="0" alt=""><br></td>
	</tr>
<!-- END SUB: message -->
</table>

<img src="/img/joon.gif" width="370" height="1" border="0" alt=""><br>

<table width="370" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>&nbsp;</td>
    <td align="right">



<span class="textSmall">&nbsp;
<!-- SUB: PAGES -->
: {VAR:LC_MSGBOARD_CHOOSE_PAGES}:&nbsp;
<!-- SUB: PAGE -->
<a href='{VAR:url}' class="link2"><b>{VAR:ltext}</b></a>&nbsp;&nbsp;
<!-- END SUB: PAGE -->
<!-- SUB: SEL_PAGE -->
<a href='{VAR:url}' class="link2">&gt;<b>{VAR:ltext}</b>&lt;</a>&nbsp;&nbsp;
<!-- END SUB: SEL_PAGE -->
<!-- END SUB: PAGES -->
</span>

    </td>
  </tr>
</table>

<img src="img/trans.gif" width="1" height="10" border="0" alt=""><br>

