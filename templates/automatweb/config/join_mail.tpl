<br>
<form action='reforb.{VAR:ext}' method=POST>
<table bgcolor="#CCCCCC" cellpadding=3 cellspacing=1 border=0>
<tr bgcolor="#C9EFEF">
<td class="plain">E-maili sisu, mis saadetakse kasutajale liitumisel (kasutajate andmete alias on #liituja_andmed# , kasutajanime alias #kasutaja# ja parooli alias #parool#).</td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Subject: <input type='text' name='join_mail_subj' value='{VAR:join_mail_subj}'></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><textarea name='join_mail' cols=70 rows=20 wrap=hard>{VAR:join_mail}</textarea></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">E-maili sisu, mis saadetakse kasutajale kui tal on parool meelest l2inud (kasutajate andmete alias on #liituja_andmed# , kasutajanime alias #kasutaja# ja parooli alias #parool#).</td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain">Subject: <input type='text' name='pwd_mail_subj' value='{VAR:pwd_mail_subj}'></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><textarea name='pwd_mail' cols=70 rows=20 wrap=hard>{VAR:pwd_mail}</textarea></td>
</tr>
<tr bgcolor="#C9EFEF">
<td class="plain"><input type='submit' value='Salvesta'></td>
</tr>
{VAR:reforb}
</form>
