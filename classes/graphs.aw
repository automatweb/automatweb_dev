<?php

/*

	See on siis see fail mis selle graph klassiga tegeleb
	
*/

include("../const.aw");
switch($type) {
	case "add":
		include("admin_header.aw");							
		$site_title = "Graafikud";
		$doc_title = "Lisamine";
		classload("graph");
		$g=new graph();
		$content = $g->graph_new();
		require("admin_footer.aw");
		break;
	case "meta":
		include("admin_header.aw");
		if ($id)
		{
			$site_title = "Graafikud";
			$doc_title = "Meta-Info";
			classload("graph");
			$g=new graph();
			$content = $g->graph_meta($id);
		}
		require("admin_footer.aw");
		break;
	case "conf":
		include("admin_header.aw");							
		if ($id) 
		{
			classload("graph");
			$site_title = "Graafikud";
			$doc_title = "Konfimine";
			$g = new graph;
			$content = $g->graph_conf($id);
		} else $this->raise_error("Graafi konfimise viga",FALSE);
		require("admin_footer.aw");
		break;
	case "show":
		if ($id)
			classload("aw_template");
			classload("graph");
			$g = new graph;
			$Im=$g->show($id);
			header("Content-type: image/gif");
			imagegif($Im);
			imagedestroy($Im);
		break;
	case "preview":
		if ($id) 
		{
			include("admin_header.aw");
			header ("Cache-Control: no-store");
			header ("Pragma: no-cache");
			classload(graph);
			$g=new graph;
			$content=$g->preview($id);	
		}
		require("admin_footer.aw");
		break;
	case "data":
		if ($id)
		{
			include("admin_header.aw");
			classload(graph);
			$g=new graph;
			$content=$g->insert_data($id);
		}
		require("admin_footer.aw");
		break;
	case "a":
		classload("aw_template");
		classload("graph");
		$g = new graph;
		$g->tryitout($id);		
	default:
		include("admin_header.aw");							
		$site_title = "Graafikud";
		$doc_title = "";
		classload("graph");
		$g=new graph();
		$content = $g->graph_list();
		require("admin_footer.aw");
};
?>
