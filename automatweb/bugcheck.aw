<?php
// siin on vaja midagi ära muuta
// et ta võtaks worki alt const.aw
$SCRIPT_FILENAME="e:/str/work/public/kala.aw";

include("const.aw");
classload("bugtrack");
$bt=new bugtrack();

$parandatud=array_flip($bt->statlist)["lahendatud"];
$tm=time();

$q=$bt->db_query("SELECT * FROM bugtrack where status!='$parandatud' and alertsent!=1 and timeready<='$tm'",false);

if ($q)
while ($bug=$bt->db_next())
{
	if ($bug["developer_mail"])
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
		@mail($developer_mail,$subject,$msg,"From: bugtrack <dev@struktuur.ee>");
		$bt->save_handle();
		$bt->db_query("UPDATE bugtrack set alertsent='1' where id=$id");
		$bt->restore_handle();
	};
};
?>