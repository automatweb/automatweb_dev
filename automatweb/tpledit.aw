<?php
// tegelikult on kogu selle asja idee alguses peale vale olnud. Ideaalis peaks see v�lja n�gema
// hoopis nii, et koigepealt joonistame valmis mingi abstraktse cellidest koosneva tabeli.
// Ja igasse celli saaks siis lisada mone elemendi "poolist", ehk koikide selle template
// juurde lisatud staatiliste voi d�naamiliste elementide nimekirjast
// See oleks IDEAALvariant.

// see praegune on aga t��variant. �ritan sellega hakkama saada. 

include("const.aw");
include("admin_header.$ext");

classload("defs","doctemplate");

$dt = new doctemplate($tpl);

switch($action)
{
	// lisab uue d�naamilise elemendi
	case "submitsection":
		$dt->add_dynamic($HTTP_GET_VARS);
		print "adsadsad";
		header("Refresh: 0;url=$PHP_SELF?tpl=$tpl");
		print "Salvestan..";
		exit;
	// salvestab template kogu t�iega
	case "savetemplate":
		$dt->submit_template($HTTP_POST_VARS);
		header("Refresh: 0;url=$PHP_SELF?tpl=$tpl");
		print "Salvestan..";
		exit;

	// lisab elemnte object poolist
	case "addstatic":
		$dt->add_static($HTTP_GET_VARS);
		header("Refresh: 0;url=$PHP_SELF?tpl=$tpl");
		print "Salvestan..";
		exit;
	case "add":
		$content = $dt->add_form();
		break;
	
	case "addnew":
		$params["name"] = $name;
		$tpl = $dt->register_tpl($params);
		header("Refresh: 0;url=$PHP_SELF?tpl=$tpl");
		print "..";
		exit;

	// n�itab templatede nimekirja
	case "list":
		$content = $dt->gen_list();
		break;

	// kustutab m�rgitud elemendid
	case "delete":
		$HTTP_POST_VARS["delete_marked"] = 1;
		$dt->submit_template($HTTP_POST_VARS);
		header("Refresh: 0;url=$PHP_SELF?tpl=$tpl");
		print "Salvestan..";
		exit;
	
	// n�itab object pooli
	case "objectpool":
		print $dt->gen_object_pool();
		exit;

	// n�itab template eelvaadet
	case "preview":
		$content = $dt->show(array("type" => "preview"));
		break;
	
	// vaikimisi n�itame template muutmise vormi
	default:
		$content = $dt->show();
		break;
};

include("admin_footer.$ext");
?>
