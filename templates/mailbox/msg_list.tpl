<script language=javascript>
	var selected1 = "";

	function setSel(nr)
	{
		if (document.all)
		{
			eval("document.all.l_"+nr+"_1.style.visibility='visible';");
			eval("document.all.l_"+nr+"_2.style.visibility='visible';");
			eval("document.all.l_"+nr+"_3.style.visibility='visible';");
			if (selected1 != "" && selected1 != nr)
			{
				eval("document.all.l_"+selected1+"_1.style.visibility='hidden';");
				eval("document.all.l_"+selected1+"_2.style.visibility='hidden';");
				eval("document.all.l_"+selected1+"_3.style.visibility='hidden';");
			}
		}
		else
		{
			eval("document.surround_"+nr+"_1.document.l_"+nr+"_1.visibility='visible';");
			eval("document.surround_"+nr+"_2.document.l_"+nr+"_2.visibility='visible';");
			eval("document.surround_"+nr+"_3.document.l_"+nr+"_3.visibility='visible';");
			if (selected1 != "" && selected1 != nr)
			{
				eval("document.surround_"+selected1+"_1.document.l_"+selected1+"_1.visibility='hidden';");
				eval("document.surround_"+selected1+"_2.document.l_"+selected1+"_2.visibility='hidden';");
				eval("document.surround_"+selected1+"_3.document.l_"+selected1+"_3.visibility='hidden';");
			}
		}
		
		selected1 = nr;
	}

	function setRead(img)
	{
		if (document.all)
		{
			document.all[img].src='/images/mail.gif';
		}
	}

	function doSubmit(vr)
	{
		eval("document.awp."+vr+".value=1");
		document.awp.submit();
	}
</script>
<style type='text/css'>
.sels { background-color: #000000; color: #ffffff; FONT-FAMILY: Arial,Helvetica,sans-serif; FONT-SIZE: 11px; FONT-WEIGHT: normal; TEXT-DECORATION: none;}

BODY { FONT-FAMILY: Arial,Helvetica,sans-serif; FONT-SIZE: 11px; FONT-WEIGHT: normal; TEXT-DECORATION: none;}
.mlink { background-color: #000000; color: #ffffff; }

.abs { visibility: hidden; background-color: #000000; color: #ffffff; }
<!-- SUB: SPANS -->
#surround_{VAR:id}_3 {position: relative;}
#surround_{VAR:id}_2 {position: relative;}
#surround_{VAR:id}_1 {position: relative;}
#l_{VAR:id}_1 {position: absolute;}
#l_{VAR:id}_2 {position: absolute;}
#l_{VAR:id}_3 {position: absolute;}
<!-- END SUB: SPANS -->
</style>
<table border="0" cellspacing="1" cellpadding="0" width="100%">
<tr>
<td class="title"><form name='awp' action='refcheck.{VAR:ext}' METHOD=POST><a href='javascript:doSubmit("move")'>Liiguta</a> valitud kirjad folderisse <select name='folder' class='small_button'>{VAR:folders}</select>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:doSubmit("del")'>Kustuta valitud</a></td>
</tr>
</table>{VAR:table}<input type='hidden' NAME='action' VALUE='submit_msg_list'><input type='hidden' NAME='parent' VALUE='{VAR:parent}'><input type='hidden' NAME='del' VALUE='0'><input type='hidden' NAME='move' VALUE='0'></form>