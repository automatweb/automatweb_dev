Jutuka kuvamise viisid (kui valjad tyhjaks jata, siis avaneb kohe lehte kylastades):<br><br>
<form action='reforb.{VAR:ext}' method="POST">
Sisesta saidil oleva nupu kiri: <input type='text' name='buttontext' value='{VAR:buttontext}'><br>
Ikooni URL: <input type='text' name='port' value='{VAR:port}'><br>
Vali jutuka tyyp:<br>
Objekti nimi: <input type='text' name='nimi' value='{VAR:nimi}'><br>
<input type='submit' value='salvesta'>
{VAR:reforb}
</form>



<applet code="IRC.class" archive="kfc.jar" width=20 height=20>
<param name="buttontext" value=""><!-- kui text,siis pannakse nupp, nupp prioriteetsem, kui ikoon-->
<param name="icon" value=""><!-- kui mõlemad tyhjad, siis avatakse kohe -->
<param name="mode" value="4"><!-- 0 - piiranguidpole, 1 kanal + privad,2 - 1 priva 4 - arco-->  
<param name="channel" value="">
<param name="message" value="">
<param name="privat" value="">
<param name="windowcolor" value="#BDD2DE">
<param name="backcolor" value="#EEEEEE">
<param name="textcolor" value="#000000">
<param name="buttoncolor" value="#BDD2DE">
<param name="user" value=""><!-- {VAR:uid} -->
</applet>