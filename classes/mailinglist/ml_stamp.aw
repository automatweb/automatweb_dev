<?php

// extra lame klass , ainult selleks, et salvestada kaks muutujat
class ml_stamp extends aw_template
{
	function ml_stamp()
	{
		$this->init("automatweb/mlist");
		lc_load("definition");
	}

	function orb_new($arr)
	{
		is_array($arr)? extract($arr) : $parent=$arr;

		$this->mk_path($parent,"Lisa stamp");
		$this->read_template("stamp_edit.tpl");

		$this->vars(array(
			"reforb" => $this->mk_reforb("submit_edit",array("parent" => $parent))
		));
		
		return $this->parse();
	}

	function orb_change($arr)
	{
		is_array($arr)? extract($arr) : $parent=$arr;

		$this->mk_path($parent,"Muuda stampi");
		$this->read_template("stamp_edit.tpl");

		$name=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$id'","name");
		$content=$this->get_object_metadata(array(
			"oid" => $id,
			"key" => "content"
		));

		$this->vars(array(
			"name" => $name,
			"content" => $content,
			"reforb" => $this->mk_reforb("submit_edit",array("parent" => $parent, "id" => $id))
		));
		
		return $this->parse();
	}


	function orb_submit_edit($arr)
	{
		extract($arr);

		if (!$id)
		{
			$id=$this->new_object(array(
				"class_id" => CL_ML_STAMP,
				"name" => $name, 
				"parent" => $parent
			));
			$this->set_object_metadata(array(
				"oid" => $id,
				"value" => $content,
				"key" => "content"
			));

			$this->_log(ST_MAILINGLIST_STAMP, SA_ADD, $name, $id);
		} 
		else
		{
			$this->db_query("UPDATE objects SET name='$name' WHERE oid = '$id'");
			$this->set_object_metadata(array(
				"oid" => $id,
				"value" => $content,
				"key" => "content"
			));
			$this->_log(ST_MAILINGLIST_STAMP, SA_CHANGE, $name, $id);
		};
		
		return $this->mk_my_orb("change",array("id" => $id,"parent" => $parent));
	}
};
?>
