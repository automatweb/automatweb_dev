<?php
include("const.aw");
classload("bugtrack");
$bt=new bugtrack();

$tm=time();
$q=$bt->db_query("SELECT * FROM bugtrack where alertsent!=1 and timeready<='$tm' and status not in ('$this->stat6','$this->stat4')",false);

if ($q)
while ($bug=$bt->db_next())
{
	if ($addr=$bug["developer_mail"]?$bug["developer_mail"]:$bt->get_user_mail($bug["developer"]))
	{
		extract($bug);
		$bug["timeready"]=date("H:i:s Y.m.d",$timeready);
		$bug["tm"]=date("H:i:s Y.m.d",$tm);
		$bug["status"]=$bt->statlist[$status];
		$bug["resol"]=$bt->reslist[$resol];
		$bug["severity"]=$bt->sevlist[$severity];
		$bt->read_template("mailwarningmsg.tpl");
		$bt->vars($bug);
		$msg=$bt->parse();
		$subject="Puuk parandamata: $site $title ";
		@mail($addr,$subject,$msg,"From: bugtrack <dev@struktuur.ee>");
		$bt->save_handle();
		$bt->db_query("UPDATE bugtrack set alertsent='1' where id=$id");
		$bt->restore_handle();
	};
};
?>