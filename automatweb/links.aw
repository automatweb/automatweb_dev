<?php
// väliste linkide manageerimine
include("const.aw");
include("admin_header.$ext");
classload("extlinks","document");
$extlinks = new extlinks;
$docs = new document;
$extlinks->tpl_init("automatweb/extlinks");
$extlinks->db_init();
switch($op) {
	case "addform":
	    $site_title = "Lingi lisamine dokumendile '" . $docs->get_title($docid) . "'";
			$extlinks->read_template("add.tpl");
			$extlinks->vars(array("docid" => $docid));
			$content = $extlinks->parse();
			break;
	case "addlink":
		// lingi lisamine andmebaasi
		$newlinkid = $extlinks->register_object($docid,"$name",CL_EXTLINK,"Väline link");
		$extlinks->add_link(array("id"  => $newlinkid,
															"oid" => $docid,
		                          "name" => $name,
															"url"   => $url,
															"desc"  => $desc));
		$extlinks->add_alias($docid,$newlinkid);
		http_refresh(0,$extlinks->mk_orb("change", array("id" => $docid), "document"));
		exit;
	default:
      $content = "Väliste linkide manageerimine";
			break;
};
include("admin_footer.$ext");
?>
