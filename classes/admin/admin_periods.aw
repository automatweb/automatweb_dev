<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/Attic/admin_periods.aw,v 1.2 2003/04/25 09:21:47 duke Exp $
// this is here so that orb will work...
classload("periods");
class admin_periods extends periods
{
	function periods($oid = 0)
	{
		parent::init($oid);
	}
	

	function get_next($id,$oid) 
	{
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) 
		{
			if ($select == 1) 
			{
				$next = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) 
			{
				$select = 1;
			};
		};
		return $next;
	}
	
	function get_prev($id,$oid) 
	{
		$q = "SELECT * FROM periods WHERE oid = '$oid' ORDER BY jrk DESC";
		$this->db_query($q);
		$select = 0;
		while($row = $this->db_next()) 
		{
			if ($select == 1) 
			{
				$prev = $row["id"];
				$select = 0;
			};
			if ($row["id"] == $id) 
			{
				$select = 1;
			};
		};
		return $prev;
	}
	
	function save($data) 
	{
		extract($data);

		$old = $this->get($id);

		// if image uploaded, save it
		$img = get_instance("image");
		$old["data"]["image"] = $img->add_upload_image("image",0,$old["data"]["image"]["id"]);
		$old["data"]["image_link"] = $image_link;
		$old["data"]["pyear"] = $pyear;
		$old['data']['comment'] = $comment;

		$dstr = aw_serialize($old["data"]);
		$this->quote($dstr);

		$q = "UPDATE periods
			SET description = '$description',
			    archived = '$archived',
					data = '$dstr'
			WHERE id = '$id'";
		$this->db_query($q);
		$this->cache->file_invalidate($this->cf_name.$id);
		aw_cache_set("per_by_id", $id, false);
	}

	function add($archived,$description) 
	{
		$aflag = ($archived == "on") ? 1 : 0;
		$t = time();
		$oid = $this->oid;
		$q = "INSERT INTO periods (archived,description,created,oid)
			VALUES('$aflag','$description','$t','$oid')";
		$this->db_query($q);
		return $this->db_last_insert_id();
	}

	function savestatus($data) 
	{
		// checkboxid, mis näitavad perioodi arhiveeritust
		$arc_flags = $data["arc"];
		// eelmised väärtused
		$old_arc_flags = $data["oldarc"];

		// salvestame flagid, mis naitavad perioodide arhiveeritust
		while(list($k,$v) = each($old_arc_flags)) 
		{
			// teeme kindlaks, kas staatust on vaja muuta
			$newstatus = ($arc_flags[$k] == "on") ? 1 : 0;
			if ($newstatus != $v) 
			{
				$q = "UPDATE periods SET archived = '$newstatus' WHERE id = '$k'";
				$this->db_query($q);
				// also flush caches
				$this->cache->file_invalidate($this->cf_name.$k);
				aw_cache_set("per_by_id", $k, false);
			};
		};

		if ($data["oldactiveperiod"] != $data["activeperiod"]) 
		{
			$this->activate_period($data["activeperiod"],$this->oid);
			$this->_log(ST_PERIOD,SA_ACTIVATE_PERIOD, $data["activeperiod"]);
		};

		$oldjrk = $data["oldjrk"];
		$jrk = $data["jrk"];

		// salvestame jarjekorranumbrid
		while(list($k,$v) = each($oldjrk)) 
		{
			$newjrk = $jrk[$k];
			if ($v != $newjrk) 
			{
				$q = "UPDATE periods SET jrk = '$newjrk' WHERE id = '$k'";
				$this->db_query($q);
				$this->cache->file_invalidate($this->cf_name.$k);
				aw_cache_set("per_by_id", $k, false);
			};
		};
	}

	function toggle_arc_flag($id) 
	{
		$old = $this->get($id);
		$new = ($old["archived"] == 1) ? "0" : "1";
		$q = "UPDATE periods SET archived = '$new' WHERE id = '$id'";
		$this->db_query($q);
		$this->cache->file_invalidate($this->cf_name.$id);
		aw_cache_set("per_by_id", $id, false);
	}

	function admin_list($arr)
	{
		extract($arr);
		$this->read_template("list.tpl");
		$active = $this->rec_get_active_period();
		$this->clist();
		load_vcl("table");
		$table = new aw_table(array(
			"prefix" => "periods",
		));
	
		$table->parse_xml_def($this->cfg["basedir"]."/xml/periods/list.xml");


		while($row = $this->db_next()) 
		{
			$jrk_html = "<input type='text' size='3' maxlength='3' name='jrk[$row[id]]' value='$row[jrk]'><input type='hidden' name='oldjrk[$row[id]]' value='$row[jrk]'>";
			$archived = checked($row["archived"]);
			$arc_html = "<input type='checkbox' name='arc[$row[id]]' $archived><input type='hidden' name='oldarc[$row[id]]' value='$row[archived]'>";
			$actcheck = checked($row["id"] == $active);
			$act_html = "<input type='radio' name='activeperiod' $actcheck value='$row[id]'>";
			$row["jrk"] = $jrk_html;
			$row["archived"] = $arc_html;
			$row["active"] = $act_html;
			$ch_url = $this->mk_my_orb("change",array("id" => $row["id"]));
			$row["change"] = "<a href='$ch_url'>Muuda</a>";
			$table->define_data($row);
		};
		
		$table->sort_by(array("sortby" => $sortby));

		$this->vars(array(
			"add" => $this->mk_my_orb("add"),
			"table" => $table->draw(),
			"reforb" => $this->mk_reforb("savestatus", array("oldactiveperiod" => $active, "oid" => $this->oid))
		));
		return $this->parse();
	}

	function orb_add($arr)
	{
		extract($arr);
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Perioodid</a> / Lisa uus");
		$this->read_template("add.tpl");
		$years = array(
			"2000" => "2000",
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
		);
		$this->vars(array(
			"pyear" => $this->picker(-1,array("0" => "--vali--") + $years),
			"reforb" => $this->mk_reforb("submit_add", array("oid" => $this->oid))
		));
		return $this->parse();
	}

	function orb_submit_add($arr)
	{
		extract($arr);
		if (!$id)
		{
			$id = $this->add($archived,$description);
		};
		$arr["id"] = $id;
		$this->save($arr);
		return $this->mk_my_orb("change", array("id" => $id));
	}

	function orb_edit($arr)
	{
		extract($arr);
		$this->read_template("edit.tpl");
		$this->mk_path(0,"<a href='".$this->mk_my_orb("admin_list")."'>Perioodid</a> / Muuda");
		$cper = $this->get($id);
		$years = array(
			"2000" => "2000",
			"2001" => "2001",
			"2002" => "2002",
			"2003" => "2003",
			"2004" => "2004",
			"2005" => "2005",
		);
		classload("image");
		$this->vars(array(
			"ID" => $cper["id"],
			"description" => $cper["description"],
			"plist" => $this->period_olist(),
			"arc" => $this->option_list($cper["archived"],array("0" => "Ei","1" => "Jah")),
			"image" => image::make_img_tag(image::check_url($cper["data"]["image"]["url"])),
			"image_link" => $cper["data"]["image_link"],
			"pyear" => $this->picker($cper["data"]["pyear"],array("0" => "--vali--") + $years),
			"comment" => $cper['data']['comment'],
			"reforb" => $this->mk_reforb("submit_add", array("id" => $id))
		));
		return $this->parse();
	}

	function orb_savestatus($arr)
	{
		extract($arr);
		$this->savestatus($arr);
		return $this->mk_my_orb("admin_list");
	}

};
?>
