<?php

class aip_change extends aw_template
{
	function aip_change()
	{
		$this->init("aip_change");
	}

	function add($arr)
	{
		extract($arr);
		$this->read_template("add.tpl");
		$this->mk_path(0, "<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Lisa muudatus");

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"act_time" => $de->gen_edit_form("act_time", time()),
			"j_time" => $de->gen_edit_form("j_time", time()),
			"files" => $this->multiple_option_list(array(), $this->get_chfile_list()),
			"types" => $this->picker(0,array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT")),
			"reforb" => $this->mk_reforb("submit")
		));
		return $this->parse();
	}

	function submit($arr)
	{
		extract($arr);

		load_vcl("date_edit");
		$de = new date_edit("act_time");

		classload("scheduler");
		$sch = new scheduler;

		if ($id)
		{
			$ch = $this->load($id);
			$sch->remove(array(
				"event" => $this->mk_my_orb("do_change", array("id" => $id))
			));

			$f = get_instance("file");

			global $change_pdf_1, $change_pdf_2, $change_pdf_3;
			if (is_uploaded_file($change_pdf_1))
			{
				$f1_dat = $f->add_upload_image("change_pdf_1", 1, $ch["meta"]["pfiles"]["1"]["id"]);
			}
			else
			{
				$f1_dat = $ch["meta"]["pfiles"]["1"];
			}

			if (is_uploaded_file($change_pdf_2))
			{
				$f2_dat = $f->add_upload_image("change_pdf_2", 1, $ch["meta"]["pfiles"]["2"]["id"]);
			}
			else
			{
				$f2_dat = $ch["meta"]["pfiles"]["2"];
			}

			if (is_uploaded_file($change_pdf_3))
			{
				$f3_dat = $f->add_upload_image("change_pdf_3", 1, $ch["meta"]["pfiles"]["3"]["id"]);
			}
			else
			{
				$f3_dat = $ch["meta"]["pfiles"]["3"];
			}

			if ($del_chp_1 == 1)
			{
				$f1_dat = array();
			}
			if ($del_chp_2 == 1)
			{
				$f2_dat = array();
			}
			if ($del_chp_3 == 1)
			{
				$f3_dat = array();
			}

			$this->upd_object(array(
				"oid" => $id, 
				"name" => $name,
				"metadata" => array(
					"act_time" => $de->get_timestamp($act_time),
					"j_time" => $de->get_timestamp($j_time),
					"files" => $this->make_keys($files),
					"upd_type" => $type,
					"pfiles" => array(
						"1" => $f1_dat,
						"2" => $f2_dat,
						"3" => $f3_dat
					)
				)
			));
		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent, 
				"class_id" => CL_AIP_CHANGE,
				"name" => $name,
				"metadata" => array(
					"act_time" => $de->get_timestamp($act_time),
					"j_time" => $de->get_timestamp($j_time),
					"files" => $this->make_keys($files),
					"upd_type" => $type
				)
			));
		}

		$sch->add(array(
			"time" => $de->get_timestamp($act_time),
			"event" => $this->mk_my_orb("do_change", array("id" => $id))
		));
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function change($arr)
	{
		extract($arr);
		$ch = $this->load($id);
		$this->mk_path(0, "<a href='".$this->mk_my_orb("list")."'>Nimekiri</a> / Muuda");
		$this->read_template("add.tpl");

		load_vcl("date_edit");
		$de = new date_edit("act_time");
		$de->configure(array(
			"year" => 1,
			"month" => 1,
			"day" => 1,
			"hour" => 1,
			"minute" => 1,
			"classid" => "formselect"
		));

		$this->vars(array(
			"cur_pdf_1" => file::check_url($ch["meta"]["pfiles"]["1"]["url"]),
			"cur_pdf_2" => file::check_url($ch["meta"]["pfiles"]["2"]["url"]),
			"cur_pdf_3" => file::check_url($ch["meta"]["pfiles"]["3"]["url"]),
		));

		if ($ch["meta"]["pfiles"]["1"]["id"])
		{
			$this->vars(array("IS_PDF1" => $this->parse("IS_PDF1")));
		}

		if ($ch["meta"]["pfiles"]["2"]["id"])
		{
			$this->vars(array("IS_PDF2" => $this->parse("IS_PDF2")));
		}

		if ($ch["meta"]["pfiles"]["3"]["id"])
		{
			$this->vars(array("IS_PDF3" => $this->parse("IS_PDF3")));
		}

		$this->vars(array(
			"act_time" => $de->gen_edit_form("act_time", $ch["meta"]["act_time"]),
			"j_time" => $de->gen_edit_form("j_time", $ch["meta"]["j_time"]),
			"files" => $this->multiple_option_list($ch["meta"]["files"], $this->get_chfile_list($ch["meta"]["files"])),
			"name" => $ch["name"],
			"types" => $this->picker($ch["meta"]["upd_type"],array("1" => "AIP AMDT", "2" => "AIRAC AIP AMDT")),
			"reforb" => $this->mk_reforb("submit", array("id" => $id))
		));
		return $this->parse();
	}

	function orb_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");

		$chd = $this->get_cval("aip_change::change_dir");
		$act_1 = $this->get_cval("aip_change::act_change_1");
		$act_2 = $this->get_cval("aip_change::act_change_2");

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_AIP_CHANGE." AND status != 0");
		while ($row = $this->db_next())
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));
			$this->vars(array(
				"name" => $row["name"],
				"time" => $this->time2date($meta["act_time"], 2),
				"j_time" => $this->time2date($meta["j_time"], 2),
				"id" => $row["oid"],
				"checked_1" => checked($act_1 == $row["oid"]),
				"checked_2" => checked($act_2 == $row["oid"]),
				"change" => $this->mk_my_orb("change", array("id" => $row["oid"])),
				"delete" => $this->mk_my_orb("delete", array("id" => $row["oid"]))
			));
			$l.= $this->parse("LINE");
		}
		$this->vars(array(
			"change_dir" => $chd,
			"LINE" => $l,
			"add" => $this->mk_my_orb("new", array("parent" => 1)),
			"reforb" => $this->mk_reforb("submit_list")
		));
		return $this->parse();
	}

	function submit_list($arr)
	{
		extract($arr);

		classload("config");
		$co = new config;
		$co->set_simple_config("aip_change::change_dir", $change_dir);
		$co->set_simple_config("aip_change::act_change_1", $act_1);
		$co->set_simple_config("aip_change::act_change_2", $act_2);

		return $this->mk_my_orb("list");
	}

	function load($id)
	{
		return $this->get_object($id);
	}

	function orb_delete($arr)
	{
		extract($arr);

		classload("scheduler");
		$sched = new scheduler;
		$sched->remove(array(
			"event" => $this->mk_my_orb("do_change", array("id" => $id))
		));
		$this->delete_object($id);
		header("Location: ".$this->mk_my_orb("list"));
		die();
	}

	function get_chfile_list($add = array())
	{
		$ret = array();

		$dir = $this->get_cval("aip_change::change_dir");
		if ($dir = @opendir($dir)) 
		{
		  while (($file = readdir($dir)) !== false) 
			{
				if (!($file == "." || $file == ".."))
				{
					$ret[$file] = $file;
				}
		  }  
		  closedir($dir);
		}

		return array_merge($ret,$add);
	}

	function do_change($arr)
	{
		extract($arr);
	}

	function show_files($arr)
	{
		extract($arr);
		$this->read_template("show_files.tpl");

		$ids = array();
		$ch = $this->load($this->get_cval("aip_change::act_change_".$type));
		if (is_array($ch["meta"]["files"]))
		{
			foreach($ch["meta"]["files"] as $fi)
			{
				$id = $this->db_fetch_field("SELECT id FROM aip_files WHERE filename LIKE '%".$fi."%'","id");
				if ($id)
				{
					$ids[] = $id;
				}
			}
		}

		$this->db_query("SELECT * FROM objects WHERE class_id = ".CL_FILE." AND status = 2 AND oid IN (".join(",",$ids).")");
		while ($row = $this->db_next())
		{
			$meta = $this->get_object_metadata(array(
				"metadata" => $row["metadata"]
			));

			$this->vars(array(
				"name" => str_replace(".pdf","",$row["name"]),
				"j_time" => $this->time2date($meta["j_time"], 2),
				"act_time" => $this->time2date($meta["act_time"], 2)
			));
			$l.=$this->parse("LINE");
		}

		for ($i=1; $i < 4; $i++)
		{
			if ($ch["meta"]["pfiles"][$i]["id"])
			{
				$this->vars(array(
					"url" => file::check_url($ch["meta"]["pfiles"][$i]["url"]),
					"name" => $ch["meta"]["pfiles"][$i]["orig_name"]
				));
				$p.=$this->parse("CHANGE_PDF");
			}
		}

		$this->vars(array(
			"CHANGE_PDF" => $p,
			"LINE" => $l
		));
		return $this->parse();
	}
}
?>