<?php
include("const.aw");
switch($type) {
	default:
		include("admin_header.aw");							
		classload("aw_template");
		$tt = new aw_template;
		$tt->db_init();
		if (!$tt->prog_acl("view", PRG_GRAPH))
		{
			$tt->prog_acl_error("view", PRG_GRAPH);
		}

		$site_title = "Graafikud";
		$doc_title = "";
		classload("graph");
		$ar=array("parent" => "0");
		$g=new graph();
		$content = $g->glist($ar);
		require("admin_footer.aw");
};
?>
