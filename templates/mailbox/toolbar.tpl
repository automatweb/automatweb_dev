<script language=javascript>
nm2_up = new Image();
nm2_up.src = "images/nm2_up.gif";
nm2_down = new Image();
nm2_down.src = "images/nm2_down.gif";

re_up = new Image();
re_up.src = "images/re_up.gif";
re_down = new Image();
re_down.src = "images/re_down.gif";

ra_up = new Image();
ra_up.src = "images/ra_up.gif";
ra_down = new Image();
ra_down.src = "images/ra_down.gif";

fo_up = new Image();
fo_up.src = "images/fo_up.gif";
fo_down = new Image();
fo_down.src = "images/fo_down.gif";

pr_up = new Image();
pr_up.src = "images/pr_up.gif";
pr_down = new Image();
pr_down.src = "images/pr_down.gif";

de_up = new Image();
de_up.src = "images/de_up.gif";
de_down = new Image();
de_down.src = "images/de_down.gif";

sr2_up = new Image();
sr2_up.src = "images/sr2_up.gif";
sr2_down = new Image();
sr2_down.src = "images/sr2_down.gif";

ad_up = new Image();
ad_up.src = "images/ad_up.gif";
ad_down = new Image();
ad_down.src = "images/ad_down.gif";

fi_up = new Image();
fi_up.src = "images/fi_up.gif";
fi_down = new Image();
fi_down.src = "images/fi_down.gif";

co_up = new Image();
co_up.src = "images/co_up.gif";
co_down = new Image();
co_down.src = "images/co_down.gif";

function over(nm)
{
	document[nm].src=eval(nm+"_up.src");
}

function out(nm)
{
	document[nm].src=eval(nm+"_down.src");
}
</script>
<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td background='images/toolbar_end.gif'><a onMouseOver="over('nm2')" onMouseOut="out('nm2')" target='message' href='mail.aw?type=new_mail'><img src='images/nm2_down.gif' NAME='nm2' border=0></a><a onMouseOver="over('re')" onMouseOut="out('re')" target='message' href='mail.aw?type=reply'><img src='images/re_down.gif' NAME='re' border=0></a><!--<a onMouseOver="over('ra')" onMouseOut="out('ra')" target='mailer' href='mail.aw?type=reply_all'><img src='images/ra_down.gif' NAME='ra' border=0></a>--><a onMouseOver="over('fo')" onMouseOut="out('fo')" target='message' href='mail.aw?type=forward'><img src='images/fo_down.gif' NAME='fo' border=0></a><a onMouseOver="over('pr')" onMouseOut="out('pr')" target='mailer' href='mail.aw?type=print'><img src='images/pr_down.gif' NAME='pr' border=0></a><a onMouseOver="over('de')" onMouseOut="out('de')" target='mailer' href='mail.aw?type=delete'><img src='images/de_down.gif' NAME='de' border=0></a><a onMouseOver="over('sr2')" onMouseOut="out('sr2')" target='mailer' href='mail.aw?type=check_mail'><img src='images/sr2_down.gif' NAME='sr2' border=0></a><!--<a onMouseOver="over('ad')" onMouseOut="out('ad')" target='mailer' href='mail.aw?type=address_book'><img src='images/ad_down.gif' NAME='ad' border=0></a>--><a onMouseOver="over('fi')" onMouseOut="out('fi')" target='mailer' href='mail.aw?type=find'><img src='images/fi_down.gif' NAME='fi' border=0></a><a onMouseOver="over('co')" onMouseOut="out('co')" target='mailer' href='mail.aw?type=config'><img src='images/co_down.gif' NAME='co' border=0></a></td></tr><tr><td background='images/toolbar_bot.gif'><img src='/images/transa.gif' width=100 height=4></td></tr></table>