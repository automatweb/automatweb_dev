<?php
// $Header
// objconfig.aw - objekti konfiguratsiooniobjekt
class objconfig extends aw_template
{
	function objconfig() 
	{
		$this->init("objconfig");
		global $lc_objconfig;
		lc_load("objconfig");

		$this->lc_load("objconfig","lc_objconfig");

		// add all clid's which support config objects here
		$this->baseclasses = array(CL_CALENDAR);
	}

	////
	// !Used for adding of changing  config object
	function add($args = array())
	{
		extract($args);


		$caption = "Lisa konfiguratsiooniobjekt";
		$this->read_template("add.tpl");
		$ac = get_instance("config");
		$cfgforms = $ac->get_simple_config("config_forms");
		$config_forms = aw_unserialize($cfgforms);

		foreach($this->baseclasses as $clid)
		{
			$this->vars(array(
				"clid" => $clid,
				"name" => $this->cfg["classes"][$clid]["name"],
				"selected" => "checked",
			));

			$block .= $this->parse("classlist");
		};
		
		$this->mk_path($parent,$caption);


		$this->vars(array(
			"name" => $obj["name"],
			"classlist" => $block,
			"forms" => $this->picker(-1,$config_forms),
			"comment" => $obj["comment"],
			"reforb" => $this->mk_reforb("submit_add",array("parent" => $parent)),
		));
		return $this->parse();
	}

	////
	// !Submits a new config object
	function submit_add($args = array())
	{
		extract($args);
		$parent = (int)$parent;
		$id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"comment" => $comment,
			"class_id" => CL_OBJCONFIG,
			"subclass" => $baseclass,
			"metadata" => array("form" => $form),
		));
		return $this->mk_my_orb("change",array("id" => $id));
	}

	////
	// !Wrapper for object configuration
	function change($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);
		$this->read_template("change.tpl");
		$this->mk_path($obj["parent"],"Muuda konfiguratsiooniobjekti");
		$form = $obj["meta"]["form"];

		// figure out which entry_id this form uses
		$q = "SELECT id FROM form_entries WHERE obj_id = '$id'";
		$this->db_query($q);
		$row = $this->db_next();

		// that's the form we want to use for editing object configuration

		// this is now fixed width.
		$frm = get_instance("formgen/form");
		return $frm->gen_preview(array("id" => $form,"obj_id" => $id,"entry_id" => $row["id"]));
	}

	////
	// !Submits a new or changed iframe object
	function submit($args = array())
	{
		extract($args);
		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"comment" => $comment,
		));

		$this->set_object_metadata(array(
			"oid" => $id,
			"data" => $conf,
			"overwrite" => 1,
		));

		return $this->mk_my_orb("change",array("id" => $id));
	}

	
}
?>
