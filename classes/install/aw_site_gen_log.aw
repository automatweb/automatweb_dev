<?php

/*

@classinfo syslog_type=ST_AW_SITE_GEN_LOG

@groupinfo general caption=Üldine
@groupinfo view caption="Vaata logi"

@default table=objects
@default group=general

@property status type=status field=status
@caption Staatus

@property show type=text field=meta method=serialize group=view no_caption=1

*/

class aw_site_gen_log extends class_base
{
	function aw_site_gen_log()
	{
		$this->init(array(
			'tpldir' => 'install/aw_site_gen_log',
			'clid' => CL_AW_SITE_GEN_LOG
		));
	}

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
		return $this->show(array('id' => $alias['target']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		$t = new aw_table(array("layout" => "generic"));

		$df = aw_ini_get('config.dateformats');

		$t->define_field(array(
			'name' => 'tm',
			'caption' => 'Millal',
			'sortable' => 1,
			'numeric' => 1,
			'type' => 'time',
			'format' => $df[2],
			'nowrap' => 1
		));

		$t->define_field(array(
			"name" => "uid",
			"caption" => "Kes",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "msg",
			"caption" => "Mida",
			"sortable" => 1
		));

		$t->define_field(array(
			"name" => "result",
			"caption" => "Tulemus",
			"sortable" => 1
		));
	
		$t->define_field(array(
			"name" => "comment",
			"caption" => "Kommentaar",
			"sortable" => 1
		));
	
		$ar = new aw_array($ob['meta']['log']);
		foreach($ar->get() as $idx => $row)
		{
			$row["id"] = $idx;
			$t->define_data($row);
		}

		$t->set_default_sortby("id");
		$t->sort_by();
		return $t->draw();
	}

	////
	// !start site gen log
	// parameters:
	//	parent - where to create log
	//	name - log object name
	function start_log($arr)
	{
		extract($arr);
		if (!$parent)
		{
			$this->raise_error(ERR_SG_NO_PARENT, "Saidi logi alustemisele ei antud parent menyy idd!", false, true);
		}

		$this->cur_log_id = $this->new_object(array(
			"parent" => $parent,
			"name" => $name,
			"class_id" => $this->clid,
			"status" => 2
		));

		$this->log_entries = array();
	}


	//// 
	// !adds a line to the current site log object
	// parameters:
	//	uid - who did
	//	msg - did what
	//	comment - comment
	//	result
	function add_line($arr)
	{
		$arr["tm"] = time();
		$this->log_entries[] = $arr;
	}

	function finish_log()
	{
		if (!$this->cur_log_id)
		{
			$this->raise_error(ERR_NO_LOG, "Logimist pole alustatud!", false, true);
		}
		$this->set_object_metadata(array(
			"oid" => $this->cur_log_id,
			"key" => "log",
			"value" => $this->log_entries
		));
	}

	function get_property($arr)
	{
		if ($arr['prop']['name'] == "show")
		{
			$arr['prop']['value'] = $this->show(array("id" => $arr['obj']['oid']));
		}
		return PROP_OK;
	}
}
?>
