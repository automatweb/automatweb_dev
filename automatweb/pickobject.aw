<?php
// Obsolete?
include("const.aw");
include("admin_header.$ext");
// see on nö lyhendatud versioon object prauserit ... saab kasutada yhe konkreetse objekti
// välja valimiseks

// catchime voimalikke erroreid
if ($docid) 
{
	// meil on vaja kindlaks teha dokumendi nimi, millele me lisame
	classload("document");
	$docs = new document;
	$docdata = $docs->fetch($docid);
	// siin on ka sobiv koht ACL kontrolliks
	if (!$docdata) 
	{
		$sf->raise_error("Dokumenti IDga $docid ei eksiteeri",1);
	};
	if ($parent < 1) 
	{
		// kusagilt peab ju alustama
		$parent = $rootmenu;
	};
	if ($type == "search")
	{
		$content = $ob->search_objs($docid);	
	}
	else
	{
		$content = $ob->gen_pickable_list($parent,$docid,$mstring);	
	}
	$title = "Dokumendid &gt; '<a href='".$ob->mk_orb("change",array("id" => $docid),"document")."'>".$docdata[title]."</a>' &gt; Lisa objekt";
	$menu[] = $mstring;
} 
else 
{
	$sf->raise_error("Vigane päring: docid on defineerimata",1);
};
include("admin_footer.$ext");
?>
